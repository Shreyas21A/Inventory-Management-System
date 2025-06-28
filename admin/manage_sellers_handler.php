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
    $seller_id = (int)($_POST['seller_id'] ?? 0);
    $seller_name = $_POST['seller_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    if (!$seller_id || !$seller_name || !$email) {
        $response['error'] = 'Seller ID, name, and email are required.';
    } else {
        $stmt = $conn->prepare("UPDATE sellers SET seller_name = ?, email = ?, phone = ?, address = ? WHERE seller_id = ?");
        $stmt->bind_param("ssssi", $seller_name, $email, $phone, $address, $seller_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to update seller.';
        }
        $stmt->close();
    }
} elseif ($action === 'delete') {
    $seller_id = (int)($_POST['seller_id'] ?? 0);
    if (!$seller_id) {
        $response['error'] = 'Seller ID is required.';
    } else {
        $stmt = $conn->prepare("DELETE FROM sellers WHERE seller_id = ?");
        $stmt->bind_param("i", $seller_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to delete seller. It may be linked to purchase records.';
        }
        $stmt->close();
    }
} else {
    $search = $_POST['search'] ?? '';
    $query = "SELECT * FROM sellers";
    $params = [];
    $types = '';

    if ($search) {
        $query .= " WHERE seller_name LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= 's';
    }

    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $response['sellers'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>