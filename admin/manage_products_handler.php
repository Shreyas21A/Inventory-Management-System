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
    $product_id = (int)($_POST['product_id'] ?? 0);
    $product_name = $_POST['product_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    if (!$product_id || !$product_name || $quantity < 0 || $price < 0) {
        $response['error'] = 'Invalid input data.';
    } else {
        $image_path = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024;
            if (in_array($_FILES['product_image']['type'], $allowedTypes) && $_FILES['product_image']['size'] <= $maxSize) {
                $image_name = time() . '_' . basename($_FILES['product_image']['name']);
                $image_path = 'images/' . $image_name;
                if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $image_path)) {
                    $response['error'] = 'Failed to upload image.';
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response['error'] = 'Invalid image type or size (max 2MB, JPEG/PNG/GIF).';
                echo json_encode($response);
                exit;
            }
        }

        $query = "UPDATE products SET product_name = ?, description = ?, quantity = ?, price = ?";
        $params = [$product_name, $description, $quantity, $price];
        $types = 'ssii';
        if ($image_path) {
            $query .= ", image_path = ?";
            $params[] = $image_path;
            $types .= 's';
        }
        $query .= " WHERE product_id = ?";
        $params[] = $product_id;
        $types .= 'i';

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to update product.';
        }
        $stmt->close();
    }
} elseif ($action === 'delete') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if (!$product_id) {
        $response['error'] = 'Product ID is required.';
    } else {
        $stmt = $conn->prepare("DELETE FROM purchases WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM sales WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to delete product.';
        }
        $stmt->close();
    }
} else {
    $search = $_POST['search'] ?? '';
    $query = "SELECT * FROM products";
    $params = [];
    $types = '';

    if ($search) {
        $query .= " WHERE product_name LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= 's';
    }

    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $response['products'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>