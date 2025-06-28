<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$productData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select'])) {
    $productId = (int)$_POST['product_id'];
    $stmt = $conn->prepare("SELECT product_name, description, image_path FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $productData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $productId = (int)$_POST['product_id'];
    $productName = $_POST['product_name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ? WHERE product_id = ?");
    $stmt->bind_param("ssi", $productName, $description, $productId);
    if ($stmt->execute()) {
        if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024;
            if (in_array($_FILES['product_image']['type'], $allowedTypes) && $_FILES['product_image']['size'] <= $maxSize) {
                $newImagePath = 'images/' . basename($_FILES['product_image']['name']);
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $newImagePath)) {
                    $stmt = $conn->prepare("UPDATE products SET image_path = ? WHERE product_id = ?");
                    $stmt->bind_param("si", $newImagePath, $productId);
                    $stmt->execute();
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image type or size (max 2MB, JPEG/PNG/GIF).";
            }
        }
        if (!$error) {
            $success = "Product updated successfully!";
        }
    } else {
        $error = "Failed to update product.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $productId = (int)$_POST['product_id'];

    $stmt = $conn->prepare("DELETE FROM purchases WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    
    $stmt = $conn->prepare("DELETE FROM sales WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        $success = "Product deleted successfully!";
    } else {
        $error = "Failed to delete product.";
    }
    $stmt->close();
}

$productsResult = $conn->query("SELECT product_id, product_name FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 font-sans">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-6 tracking-tight">Update or Delete Product</h1>
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg flex items-center space-x-2 animate-fade-in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-teal-100 text-teal-700 rounded-lg flex items-center space-x-2 animate-fade-in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            <form method="post" action="" class="mb-6">
                <div class="mb-4">
                    <label for="product_id" class="block text-sm font-medium text-gray-700">Select Product</label>
                    <select id="product_id" name="product_id" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        <option value="">Select a product</option>
                        <?php while ($product = $productsResult->fetch_assoc()): ?>
                            <option value="<?php echo $product['product_id']; ?>" <?php echo isset($productData['product_name']) && $product['product_name'] === $productData['product_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="select" class="w-full bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Load Product</button>
            </form>

            <?php if ($productData): ?>
                <form method="post" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_POST['product_id']); ?>">
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($productData['product_name']); ?>" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="4" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200"><?php echo htmlspecialchars($productData['description']); ?></textarea>
                    </div>
                    <div>
                        <label for="product_image" class="block text-sm font-medium text-gray-700">Product Image (optional)</label>
                        <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/png,image/gif" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Current: <img src="<?php echo htmlspecialchars($productData['image_path']); ?>" alt="Current Image" class="inline h-16 w-16 object-cover rounded-lg"></p>
                    </div>
                    <div class="flex space-x-4">
                        <button type="submit" name="update" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Update Product</button>
                        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this product?');" class="flex-1 bg-gradient-to-r from-red-600 to-red-700 text-white p-3 rounded-lg hover:bg-red-800 transform hover:scale-105 transition duration-200">Delete Product</button>
                    </div>
                </form>
            <?php endif; ?>
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