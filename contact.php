<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Honeypot-Check: Wenn dieses Feld befüllt ist, war es ein Bot
    if (!empty($_POST["_hp_field"])) {
        header("Location: index.html?status=success#cta-w"); // Wir tun so als ob, um den Bot zu täuschen
        exit;
    }

    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = strip_tags(trim($_POST["message"]));

    // Projektart aus dem Formular (nur bekannte Werte, sonst "Nicht angegeben")
    $project_types = [
        'ersatzteil'   => 'Ersatzteil',
        'bauteil'      => 'Individuelles Bauteil',
        'kleinserie'   => 'Kleinserie / mehrere Stück',
        'datei'        => 'Vorhandene 3D-Datei drucken',
        'konstruktion' => 'Konstruktion / Prototyp',
        'sonstiges'    => 'Sonstiges',
    ];
    $project_type = $project_types[$_POST["projekttyp"] ?? ''] ?? 'Nicht angegeben';

    // Validierung
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
        header("Location: index.html?status=error#cta-w");
        exit;
    }

    // Anhaenge: max. 5 Dateien, je 10 MB, zusammen max. 15 MB (3D-Dateien, Fotos, Skizzen)
    $max_file_size  = 10 * 1024 * 1024;
    $max_total_size = 15 * 1024 * 1024;
    $max_files      = 5;
    $allowed_extensions = ['stl', 'obj', '3mf', 'step', 'stp', 'zip', 'jpg', 'jpeg', 'png', 'webp', 'heic', 'pdf'];
    $image_extensions   = ['jpg', 'jpeg', 'png', 'webp'];

    // $_FILES['files'] in eine Liste einzelner Dateien umwandeln (leere Auswahlfelder ignorieren)
    $uploads = [];
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        foreach ($_FILES['files']['name'] as $i => $upload_name) {
            if ($_FILES['files']['error'][$i] == UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $uploads[] = [
                'name'  => $upload_name,
                'tmp'   => $_FILES['files']['tmp_name'][$i],
                'size'  => $_FILES['files']['size'][$i],
                'error' => $_FILES['files']['error'][$i],
            ];
        }
    }

    // Dateicheck: Fehler, Anzahl, Groesse, Dateityp (bei Bildern zusaetzlich der tatsaechliche Inhalt)
    $total_size = 0;
    if (count($uploads) > $max_files) {
        header("Location: index.html?status=error#cta-w");
        exit;
    }
    foreach ($uploads as $i => $upload) {
        $ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
        $total_size += $upload['size'];
        $valid = $upload['error'] == UPLOAD_ERR_OK
            && $upload['size'] <= $max_file_size
            && in_array($ext, $allowed_extensions);
        if ($valid && in_array($ext, $image_extensions)) {
            $valid = @getimagesize($upload['tmp']) !== false;
        }
        if (!$valid || $total_size > $max_total_size) {
            header("Location: index.html?status=error#cta-w");
            exit;
        }
        $uploads[$i]['ext'] = $ext;
    }

    $to = "info@3doberberg.de";
    $subject = "Neue Projektanfrage von $name (via Website)";
    
    $eol = "\r\n";
    $semi_rand = md5(time()); 
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

    $headers = "From: 3D Oberberg Kontakt <$to>" . $eol .
               "Reply-To: $email" . $eol .
               "MIME-Version: 1.0" . $eol .
               "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"";

    $body = "--{$mime_boundary}" . $eol . 
            "Content-Type: text/plain; charset=\"UTF-8\"" . $eol .
            "Content-Transfer-Encoding: 7bit" . $eol . $eol . 
            "Name: $name" . $eol . "E-Mail: $email" . $eol . "Projektart: $project_type" . $eol . $eol . "Nachricht:" . $eol . $message . $eol . $eol;

    // Anhaenge einbinden
    foreach ($uploads as $upload) {
        // Dateiname fuer den Mail-Header bereinigen
        $file_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($upload['name']));
        $data = chunk_split(base64_encode(file_get_contents($upload['tmp'])));

        $body .= "--{$mime_boundary}" . $eol .
                 "Content-Type: application/octet-stream; name=\"{$file_name}\"" . $eol .
                 "Content-Disposition: attachment; filename=\"{$file_name}\"" . $eol .
                 "Content-Transfer-Encoding: base64" . $eol . $eol . $data . $eol . $eol;
    }

    $body .= "--{$mime_boundary}--" . $eol;

    // Der zusätzliche Parameter "-f" hilft bei netcup, die Mail-Zustellung zu garantieren
    if (mail($to, $subject, $body, $headers, "-f " . $to)) {
        header("Location: index.html?status=success#cta-w");
    } else {
        header("Location: index.html?status=error#cta-w");
    }
    exit;
} else {
    header("Location: index.html");
    exit;
}
?>