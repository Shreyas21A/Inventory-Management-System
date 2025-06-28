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
    <title>Manage Sellers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
</head>
<body class="bg-blue-50 font-sans animate-scale-in">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-8 tracking-tight flex items-center">
            <svg class="w-8 h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5c4.418 0 8 3.582 8 8s-3.582 8-8 8-8-3.582-8-8 3.582-8 8-8m0 16c-2.761 0-5-2.239-5-5m10 0c0 2.761-2.239 5-5 5"></path>
            </svg>
            Manage Sellers
        </h1>

        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            <div class="flex justify-between mb-6">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5c4.418 0 8 3.582 8 8s-3.582 8-8 8-8-3.582-8-8 3.582-8 8-8m0 16c-2.761 0-5-2.239-5-5m10 0c0 2.761-2.239 5-5 5"></path>
                    </svg>
                    Seller List
                </h2>
                <div class="flex space-x-4">
                    <input type="text" id="sellerSearch" class="p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200" placeholder="Search by name...">
                    <button id="clearSearch" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Clear</button>
                </div>
            </div>
            <div id="sellerTable" class="overflow-x-auto"></div>
        </div>

        <!-- Update Modal -->
        <div id="updateSellerModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full">
                <h2 class="text-xl font-semibold text-indigo-700 mb-4">Update Seller</h2>
                <form id="updateSellerForm" class="space-y-4">
                    <input type="hidden" name="seller_id" id="sellerId">
                    <input type="text" name="seller_name" id="sellerName" placeholder="Seller Name" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <input type="email" name="email" id="sellerEmail" placeholder="Email" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <input type="tel" name="phone" id="sellerPhone" placeholder="Phone" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <input type="text" name="address" id="sellerAddress" placeholder="Address" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Update</button>
                        <button type="button" onclick="closeModal('updateSellerModal')" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php $conn->close(); ?>

    <script>
        $(document).ready(function() {
            loadSellers();

            $('#sellerSearch').on('input', function() {
                if ($(this).val().length > 2 || $(this).val().length === 0) {
                    loadSellers();
                }
            });

            $('#clearSearch').click(function() {
                $('#sellerSearch').val('');
                loadSellers();
            });

            function loadSellers() {
                $.ajax({
                    url: 'manage_sellers_handler.php',
                    method: 'POST',
                    data: { search: $('#sellerSearch').val() },
                    dataType: 'json',
                    success: function(response) {
                        renderSellers(response.sellers);
                    },
                    error: function() {
                        $('#sellerTable').html('<p class="text-red-700">Error loading sellers.</p>');
                    }
                });
            }

            function renderSellers(sellers) {
                let html = '<table class="min-w-full divide-y divide-gray-200">' +
                    '<thead class="bg-gray-50">' +
                    '<tr>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>' +
                    '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                if (sellers.length === 0) {
                    html += '<tr><td colspan="5" class="px-6 py-4 text-sm text-gray-900 text-center">No sellers found.</td></tr>';
                } else {
                    sellers.forEach(s => {
                        html += `<tr class="hover:bg-blue-50 transition duration-200">` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${s.seller_name}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${s.email}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${s.phone || ''}</td>` +
                            `<td class="px-6 py-4 text-sm text-gray-900">${s.address || ''}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm">` +
                            `<button onclick="openUpdateModal(${s.seller_id}, '${s.seller_name}', '${s.email}', '${s.phone || ''}', '${s.address || ''}')" class="bg-gradient-to-r from-teal-500 to-green-500 text-white px-3 py-1 rounded-lg hover:bg-teal-600 mr-2">Update</button>` +
                            `<button onclick="confirmDelete(${s.seller_id}, 'seller')" class="bg-gradient-to-r from-red-600 to-red-700 text-white px-3 py-1 rounded-lg hover:bg-red-800">Delete</button>` +
                            `</td></tr>`;
                    });
                }
                html += '</tbody></table>';
                $('#sellerTable').html(html);
            }

            window.openUpdateModal = function(id, name, email, phone, address) {
                $('#sellerId').val(id);
                $('#sellerName').val(name);
                $('#sellerEmail').val(email);
                $('#sellerPhone').val(phone);
                $('#sellerAddress').val(address);
                $('#updateSellerModal').removeClass('hidden');
            };

            window.closeModal = function(modalId) {
                $(`#${modalId}`).addClass('hidden');
                $(`#${modalId} form`)[0].reset();
            };

            window.confirmDelete = function(id, type) {
                if (confirm(`Are you sure you want to delete this ${type}?`)) {
                    $.ajax({
                        url: 'manage_sellers_handler.php',
                        method: 'POST',
                        data: { action: 'delete', seller_id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                loadSellers();
                            } else {
                                alert(response.error);
                            }
                        }
                    });
                }
            };

            $('#updateSellerForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'manage_sellers_handler.php',
                    method: 'POST',
                    data: $(this).serialize() + '&action=update',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            closeModal('updateSellerModal');
                            loadSellers();
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