<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'];
    $description = $_POST['description'];
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    if ($_FILES['product_image']['size'] > 0 && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;
        if (in_array($_FILES['product_image']['type'], $allowedTypes) && $_FILES['product_image']['size'] <= $maxSize) {
            $imagePath = 'images/' . basename($_FILES['product_image']['name']);
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $imagePath)) {
                $stmt = $conn->prepare("INSERT INTO products (product_name, description, quantity, price, image_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssids", $productName, $description, $quantity, $price, $imagePath);
                if ($stmt->execute()) {
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Failed to add product: " . htmlspecialchars($conn->error);
                }
                $stmt->close();
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image type or size (max 2MB, JPEG/PNG/GIF).";
        }
    } else {
        $error = "Please upload a product image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 font-sans">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-6 tracking-tight">Add New Product</h1>
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg flex items-center space-x-2 animate-fade-in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" id="product_name" name="product_name" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200"></textarea>
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="0" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                </div>
                <div>
                    <label for="product_image" class="block text-sm font-medium text-gray-700">Product Image</label>
                    <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/png,image/gif" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg">
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Add Product</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-in;
    }
</style>