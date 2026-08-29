<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
require_once __DIR__ . '/../config/config.php';
$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['error' => 'Keine Bestell-ID angegeben']);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Bestellung nicht gefunden']);
        exit;
    }
    $stmtItems = $pdo->prepare("SELECT oi.*, p.name as name, p.price as price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmtItems->execute([$order['id']]);
    $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    $stmtCustomer = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmtCustomer->execute([$order['customer_id']]);
    $order['customer'] = $stmtCustomer->fetch(PDO::FETCH_ASSOC);
    echo json_encode($order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
