<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: admin/delete-user.php
// Description: User deletion processor, restricted to Administrators, preventing self-deletion.

// Enforce admin middleware
require_once __DIR__ . "/../middleware/admin.php";

// Include database
require_once __DIR__ . "/../config/database.php";

$delete_user_id = $_GET["id"] ?? "";

// Check if ID is empty
if (empty($delete_user_id)) {
    $_SESSION["error"] = "No user ID specified for deletion.";
    header("Location: users.php");
    exit;
}

// Prevent self-deletion
if ((int)$delete_user_id === (int)$_SESSION["user_id"]) {
    $_SESSION["error"] = "Security Violation: You cannot delete your own active administrator account!";
    header("Location: users.php");
    exit;
}

try {
    // 1. Fetch user to verify they exist and make sure they aren't the primary admin 'admin'
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $delete_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION["error"] = "User account not found or already deleted.";
        header("Location: users.php");
        exit;
    }

    // Optional: Protect the primary administrator account 'admin' from being deleted
    if ($user['username'] === 'admin') {
        $_SESSION["error"] = "Security Violation: The primary 'admin' account cannot be deleted.";
        header("Location: users.php");
        exit;
    }

    // 2. Perform deletion (dependent posts are cleaned up via ON DELETE CASCADE foreign key constraint)
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $delete_stmt->execute(['id' => $delete_user_id]);

    $_SESSION["success"] = "User account '" . htmlspecialchars($user['username']) . "' and all their associated blog posts were deleted successfully.";

} catch (PDOException $e) {
    error_log("Admin Delete User Query Error: " . $e->getMessage());
    $_SESSION["error"] = "An internal system error occurred while deleting the user account.";
}

// Redirect back to users table
header("Location: users.php");
exit;
?>
