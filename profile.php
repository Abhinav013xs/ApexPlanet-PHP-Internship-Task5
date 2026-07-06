<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: profile.php
// Description: User profile page allowing username updates and password changes securely.

// Enforce authentication middleware
require_once "middleware/auth.php";

// Include database
require_once "config/database.php";

$error = "";
$success = "";
$user_id = $_SESSION["user_id"];

// Fallback defaults to prevent PHP Undefined Variable warnings during database connection issues
$user = [
    'username' => '',
    'role' => '',
    'created_at' => date('Y-m-d H:i:s')
];
$total_user_posts = 0;

// Fetch current user details and total posts count
try {
    // Fetch details
    $user_stmt = $conn->prepare("SELECT username, role, created_at FROM users WHERE id = :id LIMIT 1");
    $user_stmt->execute(['id' => $user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Fallback if user session state is desynced
        header("Location: logout.php");
        exit;
    }

    // Count posts authored by this user
    $post_count_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :user_id");
    $post_count_stmt->execute(['user_id' => $user_id]);
    $total_user_posts = (int)$post_count_stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Profile Load Error: " . $e->getMessage());
    $error = "An internal error occurred retrieving your profile details.";
}

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "update_username") {
        $new_username = trim($_POST["username"] ?? "");

        // Validations
        if (empty($new_username)) {
            $error = "Username cannot be empty.";
        } elseif (strlen($new_username) < 3) {
            $error = "Username must be at least 3 characters long.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
            $error = "Username can only contain alphanumeric characters and underscores.";
        } else {
            try {
                // Check if username is already taken by someone else
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1");
                $check_stmt->execute(['username' => $new_username, 'id' => $user_id]);
                if ($check_stmt->fetch()) {
                    $error = "The username '" . htmlspecialchars($new_username) . "' is already taken.";
                } else {
                    // Update database
                    $update_stmt = $conn->prepare("UPDATE users SET username = :username WHERE id = :id");
                    $update_stmt->execute(['username' => $new_username, 'id' => $user_id]);

                    // Sync session variable
                    $_SESSION["username"] = $new_username;
                    $user["username"] = $new_username; // Update local variable for rendering
                    $success = "Username updated successfully!";
                }
            } catch (PDOException $e) {
                error_log("Username Update Error: " . $e->getMessage());
                $error = "Could not update username. Please try again later.";
            }
        }
    } elseif ($action === "change_password") {
        $current_password = $_POST["current_password"] ?? "";
        $new_password = $_POST["new_password"] ?? "";
        $confirm_password = $_POST["confirm_password"] ?? "";

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            try {
                // Fetch the hashed password to verify
                $hash_stmt = $conn->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
                $hash_stmt->execute(['id' => $user_id]);
                $db_user = $hash_stmt->fetch(PDO::FETCH_ASSOC);

                if ($db_user && password_verify($current_password, $db_user["password"])) {
                    // Hash and save new password
                    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $update_pw_stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                    $update_pw_stmt->execute(['password' => $new_hash, 'id' => $user_id]);

                    $success = "Password changed successfully!";
                } else {
                    $error = "Current password is incorrect.";
                }
            } catch (PDOException $e) {
                error_log("Password Change Error: " . $e->getMessage());
                $error = "Could not change password. Please try again later.";
            }
        }
    }
}

// Include page header and navbar
$title = "My Profile - Blog System";
$base_path = "./";
require_once "includes/header.php";
require_once "includes/navbar.php";
?>

<main class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold text-dark mb-1">Account Profile</h1>
            <p class="text-muted">Manage your credentials, view statistics, or update passwords.</p>
        </div>
    </div>

    <!-- Alert Dialogues -->
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

    <!-- Client-Side JS Alert Placeholder -->
    <div id="js-error-alert" class="alert alert-danger d-none align-items-center gap-2 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div id="js-error-msg"></div>
    </div>

    <div class="row g-4">
        <!-- Profile details + Edit username -->
        <div class="col-lg-6">
            <!-- Account Info Stats Card -->
            <div class="card shadow-sm border-0 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle fs-3">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Account Overview</h2>
                            <p class="text-muted mb-0">Overview of your system permissions</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-7">Username</span>
                            <span class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-7">Assigned Role</span>
                            <span class="badge bg-dark text-capitalize px-3 py-2 mt-1 fs-8"><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-7">Articles Published</span>
                            <span class="fw-bold text-primary fs-5"><i class="bi bi-file-earmark-post"></i> <?php echo $total_user_posts; ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-7">Member Since</span>
                            <span class="text-dark fw-semibold"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Username Card -->
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold mb-3"><i class="bi bi-pencil-square text-primary me-1"></i> Update Username</h3>
                    <form id="username-form" action="profile.php" method="POST">
                        <input type="hidden" name="action" value="update_username">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">New Username</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Enter new username" required value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text">Username must be at least 3 characters long and contain only letters, numbers, or underscores.</div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save"></i> Save Username
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Password card -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle fs-3">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Security Credentials</h2>
                            <p class="text-muted mb-0">Change password settings</p>
                        </div>
                    </div>

                    <form id="password-form" action="profile.php" method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password" required>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" required>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-shield-lock-fill"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const errorAlert = document.getElementById("js-error-alert");
    const errorMsg = document.getElementById("js-error-msg");

    function showError(msg) {
        errorMsg.textContent = msg;
        errorAlert.classList.remove("d-none");
        errorAlert.classList.add("d-flex");
        window.scrollTo(0, 0);
    }

    function resetError() {
        errorAlert.classList.add("d-none");
        errorAlert.classList.remove("d-flex");
    }

    // Username validation
    const usernameForm = document.getElementById("username-form");
    if (usernameForm) {
        usernameForm.addEventListener("submit", function(event) {
            resetError();
            const usernameVal = document.getElementById("username").value.trim();
            if (usernameVal === "") {
                event.preventDefault();
                showError("Username is required.");
            } else if (usernameVal.length < 3) {
                event.preventDefault();
                showError("Username must be at least 3 characters long.");
            } else if (!/^[a-zA-Z0-9_]+$/.test(usernameVal)) {
                event.preventDefault();
                showError("Username can only contain alphanumeric characters and underscores.");
            }
        });
    }

    // Password validation
    const passwordForm = document.getElementById("password-form");
    if (passwordForm) {
        passwordForm.addEventListener("submit", function(event) {
            resetError();
            const currentPass = document.getElementById("current_password").value;
            const newPass = document.getElementById("new_password").value;
            const confirmPass = document.getElementById("confirm_password").value;

            if (!currentPass || !newPass || !confirmPass) {
                event.preventDefault();
                showError("All password fields are required.");
            } else if (newPass.length < 6) {
                event.preventDefault();
                showError("New password must be at least 6 characters long.");
            } else if (newPass !== confirmPass) {
                event.preventDefault();
                showError("New passwords do not match.");
            }
        });
    }
});
</script>

<?php
// Include page footer
require_once "includes/footer.php";
?>
