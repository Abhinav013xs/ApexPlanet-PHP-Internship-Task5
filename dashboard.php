<?php
// Project: PHP & MySQL Blog Management System (Task 2)
// File: dashboard.php
// Description: Author dashboard to manage all blog posts (View, Edit, Delete).

// Start session
session_start();

// Verify user is authenticated; redirect to login if not
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Include database
require_once "config/database.php";

$posts = [];
$success_message = "";
$error_message = "";

// Check if there are messages passed via session (from redirects)
if (isset($_SESSION["success"])) {
    $success_message = $_SESSION["success"];
    unset($_SESSION["success"]);
}
if (isset($_SESSION["error"])) {
    $error_message = $_SESSION["error"];
    unset($_SESSION["error"]);
}

try {
    // Select all articles to list in the management table
    $stmt = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blog Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Simple Navigation Bar -->
    <nav>
        <div class="container nav-container">
            <div class="logo">
                <a href="index.php">BlogSystem</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php" style="color: #ffffff;">Dashboard</a></li>
                <li><a href="logout.php" class="logout-link">Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container">
        
        <!-- Dashboard Header Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1>Author Dashboard</h1>
                <p style="color: #718096;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</p>
            </div>
            <a href="create-post.php" class="btn btn-success">Create New Post</a>
        </div>

        <!-- Success & Error Alerts -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Table Listing Posts -->
        <div class="table-responsive">
            <?php if (count($posts) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th style="width: 50%;">Title</th>
                            <th style="width: 20%;">Date Created</th>
                            <th style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($post['title']); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <!-- Edit Link -->
                                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <!-- Delete Link (triggers JS confirmation via script.js) -->
                                    <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm btn-delete-confirm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <p style="font-size: 18px; margin-bottom: 10px;">No articles have been created yet.</p>
                    <a href="create-post.php" class="btn btn-primary">Create Your First Post</a>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Task 2: Blog Management System | Intern: <span class="highlight">Abhinav</span></p>
        </div>
    </footer>

    <!-- Include client-side JavaScript for confirm prompts -->
    <script src="js/script.js"></script>
</body>
</html>
