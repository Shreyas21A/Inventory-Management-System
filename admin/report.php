<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$productResult = $conn->query("SELECT product_name, quantity FROM products");
$productsData = $productResult->fetch_all(MYSQLI_ASSOC);

$purchaseQuery = "SELECT purchases.purchase_date, products.product_name, sellers.seller_name, purchases.quantity, purchases.price, purchases.total_cost
                 FROM purchases
                 INNER JOIN products ON purchases.product_id = products.product_id
                 INNER JOIN sellers ON purchases.seller_id = sellers.seller_id";
$saleQuery = "SELECT sales.sale_date, products.product_name, customers.customer_name, sales.quantity, sales.price, sales.total_price
              FROM sales
              INNER JOIN products ON sales.product_id = products.product_id
              INNER JOIN customers ON sales.customer_id = customers.customer_id";

$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
if ($startDate && $endDate) {
    $purchaseQuery .= " WHERE purchases.purchase_date BETWEEN ? AND ?";
    $saleQuery .= " WHERE sales.sale_date BETWEEN ? AND ?";
}

$stmt = $conn->prepare($purchaseQuery);
if ($startDate && $endDate) {
    $stmt->bind_param("ss", $startDate, $endDate);
}
$stmt->execute();
$purchasesData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare($saleQuery);
if ($startDate && $endDate) {
    $stmt->bind_param("ss", $startDate, $endDate);
}
$stmt->execute();
$salesData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sellerResult = $conn->query("SELECT * FROM sellers");
$customerResult = $conn->query("SELECT * FROM customers");

