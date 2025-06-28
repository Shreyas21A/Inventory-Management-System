<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$response = ['success' => false, 'error' => ''];

$product_name = $_POST['product_name'] ?? '';
$description = $_POST['description'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$image_path = '';

if (!$product_name || $quantity < 0 || $price < 0) {
    $response['error'] = 'Invalid input data.';
    echo json_encode($response);
    exit;
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image = $_FILES['image'];
    $image_name = time() . '_' . basename($image['name']);
    $image_path = 'images/' . $image_name;
    if (!move_uploaded_file($image['tmp_name'], $image_path)) {
        $response['error'] = 'Failed to upload image.';
        echo json_encode($response);
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO products (product_name, description, quantity, price, image_path) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssids", $product_name, $description, $quantity, $price, $image_path);
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['error'] = 'Failed to add product.';
}
$stmt->close();
$conn->close();
echo json_encode($response);
?>