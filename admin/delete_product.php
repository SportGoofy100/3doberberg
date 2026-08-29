<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
require_once __DIR__ . '/../config/config.php';
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Keine Produkt-ID angegeben']);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $deleted = $stmt->execute([$id]);
    if ($product && !empty($product['image_path'])) {
        $imageFile = __DIR__ . '/../' . $product['image_path'];
        if (file_exists($imageFile)) unlink($imageFile);
    }
    echo json_encode(['success' => $deleted && $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
