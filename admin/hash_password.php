<?php
$password = 'Admin123admin';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


echo $hashedPassword;
?>
