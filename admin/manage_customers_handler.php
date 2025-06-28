<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'error' => ''];

if ($action === 'update') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $customer_name = $_POST['customer_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    if (!$customer_id || !$customer_name || !$email) {
        $response['error'] = 'Customer ID, name, and email are required.';
    } else {
        $stmt = $conn->prepare("UPDATE customers SET customer_name = ?, email = ?, phone = ?, address = ? WHERE customer_id = ?");
        $stmt->bind_param("ssssi", $customer_name, $email, $phone, $address, $customer_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to update customer.';
        }
        $stmt->close();
    }
} elseif ($action === 'delete') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    if (!$customer_id) {
        $response['error'] = 'Customer ID is required.';
    } else {
        $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to delete customer. It may be linked to sales records.';
        }
        $stmt->close();
    }
} else {
    $search = $_POST['search'] ?? '';
    $query = "SELECT * FROM customers";
    $params = [];
    $types = '';

    if ($search) {
        $query .= " WHERE customer_name LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= 's';
    }

    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $response['customers'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>