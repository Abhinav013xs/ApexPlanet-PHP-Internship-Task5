<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: admin/roles.php
// Description: Refactored role configuration form for Administrators, using dynamic layout imports.

// Enforce admin middleware
require_once __DIR__ . "/../middleware/admin.php";

// Include database setup
require_once __DIR__ . "/../config/database.php";

$error = "";
$user_id = $_GET["id"] ?? "";

// Check if user ID is empty
if (empty($user_id)) {
    $_SESSION["error"] = "No user ID specified.";
    header("Location: users.php");
    exit;
}

// Fetch user information
try {
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION["error"] = "User account not found.";
        header("Location: users.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION["error"] = "System Error: Could not retrieve account details.";
    header("Location: users.php");
    exit;
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_role = $_POST["role"] ?? "";

    // Validate enum roles
    if ($new_role !== "admin" && $new_role !== "editor") {
        $error = "Invalid role value selected.";
    } else {
        try {
            // Update using secure prepared statement
            $update_stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
            $update_stmt->execute([
                'role' => $new_role,
                'id' => $user_id
            ]);

            // Save success flag and redirect
            $_SESSION["success"] = "Role for user '" . htmlspecialchars($user['username']) . "' updated successfully to " . $new_role . "!";
            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            error_log("Admin Role Update Error: " . $e->getMessage());
            $error = "System Error: Could not update user role.";
        }
    }
}

// Layout parameters
$title = "Edit User Role - Admin Panel";
$base_path = "../";

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 bg-white rounded-3">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock-fill text-primary display-4"></i>
                        <h2 class="fw-bold mt-2">Change User Role</h2>
                        <p class="text-muted">Modify role settings for <strong><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    </div>

                    <!-- Error Alerts -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form action="roles.php?id=<?php echo $user['id']; ?>" method="POST">
                        <div class="mb-4">
                            <label for="role" class="form-label fw-semibold">Select Role</label>
                            <select name="role" id="role" class="form-select">
                                <option value="editor" <?php echo ($user['role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mb-2 shadow-sm">
                            <i class="bi bi-save-fill me-1"></i> Save Changes
                        </button>
                        <a href="users.php" class="btn btn-outline-secondary w-100 py-2.5 fw-semibold">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
