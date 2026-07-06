<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: includes/navbar.php
// Description: Unified navigation bar component with RBAC elements and dynamic links.

$base_path = $base_path ?? "./";
$current_page = basename($_SERVER['PHP_SELF']);
$logged_in = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$user_role = $_SESSION["role"] ?? null;
$username = $_SESSION["username"] ?? null;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3 border-bottom border-primary border-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white fs-4" href="<?php echo $base_path; ?>index.php">
            <i class="bi bi-journal-code text-primary fs-3"></i>
            <span>Blog<span class="text-primary">System</span></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2 align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'index.php' || $current_page === 'search.php') ? 'active fw-semibold' : ''; ?>" href="<?php echo $base_path; ?>index.php">
                        <i class="bi bi-house-door-fill me-1"></i> Home
                    </a>
                </li>
                
                <?php if ($logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'dashboard.php') ? 'active fw-semibold' : ''; ?>" href="<?php echo $base_path; ?>dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    
                    <!-- Admin User Management link -->
                    <?php if ($user_role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page === 'users.php' || $current_page === 'roles.php') ? 'active fw-semibold text-info' : 'text-info'; ?>" href="<?php echo $base_path; ?>admin/users.php">
                                <i class="bi bi-people-fill me-1"></i> Manage Users
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'profile.php') ? 'active fw-semibold' : ''; ?>" href="<?php echo $base_path; ?>profile.php">
                            <i class="bi bi-person-circle me-1"></i> Profile
                        </a>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-danger btn-sm px-3 fw-semibold d-flex align-items-center gap-1 btn-logout-nav" href="<?php echo $base_path; ?>logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout (<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>)
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'login.php') ? 'active fw-semibold' : ''; ?>" href="<?php echo $base_path; ?>login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3 fw-semibold" href="<?php echo $base_path; ?>register.php">
                            <i class="bi bi-person-plus-fill me-1"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
