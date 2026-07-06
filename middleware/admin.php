<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: middleware/admin.php
// Description: Admin middleware checking active role permissions.

// First, enforce active authentication
require_once __DIR__ . "/auth.php";

// Verify if the active user role is 'admin'
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    // Return HTTP 403 Status Code
    http_response_code(403);
    
    // Dynamically calculate base path based on depth
    $base_path = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : './';
    
    // Include custom 403 Forbidden page
    require_once __DIR__ . "/../403.php";
    exit;
}
?>
