<?php
// Kontaktformular: Projektanfrage per E-Mail, optional mit Anhaengen (3D-Dateien, Fotos, Skizzen)

// Weiterleitung zurueck zur Startseite mit Statuscode (success | error | size | file)
function redirect_status($status) {
    header("Location: /?status=" . $status . "#cta-w");
    exit;
}

// Textfeld aus $_POST lesen (nur Strings, sonst leer)
function post_text($key) {
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
}

// Prueft, ob der Inhalt einer Datei zur Endung passt (soweit fuer das Format sinnvoll moeglich)
function file_content_matches($path, $ext) {
    $head = (string) file_get_contents($path, false, null, 0, 2048);
    if ($head === '') {
        return false;
    }
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'webp':
            $info = @getimagesize($path);
            if ($info === false) {
                return false;
            }
            $expected = ($ext === 'png') ? IMAGETYPE_PNG : (($ext === 'webp') ? IMAGETYPE_WEBP : IMAGETYPE_JPEG);
            return $info[2] === $expected;
        case 'pdf':
            return strpos($head, '%PDF-') !== false;
        case 'zip':
        case '3mf':
            return strncmp($head, "PK\x03\x04", 4) === 0 || strncmp($head, "PK\x05\x06", 4) === 0;
        case 'heic':
            return substr($head, 4, 4) === 'ftyp'
                && in_array(substr($head, 8, 4), ['heic', 'heix', 'heim', 'heis', 'hevc', 'hevx', 'hevm', 'hevs', 'mif1', 'msf1'], true);
        case 'step':
        case 'stp':
            return strpos($head, 'ISO-10303-21') !== false;
        case 'stl':
            if (strncmp(ltrim($head), 'solid', 5) === 0) {
                return true; // ASCII-STL
            }
            // Binaer-STL: 80 Byte Kopf + 4 Byte Dreieckszahl + 50 Byte je Dreieck
            if (strlen($head) >= 84) {
                $count = unpack('V', substr($head, 80, 4))[1];
                return filesize($path) === 84 + 50 * $count;
            }
            return false;
        case 'obj':
            return strpos($head, "\0") === false
                && preg_match('/^\s*(#|v |vn |vt |o |g |f |s |mtllib|usemtl)/m', $head) === 1;
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /");
    exit;
}

// Honeypot-Check: Wenn dieses Feld befüllt ist, war es ein Bot
if (!empty($_POST["_hp_field"])) {
    redirect_status('success'); // Wir tun so als ob, um den Bot zu täuschen
}

// Wurde die Anfrage schon von PHP/Webserver wegen zu grosser Datenmenge verworfen (post_max_size)?
if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    redirect_status('size');
}

// Zeilenumbrueche und Steuerzeichen entfernen, damit nichts in Mail-Header gelangt
$name = preg_replace('/[\x00-\x1F\x7F]+/', ' ', strip_tags(post_text('name')));
$name = substr(trim($name), 0, 100);
$email = filter_var(post_text('email'), FILTER_SANITIZE_EMAIL);
$message = strip_tags(post_text('message'));

// Projektart aus dem Formular (nur bekannte Werte, sonst "Nicht angegeben")
$project_types = [
    'ersatzteil'   => 'Ersatzteil',
    'bauteil'      => 'Individuelles Bauteil',
    'kleinserie'   => 'Kleinserie / mehrere Stück',
    'datei'        => 'Vorhandene 3D-Datei drucken',
    'konstruktion' => 'Konstruktion / Prototyp',
    'sonstiges'    => 'Sonstiges',
];
$project_type = $project_types[post_text('projekttyp')] ?? 'Nicht angegeben';

// Validierung
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '' || strlen($message) > 10000) {
    redirect_status('error');
}

// Anhaenge: max. 5 Dateien, je 10 MB, zusammen max. 15 MB
$max_file_size  = 10 * 1024 * 1024;
$max_total_size = 15 * 1024 * 1024;
$max_files      = 5;
$allowed_types = [
    'stl'  => 'application/octet-stream',
    'obj'  => 'application/octet-stream',
    '3mf'  => 'application/octet-stream',
    'step' => 'application/octet-stream',
    'stp'  => 'application/octet-stream',
    'zip'  => 'application/zip',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'heic' => 'image/heic',
    'pdf'  => 'application/pdf',
];

