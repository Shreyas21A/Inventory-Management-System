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
    <title>Manage Customers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
</head>
<body class="bg-blue-50 font-sans animate-scale-in">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-indigo-800 mb-8 tracking-tight flex items-center">
            <svg class="w-8 h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5c4.418 0 8 3.582 8 8s-3.582 8-8 8-8-3.582-8-8m0 16c-2.761 0-5-2.239-5-5m10 0c0 2.761-2.239 5-5 5"></path>
            </svg>
            Manage Customers
        </h1>

        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            <div class="flex justify-between mb-6">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5c4.418 0 8 3.582 8 8s-3.582 8-8 8-8-3.582-8-8 3.582-8 8-8m0 16c-2.761 0-5-2.239-5-5m10 0c0 2.761-2.239 5-5 5"></path>
                    </svg>
                    Customer List
                </h2>
                <div class="flex space-x-4">
                    <input type="text" id="customerSearch" class="p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-200" placeholder="Search by name...">
                    <button id="clearSearch" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Clear</button>
                </div>
            </div>
            <div id="customerTable" class="overflow-x-auto"></div>
        </div>

        <!-- Update Modal -->
        <div id="updateCustomerModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full">
                <h2 class="text-xl font-semibold text-indigo-700 mb-4">Update Customer</h2>
                <form id="updateCustomerForm" class="space-y-4">
                    <input type="hidden" name="customer_id" id="customerId">
                    <input type="text" name="customer_name" id="customerName" placeholder="Customer Name" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <input type="email" name="email" id="customerEmail" placeholder="Email" required class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <input type="tel" name="phone" id="customerPhone" placeholder="Phone" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <input type="text" name="address" id="customerAddress" placeholder="Address" class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-indigo-400">
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-3 rounded-lg hover:bg-indigo-700 transform hover:scale-105 transition duration-200">Update</button>
                        <button type="button" onclick="closeModal('updateCustomerModal')" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 text-white p-3 rounded-lg hover:bg-gray-800 transform hover:scale-105 transition duration-200">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <?php include 'footer.php'; ?>
    <?php $conn->close(); ?>

    <script>
        $(document).ready(function() {
            loadCustomers();

            $('#customerSearch').on('input', function() {
                if ($(this).val().length > 2 || $(this).val().length === 0) {
                    loadCustomers();
                }
            });

            $('#clearSearch').click(function() {
                $('#customerSearch').val('');
                loadCustomers();
            });

            function loadCustomers() {
                $.ajax({
                    url: 'manage_customers_handler.php',
                    method: 'POST',
                    data: { search: $('#customerSearch').val() },
                    dataType: 'json',
                    success: function(response) {
                        renderCustomers(response.customers);
                    },
                    error: function() {
                        $('#customerTable').html('<p class="text-red-700">Error loading customers.</p>');
                    }
                });
            }

            function renderCustomers(customers) {
                let html = '<table class="min-w-full divide-y divide-gray-200">' +
                    '<thead class="bg-gray-50">' +
                    '<tr>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>' +
                    '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>' +
                    '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                if (customers.length === 0) {
                    html += '<tr><td colspan="5" class="px-6 py-4 text-sm text-gray-900 text-center">No customers found.</td></tr>';
                } else {
                    customers.forEach(c => {
                        html += `<tr class="hover:bg-blue-50 transition duration-200">` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${c.customer_name}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${c.email}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${c.phone || ''}</td>` +
                            `<td class="px-6 py-4 text-sm text-gray-900">${c.address || ''}</td>` +
                            `<td class="px-6 py-4 whitespace-nowrap text-sm">` +
                            `<button onclick="openUpdateModal(${c.customer_id}, '${c.customer_name}', '${c.email}', '${c.phone || ''}', '${c.address || ''}')" class="bg-gradient-to-r from-teal-500 to-green-500 text-white px-3 py-1 rounded-lg hover:bg-teal-600 mr-2">Update</button>` +
                            `<button onclick="confirmDelete(${c.customer_id}, 'customer')" class="bg-gradient-to-r from-red-600 to-red-700 text-white px-3 py-1 rounded-lg hover:bg-red-800">Delete</button>` +
                            `</td></tr>`;
                    });
                }
                html += '</tbody></table>';
                $('#customerTable').html(html);
            }

            window.openUpdateModal = function(id, name, email, phone, address) {
                $('#customerId').val(id);
                $('#customerName').val(name);
                $('#customerEmail').val(email);
                $('#customerPhone').val(phone);
                $('#customerAddress').val(address);
                $('#updateCustomerModal').removeClass('hidden');
            };

            window.closeModal = function(modalId) {
                $(`#${modalId}`).addClass('hidden');
                $(`#${modalId} form`)[0].reset();
            };

            window.confirmDelete = function(id, type) {
                if (confirm(`Are you sure you want to delete this ${type}?`)) {
                    $.ajax({
                        url: 'manage_customers_handler.php',
                        method: 'POST',
                        data: { action: 'delete', customer_id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                loadCustomers();
                            } else {
                                alert(response.error);
                            }
                        }
                    });
                }
            };

            $('#updateCustomerForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'manage_customers_handler.php',
                    method: 'POST',
                    data: $(this).serialize() + '&action=update',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            closeModal('updateCustomerModal');
                            loadCustomers();
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