<?php
// Project: PHP & MySQL Blog Management System (Task 2)
// File: delete-post.php
// Description: Controller to delete posts by ID.

// Start session
session_start();

// Verify user authentication
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "config/database.php";

$post_id = $_GET["id"] ?? "";

// If ID is missing, redirect back
if (empty($post_id)) {
    $_SESSION["error"] = "No post ID specified for deletion.";
    header("Location: dashboard.php");
    exit;
}

try {
    // Delete SQL statement
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->execute(['id' => $post_id]);

    // Check if a row was actually deleted
    if ($stmt->rowCount() > 0) {
        $_SESSION["success"] = "Post deleted successfully!";
    } else {
        $_SESSION["error"] = "Post not found or already deleted.";
    }
} catch (PDOException $e) {
    $_SESSION["error"] = "Database Error: " . $e->getMessage();
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>