// $_FILES['files'] in eine Liste einzelner Dateien umwandeln (leere Auswahlfelder ignorieren)
$uploads = [];
if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
    foreach ($_FILES['files']['name'] as $i => $upload_name) {
        if ($_FILES['files']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $uploads[] = [
            'name'  => is_string($upload_name) ? $upload_name : '',
            'tmp'   => $_FILES['files']['tmp_name'][$i],
            'size'  => (int) $_FILES['files']['size'][$i],
            'error' => $_FILES['files']['error'][$i],
        ];
    }
}

if (count($uploads) > $max_files) {
    redirect_status('file');
}

// Dateicheck: Upload-Fehler, Herkunft, Groesse, Endung und Inhalt
$total_size = 0;
foreach ($uploads as $i => $upload) {
    if ($upload['error'] === UPLOAD_ERR_INI_SIZE || $upload['error'] === UPLOAD_ERR_FORM_SIZE) {
        redirect_status('size');
    }
    if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp'])) {
        redirect_status('file');
    }
    $total_size += $upload['size'];
    if ($upload['size'] > $max_file_size || $total_size > $max_total_size) {
        redirect_status('size');
    }
    $ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
    if (!isset($allowed_types[$ext]) || !file_content_matches($upload['tmp'], $ext)) {
        redirect_status('file');
    }
    $uploads[$i]['ext'] = $ext;
}

$to = "info@3doberberg.de";
$subject = "=?UTF-8?B?" . base64_encode("Neue Projektanfrage von $name (via Website)") . "?=";

$eol = "\r\n";
$mime_boundary = "==Multipart_Boundary_" . bin2hex(random_bytes(16));

$headers = "From: 3D Oberberg Kontakt <$to>" . $eol .
           "Reply-To: $email" . $eol .
           "MIME-Version: 1.0" . $eol .
           "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"";

$text = "Name: $name" . $eol . "E-Mail: $email" . $eol . "Projektart: $project_type" . $eol . $eol .
        "Nachricht:" . $eol . str_replace(["\r\n", "\r", "\n"], $eol, $message) . $eol;

$body = "--{$mime_boundary}" . $eol .
        "Content-Type: text/plain; charset=\"UTF-8\"" . $eol .
        "Content-Transfer-Encoding: base64" . $eol . $eol .
        chunk_split(base64_encode($text), 76, $eol) . $eol;

// Anhaenge einbinden (stueckweise kodiert, damit der Speicherbedarf klein bleibt)
foreach ($uploads as $upload) {
    $base = ltrim(preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($upload['name'], PATHINFO_FILENAME)), '.');
    $file_name = ($base === '' ? 'datei' : substr($base, 0, 80)) . '.' . $upload['ext'];

    $body .= "--{$mime_boundary}" . $eol .
             "Content-Type: {$allowed_types[$upload['ext']]}; name=\"{$file_name}\"" . $eol .
             "Content-Disposition: attachment; filename=\"{$file_name}\"" . $eol .
             "Content-Transfer-Encoding: base64" . $eol . $eol;

    $handle = fopen($upload['tmp'], 'rb');
    if ($handle === false) {
        redirect_status('file');
    }
    // 57 Byte ergeben genau eine Base64-Zeile mit 76 Zeichen
    while (!feof($handle)) {
        $chunk = fread($handle, 57 * 1024);
        if ($chunk === false || $chunk === '') {
            break;
        }
        $body .= chunk_split(base64_encode($chunk), 76, $eol);
    }
    fclose($handle);
    $body .= $eol;
}

$body .= "--{$mime_boundary}--" . $eol;

// Der zusätzliche Parameter "-f" hilft bei netcup, die Mail-Zustellung zu garantieren
if (mail($to, $subject, $body, $headers, "-f " . $to)) {
    redirect_status('success');
}
redirect_status('error');
