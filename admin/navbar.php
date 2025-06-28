<?php
// session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <nav class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-4 sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center space-x-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-xl font-bold tracking-tight">InventoryPro</span>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-6 items-center">
                <a href="index.php" class="text-white hover:text-teal-200 transition duration-200">Dashboard</a>
                <!-- Products Dropdown -->
                <div class="relative group">
                    <button class="text-white hover:text-teal-200 transition duration-200 flex items-center">
                        Products
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-indigo-700 rounded-lg shadow-lg hidden group-hover:block z-50">
                        <a href="addproduct.php" class="block px-4 py-2 text-white hover:text-teal-200 hover:bg-indigo-800 transition duration-200 rounded-t-lg">Add Product</a>
                        <a href="manage_products.php" class="block px-4 py-2 text-white hover:text-teal-200 hover:bg-indigo-800 transition duration-200 rounded-b-lg">Manage Products</a>
                    </div>
                </div>
                <a href="sale.php" class="text-white hover:text-teal-200 transition duration-200">Sales</a>
                <a href="purchase.php" class="text-white hover:text-teal-200 transition duration-200">Purchases</a>
                <!-- Customers Dropdown -->
                <div class="relative group">
                    <button class="text-white hover:text-teal-200 transition duration-200 flex items-center">
                        Customers
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-indigo-700 rounded-lg shadow-lg hidden group-hover:block z-50">
                        <a href="customer.php" class="block px-4 py-2 text-white hover:text-teal-200 hover:bg-indigo-800 transition duration-200 rounded-t-lg">Add Customer</a>
                        <a href="manage_customers.php" class="block px-4 py-2 text-white hover:text-teal-200 hover:bg-indigo-800 transition duration-200 rounded-b-lg">Manage Customers</a>
                    </div>
                </div>
                <!-- Sellers Dropdown -->
                <div class="relative group">
                    <button class="text-white hover:text-teal-200 transition duration-200 flex items-center">
                        Sellers
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-indigo-700 rounded-lg shadow-lg hidden group-hover:block z-50">
                        <a href="seller.php" class="block px-4 py-2 text-white hover:text-teal-200 hover:bg-indigo-800 transition duration-200 rounded-t-lg">Add Seller</a>
                        <a href="manage_sellers.php" class="block px-4 py-2 text-white hover:text-teal-200 hover:bg-indigo-800 transition duration-200 rounded-b-lg">Manage Sellers</a>
                    </div>
                </div>
                <a href="search.php" class="text-white hover:text-teal-200 transition duration-200">Search</a>
                <a href="report.php" class="text-white hover:text-teal-200 transition duration-200">Reports</a>
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="logout.php" class="text-white hover:text-red-300 transition duration-200">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-white hover:text-teal-200 transition duration-200">Login</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden bg-indigo-700 text-white flex-col space-y-2 p-4 absolute top-16 left-0 w-full">
                <a href="index.php" class="block hover:text-teal-200 transition duration-200">Dashboard</a>
                <!-- Products Dropdown Mobile -->
                <div>
                    <button data-toggle="products-menu" class="w-full text-left hover:text-teal-200 transition duration-200 flex items-center justify-between">
                        Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="products-menu" class="hidden pl-4 space-y-2">
                        <a href="addproduct.php" class="block hover:text-teal-200 transition duration-200">Add Product</a>
                        <a href="manage_products.php" class="block hover:text-teal-200 transition duration-200">Manage Products</a>
                    </div>
                </div>
                <a href="sale.php" class="block hover:text-teal-200 transition duration-200">Sales</a>
                <a href="purchase.php" class="block hover:text-teal-200 transition duration-200">Purchases</a>
                <!-- Customers Dropdown Mobile -->
                <div>
                    <button data-toggle="customers-menu" class="w-full text-left hover:text-teal-200 transition duration-200 flex items-center justify-between">
                        Customers
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="customers-menu" class="hidden pl-4 space-y-2">
                        <a href="customer.php" class="block hover:text-teal-200 transition duration-200">Add Customer</a>
                        <a href="manage_customers.php" class="block hover:text-teal-200 transition duration-200">Manage Customers</a>
                    </div>
                </div>
                <!-- Sellers Dropdown Mobile -->
                <div>
                    <button data-toggle="sellers-menu" class="w-full text-left hover:text-teal-200 transition duration-200 flex items-center justify-between">
                        Sellers
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="sellers-menu" class="hidden pl-4 space-y-2">
                        <a href="seller.php" class="block hover:text-teal-200 transition duration-200">Add Seller</a>
                        <a href="manage_sellers.php" class="block hover:text-teal-200 transition duration-200">Manage Sellers</a>
                    </div>
                </div>
                <a href="search.php" class="block hover:text-teal-200 transition duration-200">Search</a>
                <a href="report.php" class="block hover:text-teal-200 transition duration-200">Reports</a>
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="logout.php" class="block hover:text-red-300 transition duration-200">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block hover:text-teal-200 transition duration-200">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Dropdown toggle for mobile
        document.querySelectorAll('[data-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-toggle');
                const targetMenu = document.getElementById(targetId);
                targetMenu.classList.toggle('hidden');
            });
        });
    </script>
</body>
</html>