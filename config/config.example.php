<?php
// Datenbankkonfiguration - BITTE ANPASSEN
// Kopieren Sie diese Datei zu config.php und tragen Sie Ihre Daten ein

define('DB_HOST', 'localhost');
define('DB_NAME', '3doberberg');
define('DB_USER', 'benutzername');
define('DB_PASS', 'passwort');

// Stripe-Konfiguration
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');

define('BASE_URL', 'https://Ihre-Website.de');

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Europe/Berlin');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

$stripeSecretKey = STRIPE_SECRET_KEY;
$stripePublishableKey = STRIPE_PUBLISHABLE_KEY;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
