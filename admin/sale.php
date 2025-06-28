<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'];
    $saleDate = $_POST['sale_date'];
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $customerName = $_POST['customer_name'];

    $stmt = $conn->prepare("SELECT product_id, quantity FROM products WHERE product_name = ?");
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $productResult = $stmt->get_result();
    
    if ($productResult->num_rows > 0) {
        $product = $productResult->fetch_assoc();
        $productId = $product['product_id'];
        $currentStock = $product['quantity'];

        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE customer_name = ?");
        $stmt->bind_param("s", $customerName);
        $stmt->execute();
        $customerResult = $stmt->get_result();

        if ($customerResult->num_rows > 0) {
            $customerId = $customerResult->fetch_assoc()['customer_id'];

            if ($quantity <= $currentStock && $quantity > 0) {
                $newStockQuantity = $currentStock - $quantity;
                $totalPrice = $quantity * $price;

                $stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE product_id = ?");
                $stmt->bind_param("ii", $newStockQuantity, $productId);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO sales (product_id, customer_id, sale_date, quantity, price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisidd", $productId, $customerId, $saleDate, $quantity, $price, $totalPrice);
                if ($stmt->execute()) {
                    $success = "Sale recorded successfully!";
                } else {
                    $error = "Failed to record sale: " . htmlspecialchars($conn->error);
                }
            } else {
                $error = "Not enough stock available or invalid quantity.";
            }
        } else {
            $error = "Customer not found.";
        }
    } else {
        $error = "Product not found.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 font-sans">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-6 tracking-tight">Record Sale</h1>
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
            <form method="post" action="" class="space-y-6">
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <select id="product_name" name="product_name" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        <option value="">Select a product</option>
                        <?php
                        $productResult = $conn->query("SELECT product_name FROM products");
                        while ($product = $productResult->fetch_assoc()) {
                            echo "<option value=\"" . htmlspecialchars($product['product_name']) . "\">" . htmlspecialchars($product['product_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="sale_date" class="block text-sm font-medium text-gray-700">Sale Date</label>
                    <input type="date" id="sale_date" name="sale_date" max="<?php echo date('Y-m-d'); ?>" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                </div>
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                    <select id="customer_name" name="customer_name" required class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        <option value="">Select a customer</option>
                        <?php
                        $customerResult = $conn->query("SELECT customer_name FROM customers");
                        while ($customer = $customerResult->fetch_assoc()) {
                            echo "<option value=\"" . htmlspecialchars($customer['customer_name']) . "\">" . htmlspecialchars($customer['customer_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-green-500 text-white p-3 rounded-lg hover:bg-teal-600 transform hover:scale-105 transition duration-200">Record Sale</button>
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