<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = [];

if ($action === 'stats') {
    $stats = [
        ['query' => 'SELECT COUNT(*) as total FROM products'],
        ['query' => 'SELECT COUNT(*) as total FROM sales'],
        ['query' => 'SELECT COUNT(*) as total FROM customers'],
        ['query' => 'SELECT COUNT(*) as total FROM sellers']
    ];
    $response['stats'] = [];
    foreach ($stats as $stat) {
        $result = $conn->query($stat['query']);
        $response['stats'][] = $result->fetch_assoc();
    }
} else {
    $search = $_POST['search'] ?? '';
    $sortBy = $_POST['sort_by'] ?? 'product_name';
    $page = max(1, (int)($_POST['page'] ?? 1));
    $perPage = (int)($_POST['per_page'] ?? 10);
    $offset = ($page - 1) * $perPage;

    $query = "SELECT * FROM products";
    $countQuery = "SELECT COUNT(*) FROM products";
    $params = [];
    $types = '';

    if ($search) {
        $query .= " WHERE product_name LIKE ?";
        $countQuery .= " WHERE product_name LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= 's';
    }

    $query .= " ORDER BY $sortBy";
    $stmt = $conn->prepare($countQuery);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $query .= " LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $response['products'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $response['total'] = $total;
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>