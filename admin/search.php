<?php
session_start();
 include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale="1.0">
    <title>Search Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-blue-50 font-sans">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-8 tracking-tight">Search Inventory</h3>
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-3xl mx-auto mb-8">
            <div id="errorMessage" class="hidden mb-6 p-4 bg-red-100 text-red-700 rounded-lg flex items-center space-x-2 animate-fade-in">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span></span>
            </div>
            <form id="searchForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="search_type" class="block text-sm font-medium text-gray-700">Search Type</label>
                        <select id="search_type" name="search_type" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                            <option value="product">Product</option>
                            <option value="customer">Customer</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                    <div>
                        <label for="search_keyword" class="block text-sm font-medium text-gray-700">Keyword</label>
                        <input type="text" id="search_keyword" name="search_keyword" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                    </div>
                </div>
                <div id="product_filters" class="hidden space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="min_price" class="block text-sm font-medium text-gray-700">Min Price</label>
                            <input type="number" id="min_price" name="min_price" step="0.01" min="0" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        </div>
                        <div>
                            <label for="max_price" class="block text-sm font-medium text-gray-700">Max Price</label>
                            <input type="number" id="max_price" name="max_price" step="0.01" min="0" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                        </div>
                    </div>
                    <div>
                        <label for="min_quantity" class="block text-sm font-medium text-gray-700">Min Quantity</label>
                        <input type="number" id="min_quantity" name="min_quantity" min="0" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                    </div>
                </div>
                <div id="person_filters" class="hidden">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" id="phone" name="phone" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200">
                </div>
                <div class="flex space-x-4">
                    <button type="button" id="searchButton" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Search</button>
                    <button type="button" id="clearButton" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Clear</button>
                </div>
            </form>
        </div>

        <div id="searchResults" class="bg-white p-8 rounded-xl shadow-lg hidden">
            <h2 class="text-xl font-semibold text-indigo-700 mb-6">Search Results</h2>
            <div id="resultsTable" class="overflow-x-auto"></div>
            <div id="pagination" class="mt-6 flex justify-center space-x-2"></div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        $(document).ready(function() {
            const perPage = 10;
            let currentPage = 1;
            let totalResults = 0;

            function toggleFilters() {
                const searchType = $('#search_type').val();
                $('#product_filters').toggleClass('hidden', searchType !== 'product');
                $('#person_filters').toggleClass('hidden', !['customer', 'seller'].includes(searchType));
            }

            $('#search_type').change(toggleFilters);
            toggleFilters();

            function performSearch(page = 1) {
                const formData = $('#searchForm').serializeArray();
                formData.push({ name: 'page', value: page }, { name: 'per_page', value: perPage });

                $.ajax({
                    url: 'search_handler.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            $('#errorMessage span').text(response.error);
                            $('#errorMessage').removeClass('hidden');
                            $('#searchResults').addClass('hidden');
                            return;
                        }

                        $('#errorMessage').addClass('hidden');
                        totalResults = response.total;
                        renderResults(response.results, response.search_type);
                        renderPagination(page, Math.ceil(totalResults / perPage));
                        $('#searchResults').removeClass('hidden');
                    },
                    error: function() {
                        $('#errorMessage span').text('An error occurred while searching.');
                        $('#errorMessage').removeClass('hidden');
                        $('#searchResults').addClass('hidden');
                    }
                });
            }

            function renderResults(results, searchType) {
                let html = '<table class="min-w-full divide-y divide-gray-200">';
                html += '<thead class="bg-gray-50">';
                if (searchType === 'product') {
                    html += '<tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th></tr>';
                } else {
                    html += '<tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>' +
                            '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th></tr>';
                }
                html += '</thead><tbody class="bg-white divide-y divide-gray-200">';

                if (results.length === 0) {
                    html += '<tr><td colspan="' + (searchType === 'product' ? 6 : 5) + '" class="px-6 py-4 text-sm text-gray-900 text-center">No results found.</td></tr>';
                } else {
                    results.forEach(row => {
                        html += '<tr class="hover:bg-blue-50 transition duration-200">';
                        if (searchType === 'product') {
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.product_id}</td>` +
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.product_name}</td>` +
                                    `<td class="px-6 py-4 text-sm text-gray-900">${row.description}</td>` +
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.quantity}</td>` +
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(row.price).toFixed(2)}</td>` +
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><img src="${row.image_path}" alt="Product Image" class="h-16 w-16 object-cover rounded-lg"></td>`;
                        } else {
                            const idKey = searchType === 'customer' ? 'customer_id' : 'seller_id';
                            const nameKey = searchType === 'customer' ? 'customer_name' : 'seller_name';
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row[idKey]}</td>` +
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row[nameKey]}</td>` +
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.email}</td>` +
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.phone}</td>` +
                                    `<td class="px-6 py-4 text-sm text-gray-900">${row.address}</td>`;
                        }
                        html += '</tr>';
                    });
                }
                html += '</tbody></table>';
                $('#resultsTable').html(html);
            }

            function renderPagination(current, total) {
                let html = '';
                if (total > 1) {
                    html += `<button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 ${current === 1 ? 'opacity-50 cursor-not-allowed' : ''}" ${current === 1 ? 'disabled' : ''} onclick="performSearch(${current - 1})">Previous</button>`;
                    for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
                        html += `<button class="px-4 py-2 ${i === current ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'} rounded-lg hover:bg-indigo-700 hover:text-white" onclick="performSearch(${i})">${i}</button>`;
                    }
                    html += `<button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 ${current === total ? 'opacity-50 cursor-not-allowed' : ''}" ${current === total ? 'disabled' : ''} onclick="performSearch(${current + 1})">Next</button>`;
                }
                $('#pagination').html(html);
            }

            $('#searchButton').click(function() {
                currentPage = 1;
                performSearch();
            });

            $('#search_keyword').on('input', function() {
                if ($(this).val().length > 2) {
                    currentPage = 1;
                    performSearch();
                }
            });

            $('#clearButton').click(function() {
                $('#searchForm')[0].reset();
                toggleFilters();
                $('#searchResults').addClass('hidden');
                $('#errorMessage').addClass('hidden');
            });
        });
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