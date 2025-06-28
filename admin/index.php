<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch recent sales/purchases for chart (last 7 days)
$recentSalesQuery = "SELECT sale_date, SUM(total_price) as total FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY sale_date";
$recentPurchasesQuery = "SELECT purchase_date, SUM(total_cost) as total FROM purchases WHERE purchase_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY purchase_date";
$salesData = $conn->query($recentSalesQuery)->fetch_all(MYSQLI_ASSOC);
$purchasesData = $conn->query($recentPurchasesQuery)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-blue-50 font-sans animate-scale-in">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-8 tracking-tight flex items-center">
            <svg class="w-8 h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"></path>
            </svg>
            Inventory Management Dashboard
        </h1>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 animate-fade-in">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"></path>
                    </svg>
                    Products
                </h2>
                <p class="text-gray-500 mt-2">Manage your inventory products.</p>
                <button onclick="openModal('productModal')" class="mt-4 inline-block bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-200">Quick Add Product</button>
                <a href="search.php" class="mt-2 inline-block text-indigo-600 hover:underline">Search Products</a>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 animate-fade-in">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2zm0 8c-2.485 0-4.5-1.343-4.5-3s2.015-3 4.5-3 4.5 1.343 4.5 3-2.015 3-4.5 3z"></path>
                    </svg>
                    Sales
                </h2>
                <p class="text-gray-500 mt-2">Track and manage sales records.</p>
                <a href="sale.php" class="mt-4 inline-block bg-gradient-to-r from-teal-500 to-green-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transition duration-200">View Sales</a>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 animate-fade-in">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2V9a2 2 0 00-2-2h-2a2 2 0 00-2 2v10"></path>
                    </svg>
                    Reports
                </h2>
                <p class="text-gray-500 mt-2">Generate sales and inventory reports.</p>
                <a href="report.php" class="mt-4 inline-block bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-200">View Reports</a>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold text-indigo-700 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V15h5.488"></path>
                </svg>
                Quick Stats
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <?php
                $stats = [
                    ['label' => 'Total Products', 'query' => 'SELECT COUNT(*) as total FROM products'],
                    ['label' => 'Total Sales', 'query' => 'SELECT COUNT(*) as total FROM sales'],
                    ['label' => 'Total Customers', 'query' => 'SELECT COUNT(*) as total FROM customers', 'modal' => 'customerModal'],
                    ['label' => 'Total Sellers', 'query' => 'SELECT COUNT(*) as total FROM sellers', 'modal' => 'sellerModal']
                ];
                foreach ($stats as $stat): ?>
                    <div class="text-center p-4 bg-blue-50 rounded-lg animate-fade-in">
                        <p class="text-gray-500"><?php echo $stat['label']; ?></p>
                        <?php
                        $result = $conn->query($stat['query']);
                        $total = $result->fetch_assoc()['total'];
                        ?>
                        <p class="text-2xl font-bold text-indigo-800 counter" data-target="<?php echo $total; ?>">0</p>
                        <?php if (isset($stat['modal'])): ?>
                            <button onclick="openModal('<?php echo $stat['modal']; ?>')" class="mt-2 text-indigo-600 hover:underline">Quick Add</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="productModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full">
            <h2 class="text-xl font-semibold text-indigo-700 mb-4">Add Product</h2>
            <form id="addProductForm" class="space-y-4">
                <input type="text" name="product_name" placeholder="Product Name" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <textarea name="description" placeholder="Description" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400"></textarea>
                <input type="number" name="quantity" placeholder="Quantity" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="number" name="price" placeholder="Price" step="0.01" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="file" name="image" accept="image/*" class="p-3 border border-gray-300 rounded-lg w-full">
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Add</button>
                    <button type="button" onclick="closeModal('productModal')" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div id="customerModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full">
            <h2 class="text-xl font-semibold text-indigo-700 mb-4">Add Customer</h2>
            <form id="addCustomerForm" class="space-y-4">
                <input type="text" name="customer_name" placeholder="Customer Name" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="email" name="email" placeholder="Email" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="tel" name="phone" placeholder="Phone" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="text" name="address" placeholder="Address" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Add</button>
                    <button type="button" onclick="closeModal('customerModal')" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div id="sellerModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full">
            <h2 class="text-xl font-semibold text-indigo-700 mb-4">Add Seller</h2>
            <form id="addSellerForm" class="space-y-4">
                <input type="text" name="seller_name" placeholder="Seller Name" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="email" name="email" placeholder="Email" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="tel" name="phone" placeholder="Phone" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <input type="text" name="address" placeholder="Address" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Add</button>
                    <button type="button" onclick="closeModal('sellerModal')" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>
        <!-- Product List -->
        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            <h2 class="text-xl font-semibold text-indigo-700 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"></path>
                </svg>
                Product List
            </h2>
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:space-x-4">
                <input type="text" id="productSearch" class="p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200" placeholder="Search by product name...">
                <select id="sortBy" class="mt-2 md:mt-0 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                    <option value="product_name">Sort by Name</option>
                    <option value="quantity">Sort by Quantity</option>
                    <option value="price">Sort by Price</option>
                </select>
                <button id="clearFilters" class="mt-2 md:mt-0 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Clear</button>
            </div>
            <div id="productTable" class="overflow-x-auto"></div>
            <div id="pagination" class="mt-6 flex justify-center space-x-2"></div>
        </div>

        
        <!-- Recent Activity Chart -->
        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            <h2 class="text-xl font-semibold text-indigo-700 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                </svg>
                Recent Activity (Last 7 Days)
            </h2>
            <div class="max-w-md mx-auto mb-6">
                <canvas id="activityChart" class="h-64"></canvas>
            </div>
        </div>

    <?php include 'footer.php'; ?>
    <?php $conn->close(); ?>

    <script>
        $(document).ready(function() {
            const perPage = 10;
            let currentPage = 1;
            let totalResults = 0;

            // Load initial product list
            loadProducts();

            // Chart
            const salesData = <?php echo json_encode($salesData); ?>;
            const purchasesData = <?php echo json_encode($purchasesData); ?>;
            const dates = [...new Set([...salesData.map(s => s.sale_date), ...purchasesData.map(p => p.purchase_date)])].sort();
            new Chart(document.getElementById('activityChart'), {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Sales',
                            data: dates.map(d => salesData.find(s => s.sale_date === d)?.total || 0),
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1
                        },
                        {
                            label: 'Purchases',
                            data: dates.map(d => purchasesData.find(p => p.purchase_date === d)?.total || 0),
                            backgroundColor: 'rgba(79, 70, 229, 0.6)',
                            borderColor: 'rgb(79, 70, 229)',
                            borderWidth: 1
                        }
                    ]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            // Product filtering
            $('#productSearch, #sortBy').on('input change', function() {
                currentPage = 1;
                loadProducts();
            });

            $('#clearFilters').click(function() {
                $('#productSearch').val('');
                $('#sortBy').val('product_name');
                currentPage = 1;
                loadProducts();
            });

            function loadProducts() {
                $.ajax({
                    url: 'dashboard_handler.php',
                    method: 'POST',
                    data: {
                        search: $('#productSearch').val(),
                        sort_by: $('#sortBy').val(),
                        page: currentPage,
                        per_page: perPage
                    },
                    dataType: 'json',
                    success: function(response) {
                        totalResults = response.total;
                        renderProducts(response.products);
                        renderPagination();
                    },
                    error: function() {
                        $('#productTable').html('<p class="text-red-700">Error loading products.</p>');
                    }
                });
            }

            function renderProducts(products) {
                let html = '<table class="min-w-full divide-y divide-gray-200">' +
                    '<thead class="bg-gray-50">' +
                    '<tr>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>' +
                    '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                if (products.length === 0) {
                    html += '<tr><td colspan="5" class="px-6 py-4 text-sm text-gray-900 text-center">No products found.</td></tr>';
                } else {
                    products.forEach(p => {
                        html += `<tr class="hover:bg-blue-50 transition duration-200">` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${p.product_name}</td>` +
                            `<td class="px-6 py-4 text-sm text-gray-900">${p.description}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${p.quantity}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(p.price).toFixed(2)}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><img src="${p.image_path}" alt="${p.product_name}" class="h-16 w-16 object-cover rounded-lg"></td>` +
                            `</tr>`;
                    });
                }
                html += '</tbody></table>';
                $('#productTable').html(html);
            }

            function renderPagination() {
                let html = '';
                const totalPages = Math.ceil(totalResults / perPage);
                if (totalPages > 1) {
                    html += `<button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" ${currentPage === 1 ? 'disabled' : ''} onclick="currentPage--; loadProducts()">Previous</button>`;
                    for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                        html += `<button class="px-4 py-2 ${i === currentPage ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'} rounded-lg hover:bg-indigo-700 hover:text-white" onclick="currentPage=${i}; loadProducts()">${i}</button>`;
                    }
                    html += `<button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" ${currentPage === totalPages ? 'disabled' : ''} onclick="currentPage++; loadProducts()">Next</button>`;
                }
                $('#pagination').html(html);
            }

            // Animated counters
            $('.counter').each(function() {
                const target = parseInt($(this).data('target'));
                let count = 0;
                const increment = target / 100;
                const updateCount = () => {
                    if (count < target) {
                        count = Math.min(count + increment, target);
                        $(this).text(Math.ceil(count));
                        requestAnimationFrame(updateCount.bind(this));
                    }
                };
                requestAnimationFrame(updateCount.bind(this));
            });

            // Modal handling
            window.openModal = function(modalId) {
                $(`#${modalId}`).removeClass('hidden');
            };
            window.closeModal = function(modalId) {
                $(`#${modalId}`).addClass('hidden');
                $(`#${modalId} form`)[0].reset();
            };

            // Form submissions
            $('#addProductForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                $.ajax({
                    url: 'addproduct_handler.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            closeModal('productModal');
                            loadProducts();
                            updateStats();
                        } else {
                            alert(response.error);
                        }
                    }
                });
            });

            $('#addCustomerForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'addcustomer_handler.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            closeModal('customerModal');
                            updateStats();
                        } else {
                            alert(response.error);
                        }
                    }
                });
            });

            $('#addSellerForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'addseller_handler.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            closeModal('sellerModal');
                            updateStats();
                        } else {
                            alert(response.error);
                        }
                    }
                });
            });

            function updateStats() {
                $.ajax({
                    url: 'dashboard_handler.php',
                    method: 'POST',
                    data: { action: 'stats' },
                    dataType: 'json',
                    success: function(response) {
                        $('.counter').each(function(i) {
                            $(this).data('target', response.stats[i].total).text(0);
                            const target = parseInt($(this).data('target'));
                            let count = 0;
                            const increment = target / 100;
                            const updateCount = () => {
                                if (count < target) {
                                    count = Math.min(count + increment, target);
                                    $(this).text(Math.ceil(count));
                                    requestAnimationFrame(updateCount.bind(this));
                                }
                            };
                            requestAnimationFrame(updateCount.bind(this));
                        });
                    }
                });
            }
        });
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-fade-in { animation: fadeIn 0.5s ease-in; }
        .animate-scale-in { animation: scaleIn 0.5s ease-out; }
    </style>
</body>
</html>