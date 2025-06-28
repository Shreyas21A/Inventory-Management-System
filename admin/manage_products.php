<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
</head>
<body class="bg-blue-50 font-sans animate-scale-in">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-8 tracking-tight flex items-center">
            <svg class="w-8 h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"></path>
            </svg>
            Manage Products
        </h1>

        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            <div class="flex justify-between mb-6">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"></path>
                    </svg>
                    Product List
                </h2>
                <div class="flex space-x-4">
                    <input type="text" id="productSearch" class="p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200" placeholder="Search by name...">
                    <button id="clearSearch" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Clear</button>
                </div>
            </div>
            <div id="productTable" class="overflow-x-auto"></div>
        </div>

        <!-- Update Modal -->
        <div id="updateProductModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full">
                <h2 class="text-xl font-semibold text-indigo-700 mb-4">Update Product</h2>
                <form id="updateProductForm" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="product_id" id="productId">
                    <input type="text" name="product_name" id="productName" placeholder="Product Name" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <textarea name="description" id="productDescription" placeholder="Description" rows="4" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400"></textarea>
                    <input type="number" name="quantity" id="productQuantity" placeholder="Quantity" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <input type="number" name="price" id="productPrice" placeholder="Price" step="0.01" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <div>
                        <label for="productImage" class="block text-sm font-medium text-gray-700">Product Image (optional)</label>
                        <input type="file" name="product_image" id="productImage" accept="image/jpeg,image/png,image/gif" class="p-3 border border-gray-300 rounded-lg w-full">
                        <p class="text-sm text-gray-500 mt-1">Current: <img id="currentImage" src="" alt="Current Image" class="inline h-16 w-16 object-cover rounded-lg"></p>
                    </div>
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Update</button>
                        <button type="button" onclick="closeModal('updateProductModal')" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php $conn->close(); ?>

    <script>
        $(document).ready(function() {
            loadProducts();

            $('#productSearch').on('input', function() {
                if ($(this).val().length > 2 || $(this).val().length === 0) {
                    loadProducts();
                }
            });

            $('#clearSearch').click(function() {
                $('#productSearch').val('');
                loadProducts();
            });

            function loadProducts() {
                $.ajax({
                    url: 'manage_products_handler.php',
                    method: 'POST',
                    data: { search: $('#productSearch').val() },
                    dataType: 'json',
                    success: function(response) {
                        renderProducts(response.products);
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
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>' +
                    '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                if (products.length === 0) {
                    html += '<tr><td colspan="6" class="px-6 py-4 text-sm text-gray-900 text-center">No products found.</td></tr>';
                } else {
                    products.forEach(p => {
                        html += `<tr class="hover:bg-blue-50 transition duration-200">` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${p.product_name}</td>` +
                            `<td class="px-6 py-4 text-sm text-gray-900">${p.description || ''}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${p.quantity}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(p.price).toFixed(2)}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><img src="${p.image_path || ''}" alt="${p.product_name}" class="h-16 w-16 object-cover rounded-lg"></td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm">` +
                            `<button onclick="openUpdateModal(${p.product_id}, '${p.product_name}', '${p.description || ''}', ${p.quantity}, ${p.price}, '${p.image_path || ''}')" class="bg-gradient-to-r from-teal-500 to-green-500 text-white px-3 py-1 rounded-lg hover:bg-teal-600 mr-2">Update</button>` +
                            `<button onclick="confirmDelete(${p.product_id}, 'product')" class="bg-gradient-to-r from-red-600 to-red-700 text-white px-3 py-1 rounded-lg hover:bg-red-800">Delete</button>` +
                            `</td></tr>`;
                    });
                }
                html += '</tbody></table>';
                $('#productTable').html(html);
            }

            window.openUpdateModal = function(id, name, description, quantity, price, image_path) {
                $('#productId').val(id);
                $('#productName').val(name);
                $('#productDescription').val(description);
                $('#productQuantity').val(quantity);
                $('#productPrice').val(price);
                $('#currentImage').attr('src', image_path || '');
                $('#updateProductModal').removeClass('hidden');
            };

            window.closeModal = function(modalId) {
                $(`#${modalId}`).addClass('hidden');
                $(`#${modalId} form`)[0].reset();
                $('#currentImage').attr('src', '');
            };

            window.confirmDelete = function(id, type) {
                if (confirm(`Are you sure you want to delete this ${type}?`)) {
                    $.ajax({
                        url: 'manage_products_handler.php',
                        method: 'POST',
                        data: { action: 'delete', product_id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                loadProducts();
                            } else {
                                alert(response.error);
                            }
                        }
                    });
                }
            };

            $('#updateProductForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'update');
                $.ajax({
                    url: 'manage_products_handler.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            closeModal('updateProductModal');
                            loadProducts();
                        } else {
                            alert(response.error);
                        }
                    }
                });
            });
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