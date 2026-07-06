<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: admin/users.php
// Description: Refactored user administration panel with layout includes, stats, and delete triggers.

// Enforce admin middleware
require_once __DIR__ . "/../middleware/admin.php";

// Include database
require_once __DIR__ . "/../config/database.php";

$error = "";
$success = "";
$users = [];

// Session alert check
if (isset($_SESSION["success"])) {
    $success = $_SESSION["success"];
    unset($_SESSION["success"]);
}
if (isset($_SESSION["error"])) {
    $error = $_SESSION["error"];
    unset($_SESSION["error"]);
}

try {
    // Fetch all users using a prepared statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT id, username, role, created_at FROM users ORDER BY id ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin Fetch Users Error: " . $e->getMessage());
    $error = "System Error: Could not fetch user directories.";
}

// Layout parameters
$title = "User Management - Admin Panel";
$base_path = "../";

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";
?>

<!-- Main Container -->
<main class="container my-5">
    
    <!-- Header Page Panel -->
    <div class="bg-white p-4 rounded-3 shadow-sm border mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">User Directory Management</h1>
            <p class="text-muted mb-0">Modify user roles, configure permissions, or delete accounts.</p>
        </div>
        <a href="../dashboard.php" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Success & Error Alerts -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div><?php echo htmlspecialchars($success); ?></div>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <!-- Users Table Card -->
    <div class="card shadow-sm border-0 rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h3 class="h5 fw-bold mb-0 text-dark">
                <i class="bi bi-people-fill text-primary me-2"></i> Registered Accounts
            </h3>
            <span class="badge bg-secondary px-3 py-2"><?php echo count($users); ?> users</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive border-0">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%;" class="ps-4">User ID</th>
                            <th style="width: 35%;">Username</th>
                            <th style="width: 20%;">Active Role</th>
                            <th style="width: 20%;">Registered On</th>
                            <th style="width: 15%;" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php $is_self = ((int)$user['id'] === (int)$_SESSION['user_id']); ?>
                            <tr>
                                <td class="ps-4 text-muted fw-semibold">#<?php echo $user['id']; ?></td>
                                <td>
                                    <!-- XSS Protection on username -->
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if ($is_self): ?>
                                        <span class="badge bg-primary ms-1 fs-9">You</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($user['role'] === 'admin') ? 'bg-primary' : 'bg-secondary'; ?> text-capitalize px-3 py-1.5 fs-8">
                                        <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted"><i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="roles.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary fw-semibold" title="Edit User Role">
                                            <i class="bi bi-shield-lock"></i> Role
                                        </a>
                                        
                                        <?php if ($is_self): ?>
                                            <button class="btn btn-sm btn-outline-danger fw-semibold" disabled title="You cannot delete yourself">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger fw-semibold btn-delete-user-confirm" data-username="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" title="Delete User Account">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
