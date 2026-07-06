<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: 403.php
// Description: Custom 403 Forbidden Access Denied error view.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = "Access Denied - Blog System";
$base_path = "./";
require_once "includes/header.php";
require_once "includes/navbar.php";
?>

<main class="container my-5 flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="row justify-content-center w-100">
        <div class="col-md-7 col-lg-5 text-center">
            <div class="card shadow-sm border-0 bg-white p-5 rounded-3">
                <div class="card-body">
                    <div class="text-danger error-icon mb-3">
                        <i class="bi bi-shield-slash-fill"></i>
                    </div>
                    <h1 class="display-1 fw-bold text-dark mb-1">403</h1>
                    <h2 class="h4 text-muted mb-4">Access Denied</h2>
                    <p class="text-secondary mb-4">
                        You do not have the required administrative permissions to view or edit this resource.
                    </p>
                    <div class="d-flex flex-column gap-2">
                        <a href="dashboard.php" class="btn btn-primary py-2 fw-semibold">
                            <i class="bi bi-speedometer2"></i> Back to Dashboard
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary py-2 fw-semibold">
                            <i class="bi bi-house-door-fill"></i> Back to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once "includes/footer.php";
?>
