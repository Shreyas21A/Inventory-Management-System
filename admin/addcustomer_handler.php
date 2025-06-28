<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$response = ['success' => false, 'error' => ''];

$customer_name = $_POST['customer_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if (!$customer_name || !$email) {
    $response['error'] = 'Customer name and email are required.';
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare("INSERT INTO customers (customer_name, email, phone, address) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $customer_name, $email, $phone, $address);
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['error'] = 'Failed to add customer.';
}
$stmt->close();
$conn->close();
echo json_encode($response);
?>