$totalPurchaseCost = array_sum(array_column($purchasesData, 'total_cost'));
$totalSaleRevenue = array_sum(array_column($salesData, 'total_price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-blue-50 font-sans">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-8 tracking-tight">Reports</h1>

        <div class="border-b border-gray-200 mb-8">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="showTab('product-tab')" class="tab-button border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active:border-indigo-600 active:text-indigo-700">Products</button>
                <button onclick="showTab('purchase-tab')" class="tab-button border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Purchases</button>
                <button onclick="showTab('sale-tab')" class="tab-button border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Sales</button>
                <button onclick="showTab('seller-tab')" class="tab-button border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Sellers</button>
                <button onclick="showTab('customer-tab')" class="tab-button border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Customers</button>
            </nav>
        </div>

        <div id="product-tab" class="tab-content">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-indigo-700 mb-6">Product Report</h2>
                <div class="flex justify-center mb-6">
                    <div style="width: 300px; height: 300px;">
                        <canvas id="productChart"></canvas>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($productsData as $product): ?>
                                <tr class="hover:bg-blue-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $product['quantity']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="purchase-tab" class="tab-content hidden">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-indigo-700 mb-6">Purchase Report</h2>
                <div class="mb-6">
                    <form id="purchaseFilter" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="purchase_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="purchase_start_date" name="start_date" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        </div>
                        <div>
                            <label for="purchase_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" id="purchase_end_date" name="end_date" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="mb-6 flex space-x-4">
                    <button onclick="exportData('purchase', 'csv')" class="bg-gradient-to-r from-teal-500 to-green-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transform hover:scale-105 transition duration-200">Export CSV</button>
                    <button onclick="exportData('purchase', 'json')" class="bg-gradient-to-r from-teal-500 to-green-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transform hover:scale-105 transition duration-200">Export JSON</button>
                </div>
                <p class="text-lg font-semibold text-indigo-700 mb-4">Total Cost: ₹<?php echo number_format($totalPurchaseCost, 2); ?></p>
                <div class="max-w-md mx-auto mb-6">
                    <canvas id="purchaseChart" class="h-64"></canvas>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($purchasesData as $purchase): ?>
                                <tr class="hover:bg-blue-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($purchase['purchase_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($purchase['product_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($purchase['seller_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $purchase['quantity']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($purchase['price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($purchase['total_cost'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="sale-tab" class="tab-content hidden">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-indigo-700 mb-6">Sales Report</h2>
                <div class="mb-6">
                    <form id="saleFilter" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="sale_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="sale_start_date" name="start_date" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        </div>
                        <div>
                            <label for="sale_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" id="sale_end_date" name="end_date" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="mb-6 flex space-x-4">
                    <button onclick="exportData('sale', 'csv')" class="bg-gradient-to-r from-teal-500 to-green-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transform hover:scale-105 transition duration-200">Export CSV</button>
                    <button onclick="exportData('sale', 'json')" class="bg-gradient-to-r from-teal-500 to-green-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transform hover:scale-105 transition duration-200">Export JSON</button>
                </div>
                <p class="text-lg font-semibold text-indigo-700 mb-4">Total Revenue: ₹<?php echo number_format($totalSaleRevenue, 2); ?></p>
                <div class="max-w-md mx-auto mb-6">
                    <canvas id="salesChart" class="h-64"></canvas>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($salesData as $sale): ?>
                                <tr class="hover:bg-blue-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $sale['quantity']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($sale['price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($sale['total_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="seller-tab" class="tab-content hidden">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-indigo-700 mb-6">Seller Report</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($seller = $sellerResult->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($seller['seller_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($seller['seller_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($seller['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($seller['phone']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($seller['address']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="customer-tab" class="tab-content hidden">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-indigo-700 mb-6">Customer Report</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($customer = $customerResult->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($customer['address']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-indigo-600', 'text-indigo-700');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            document.getElementById(tabId).classList.remove('hidden');
            document.querySelector(`button[onclick="showTab('${tabId}')"]`).classList.add('border-indigo-600', 'text-indigo-700');
        }
        showTab('product-tab');

        const productData = <?php echo json_encode($productsData); ?>;
        new Chart(document.getElementById('productChart'), {
            type: 'pie',
            data: {
                labels: productData.map(p => p.product_name),
                datasets: [{
                    data: productData.map(p => p.quantity),
                    backgroundColor: ['#4f46e5', '#06b6d4', '#14b8a6', '#f59e0b', '#ef4444'],
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: true,
                plugins: { 
                    legend: { 
                        position: 'top' 
                    } 
                }
            }
        });

        let purchaseChart, salesChart;
        function updateCharts(purchaseData, salesData) {
            if (purchaseChart) purchaseChart.destroy();
            if (salesChart) salesChart.destroy();

            purchaseChart = new Chart(document.getElementById('purchaseChart'), {
                type: 'bar',
                data: {
                    labels: purchaseData.map(p => p.purchase_date),
                    datasets: [{
                        label: 'Total Cost',
                        data: purchaseData.map(p => p.total_cost),
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'rgb(79, 70, 229)',
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            salesChart = new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels: salesData.map(s => s.sale_date),
                    datasets: [{
                        label: 'Total Price',
                        data: salesData.map(s => s.total_price),
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        }

        updateCharts(<?php echo json_encode($purchasesData); ?>, <?php echo json_encode($salesData); ?>);

        document.getElementById('purchaseFilter').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('report.php', { method: 'POST', body: formData })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newPurchaseTable = doc.querySelector('#purchase-tab table').outerHTML;
                    const newTotalCost = doc.querySelector('#purchase-tab p').textContent;
                    document.querySelector('#purchase-tab table').outerHTML = newPurchaseTable;
                    document.querySelector('#purchase-tab p').textContent = newTotalCost;
                    updateCharts(JSON.parse(doc.querySelector('#purchase-tab script').textContent), salesData);
                });
        });

        document.getElementById('saleFilter').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('report.php', { method: 'POST', body: formData })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newSaleTable = doc.querySelector('#sale-tab table').outerHTML;
                    const newTotalRevenue = doc.querySelector('#sale-tab p').textContent;
                    document.querySelector('#sale-tab table').outerHTML = newSaleTable;
                    document.querySelector('#sale-tab p').textContent = newTotalRevenue;
                    updateCharts(purchaseData, JSON.parse(doc.querySelector('#sale-tab script').textContent));
                });
        });

        function exportData(type, format) {
            let data = type === 'purchase' ? <?php echo json_encode($purchasesData); ?> : <?php echo json_encode($salesData); ?>;
            if (format === 'csv') {
                const headers = Object.keys(data[0]).join(',');
                const rows = data.map(row => Object.values(row).map(v => `"${v}"`).join(',')).join('\n');
                const csv = headers + '\n' + rows;
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${type}_report.csv`;
                a.click();
            } else {
                const json = JSON.stringify(data, null, 2);
                const blob = new Blob([json], { type: 'application/json' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${type}_report.json`;
                a.click();
            }
        }
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
    </style>
</body>
</html>