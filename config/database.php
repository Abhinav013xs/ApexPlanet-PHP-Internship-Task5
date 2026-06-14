<?php
// Project: Internship Task 2 - Blog Management System
// File: config/database.php
// Description: Establishes a connection to the MySQL database using PDO.

// Database configuration settings
$host = "localhost";
$db_name = "blog";
$username = "root";
$password = ""; // Default XAMPP password is empty

try {
    // Create a new PDO instance to connect to the database
    // We also set the charset to utf8mb4 for security and special character support
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    
    // Set PDO error mode to Exception so SQL errors throw catchable exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $exception) {
    // If connection fails, show a simple error message and stop script
    die("Database Connection Error: " . $exception->getMessage());
}
?>
