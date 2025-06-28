# Inventory Management System

A web-based inventory management system built with PHP, JavaScript, Tailwind CSS, and MySQL. Features dynamic CRUD operations for products, customers, and sellers, with a responsive UI and AJAX-driven interactions.

## Features
- Manage products, customers, and sellers with search, update, and delete functionality.
- Responsive navbar with dropdowns for Products, Customers, and Sellers.
- AJAX for seamless data operations without page reloads.
- Secure database interactions with prepared statements.

## Setup
1. Clone the repository:

   git clone https://github.com/Shreyas21A/Inventory-Management-System.git
2. Navigate to the `admin/` folder.
3. Set up a MySQL database and create a `config.php` file in `admin/` with your database credentials:
```php
<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

Place the project in a web server (e.g., XAMPP: htdocs/NewInventory).
Ensure the admin/images/ folder is writable for product image uploads.
Access via http://localhost/NewInventory/admin.

Technologies

PHP
JavaScript (jQuery for AJAX)
Tailwind CSS
MySQL
