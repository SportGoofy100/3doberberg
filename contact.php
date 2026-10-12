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

    // Maximale Dateigroesse (10MB)
    $max_file_size = 10 * 1024 * 1024; 
    $allowed_extensions = ['stl', 'obj', '3mf', 'zip', 'step'];

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

    // File Handling
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['file']['name'];
        $file_tmp  = $_FILES['file']['tmp_name'];
        $file_type = $_FILES['file']['type'];
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Dateicheck
        if ($_FILES['file']['size'] > $max_file_size || !in_array($ext, $allowed_extensions)) {
            header("Location: index.html?status=error#cta-w");
            exit;
        }

        $file_content = file_get_contents($file_tmp);
        $data = chunk_split(base64_encode($file_content));

        $body .= "--{$mime_boundary}" . $eol .
                 "Content-Type: {$file_type}; name=\"{$file_name}\"" . $eol .
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