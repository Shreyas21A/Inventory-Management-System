<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$response = ['success' => false, 'error' => ''];

$seller_name = $_POST['seller_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if (!$seller_name || !$email) {
    $response['error'] = 'Seller name and email are required.';
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare("INSERT INTO sellers (seller_name, email, phone, address) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $seller_name, $email, $phone, $address);
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['error'] = 'Failed to add seller.';
}
$stmt->close();
$conn->close();
echo json_encode($response);
?>