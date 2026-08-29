<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
require_once __DIR__ . '/../config/config.php';
$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['orderId'] ?? null;
$status = $input['status'] ?? null;
if (!$orderId || !$status) {
    echo json_encode(['success' => false, 'error' => 'Bestell-ID und Status sind erforderlich']);
    exit;
}
$validStatuses = ['pending', 'shipped', 'completed', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'error' => 'Ungueltiger Status']);
    exit;
}
try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updated = $stmt->execute([$status, $orderId]);
    echo json_encode(['success' => $updated && $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
