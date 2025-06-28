<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$searchType = $_POST['search_type'] ?? '';
$searchKeyword = $_POST['search_keyword'] ?? '';
$page = max(1, (int)($_POST['page'] ?? 1));
$perPage = (int)($_POST['per_page'] ?? 10);
$offset = ($page - 1) * $perPage;

$response = ['error' => null, 'results' => [], 'total' => 0, 'search_type' => $searchType];

if (!$searchType || !$searchKeyword) {
    $response['error'] = 'Please select a search type and enter a keyword.';
    echo json_encode($response);
    exit;
}

$queryMap = [
    'product' => "SELECT * FROM products WHERE product_name LIKE ?",
    'customer' => "SELECT * FROM customers WHERE customer_name LIKE ?",
    'seller' => "SELECT * FROM sellers WHERE seller_name LIKE ?"
];

if (!isset($queryMap[$searchType])) {
    $response['error'] = 'Invalid search type.';
    echo json_encode($response);
    exit;
}

$conditions = [];
$params = [];
$types = '';

$likeKeyword = '%' . $searchKeyword . '%';
$conditions[] = $queryMap[$searchType];
$params[] = $likeKeyword;
$types .= 's';

if ($searchType === 'product') {
    if (isset($_POST['min_price']) && $_POST['min_price'] !== '') {
        $conditions[] = 'price >= ?';
        $params[] = (float)$_POST['min_price'];
        $types .= 'd';
    }
    if (isset($_POST['max_price']) && $_POST['max_price'] !== '') {
        $conditions[] = 'price <= ?';
        $params[] = (float)$_POST['max_price'];
        $types .= 'd';
    }
    if (isset($_POST['min_quantity']) && $_POST['min_quantity'] !== '') {
        $conditions[] = 'quantity >= ?';
        $params[] = (int)$_POST['min_quantity'];
        $types .= 'i';
    }
} elseif (isset($_POST['phone']) && $_POST['phone'] !== '') {
    $conditions[] = 'phone LIKE ?';
    $params[] = '%' . $_POST['phone'] . '%';
    $types .= 's';
}

$query = str_replace('SELECT *', 'SELECT COUNT(*)', $queryMap[$searchType]);
if (count($conditions) > 1) {
    $query .= ' AND ' . implode(' AND ', array_slice($conditions, 1));
}

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_row()[0];
$stmt->close();

$query = $queryMap[$searchType];
if (count($conditions) > 1) {
    $query .= ' AND ' . implode(' AND ', array_slice($conditions, 1));
}
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$response['results'] = $results;
$response['total'] = $total;
echo json_encode($response);
?>