<?php
// Project: PHP & MySQL Blog Management System (Task 3)
// File: login.php
// Description: User authentication and session instantiation using Bootstrap 5.

// Start session
session_start();

// Redirect user if they are already logged in
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: dashboard.php");
    exit;
}

// Include database setup
require_once "config/database.php";

$error = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // Find the user by username
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify if user exists and password matches hashed database entry
            if ($user && password_verify($password, $user['password'])) {
                // Initialize session state
                $_SESSION["logged_in"] = true;
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blog Management System</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="bi bi-journal-code text-primary fs-3"></i>
                <span class="fw-bold">BlogSystem</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php"><i class="bi bi-person-plus-fill"></i> Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-box-arrow-in-right text-primary display-4"></i>
                            <h2 class="fw-bold mt-2">Welcome Back</h2>
                            <p class="text-muted">Log in to write and manage your posts</p>
                        </div>

                        <!-- Error Alerts -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Form -->
                        <form action="login.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">Don't have an account?</span>
                            <a href="register.php" class="text-primary fw-semibold text-decoration-none">Register here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-muted py-4 mt-auto border-top border-primary border-4">
        <div class="container text-center">
            <p class="mb-0">Task 3: Advanced Blog System | Intern: <span class="text-white fw-bold">Abhinav</span></p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
