<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: 404.php
// Description: Custom 404 Page Not Found error view.

// Try to start session if not started to keep authentication context in navbar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = "Page Not Found - Blog System";
$base_path = "./";
require_once "includes/header.php";
require_once "includes/navbar.php";
?>

<main class="container my-5 flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="row justify-content-center w-100">
        <div class="col-md-7 col-lg-5 text-center">
            <div class="card shadow-sm border-0 bg-white p-5 rounded-3">
                <div class="card-body">
                    <div class="text-primary error-icon mb-3">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                    <h1 class="display-1 fw-bold text-dark mb-1">404</h1>
                    <h2 class="h4 text-muted mb-4">Oops! Page Not Found</h2>
                    <p class="text-secondary mb-4">
                        The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
                    </p>
                    <div class="d-flex flex-column gap-2">
                        <a href="index.php" class="btn btn-primary py-2 fw-semibold">
                            <i class="bi bi-house-door-fill"></i> Back to Homepage
                        </a>
                        <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                            <a href="dashboard.php" class="btn btn-outline-secondary py-2 fw-semibold">
                                <i class="bi bi-speedometer2"></i> Visit Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once "includes/footer.php";
?>
