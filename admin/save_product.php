<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
require_once __DIR__ . '/../config/config.php';
$uploadDir = __DIR__ . '/../uploads/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($fileExt, $allowedExts)) {
        $newFilename = uniqid('product_', true) . '.' . $fileExt;
        $targetPath = $uploadDir . $newFilename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/' . $newFilename;
        }
    }
}
$data = [
    'name' => $_POST['name'] ?? '',
    'price' => (float)($_POST['price'] ?? 0),
    'category' => $_POST['category'] ?? '',
    'material' => $_POST['material'] ?? '',
    'stock' => (int)($_POST['stock'] ?? 999),
    'description' => $_POST['description'] ?? '',
    'active' => isset($_POST['active']) ? 1 : 0,
    'image_path' => $imagePath
];
if (empty($data['name']) || $data['price'] <= 0) {
    echo json_encode(['success' => false, 'error' => 'Name und Preis sind Pflichtfelder']);
    exit;
}
try {
    if (!empty($_POST['id'])) {
        $id = $_POST['id'];
        if ($imagePath && !empty($_POST['old_image'])) {
            $oldFile = __DIR__ . '/../' . $_POST['old_image'];
            if (file_exists($oldFile)) unlink($oldFile);
        }
        $sql = "UPDATE products SET name = :name, price = :price, category = :category, material = :material, stock = :stock, description = :description, active = :active";
        if ($imagePath) $sql .= ", image_path = :image_path";
        $sql .= " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $params = [':id' => $id, ':name' => $data['name'], ':price' => $data['price'], ':category' => $data['category'], ':material' => $data['material'], ':stock' => $data['stock'], ':description' => $data['description'], ':active' => $data['active']];
        if ($imagePath) $params[':image_path'] = $imagePath;
        $stmt->execute($params);
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        $sql = "INSERT INTO products (name, price, category, material, stock, description, active, image_path, created_at) VALUES (:name, :price, :category, :material, :stock, :description, :active, :image_path, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $data['name'], ':price' => $data['price'], ':category' => $data['category'], ':material' => $data['material'], ':stock' => $data['stock'], ':description' => $data['description'], ':active' => $data['active'], ':image_path' => $imagePath]);
        $id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => $id]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
