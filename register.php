<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: register.php
// Description: Refactored registration page with shared layout imports.

// Start the session securely
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Redirect to dashboard if already logged in
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
require_once "config/database.php";

$error = "";
$success = "";
$username = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    
    // Server-Side Form Validation
    if (empty($username)) {
        $error = "Username is required.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    } elseif (empty($password)) {
        $error = "Password is required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if username is already taken using a secure Prepared Statement
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            $check_stmt->execute(['username' => $username]);
            
            if ($check_stmt->fetch()) {
                $error = "Username is already taken. Please choose another.";
            } else {
                // Securely hash the user password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert the new user (default role is 'editor')
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                $insert_stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password
                ]);
                
                $success = "Registration successful! You can now <a href='login.php' class='alert-link'>log in here</a>.";
                $username = ""; // Clear username field upon successful registration
            }
        } catch (PDOException $e) {
            error_log("Registration DB Error: " . $e->getMessage());
            $error = "An internal system error occurred. Please try again later.";
        }
    }
}

// Layout parameters
$title = "Register - Blog System";
$base_path = "./";

require_once "includes/header.php";
require_once "includes/navbar.php";
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 bg-white rounded-3">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus text-primary display-4"></i>
                        <h2 class="fw-bold mt-2">Create Account</h2>
                        <p class="text-muted">Register to start publishing articles</p>
                    </div>

                    <!-- Error Alerts -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- JS Client-side Validation Alert placeholder -->
                    <div id="js-error-alert" class="alert alert-danger d-none align-items-center gap-2 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div id="js-error-msg"></div>
                    </div>

                    <!-- Success Alerts -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <div><?php echo $success; ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form id="register-form" action="register.php" method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Choose Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required value="<?php echo htmlspecialchars($username); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Create Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 characters" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold shadow-sm">
                            <i class="bi bi-person-plus-fill me-1"></i> Register Account
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted">Already have an account?</span>
                        <a href="login.php" class="text-primary fw-semibold text-decoration-none hover-link">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.getElementById("register-form");
    if (registerForm) {
        registerForm.addEventListener("submit", function(event) {
            const usernameInput = document.getElementById("username").value.trim();
            const passwordInput = document.getElementById("password").value;
            const errorAlert = document.getElementById("js-error-alert");
            const errorMsg = document.getElementById("js-error-msg");
            
            let clientError = "";

            // Reset errors state
            errorAlert.classList.add("d-none");
            errorAlert.classList.remove("d-flex");

            // Validate fields length and format
            if (usernameInput === "") {
                clientError = "Username is required.";
            } else if (usernameInput.length < 3) {
                clientError = "Username must be at least 3 characters long.";
            } else if (!/^[a-zA-Z0-9_]+$/.test(usernameInput)) {
                clientError = "Username can only contain alphanumeric characters and underscores.";
            } else if (passwordInput === "") {
                clientError = "Password is required.";
            } else if (passwordInput.length < 6) {
                clientError = "Password must be at least 6 characters long.";
            }

            if (clientError !== "") {
                event.preventDefault(); // Stop form submission
                errorMsg.textContent = clientError;
                errorAlert.classList.remove("d-none");
                errorAlert.classList.add("d-flex");
                window.scrollTo(0, 0); // Scroll to error display
            }
        });
    }
});
</script>

<?php
// Include footer
require_once "includes/footer.php";
?>
