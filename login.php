<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: login.php
// Description: Refactored login page with shared layout imports.

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

// Redirect user if they are already logged in
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: dashboard.php");
    exit;
}

// Include database setup
require_once "config/database.php";

$error = "";
$username = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Server-Side Form Validation
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // Find the user by username using a secure Prepared Statement
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify if user exists and password matches hashed database entry
            if ($user && password_verify($password, $user['password'])) {
                // SECURITY BEST PRACTICE: Regenerate session ID on successful login to prevent Session Fixation attacks
                session_regenerate_id(true);

                // Initialize session state
                $_SESSION["logged_in"] = true;
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"]; // Store user role (admin/editor)

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                // SECURITY BEST PRACTICE: Keep failure messages generic to prevent account enumeration
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Login Query Error: " . $e->getMessage());
            $error = "An internal system error occurred. Please try again later.";
        }
    }
}

// Layout parameters
$title = "Login - Blog System";
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
                        <i class="bi bi-box-arrow-in-right text-primary display-4"></i>
                        <h2 class="fw-bold mt-2">Welcome Back</h2>
                        <p class="text-muted">Log in to manage your articles</p>
                    </div>

                    <!-- Error Alerts -->
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

                    <!-- Form -->
                    <form id="login-form" action="login.php" method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required value="<?php echo htmlspecialchars($username); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted">Don't have an account?</span>
                        <a href="register.php" class="text-primary fw-semibold text-decoration-none hover-link">Register here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", function(event) {
            const usernameInput = document.getElementById("username").value.trim();
            const passwordInput = document.getElementById("password").value;
            const errorAlert = document.getElementById("js-error-alert");
            const errorMsg = document.getElementById("js-error-msg");
            
            let clientError = "";

            // Reset errors state
            errorAlert.classList.add("d-none");
            errorAlert.classList.remove("d-flex");

            // Validate fields are not empty
            if (usernameInput === "") {
                clientError = "Username is required.";
            } else if (passwordInput === "") {
                clientError = "Password is required.";
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
