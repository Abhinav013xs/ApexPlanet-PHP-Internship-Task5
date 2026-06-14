<?php
// Project: PHP & MySQL Blog Management System (Task 2)
// File: index.php
// Description: Public landing page listing all blog posts from the database.

// Start the session
session_start();

// Include database configurations
require_once "config/database.php";

$posts = [];
$error_message = "";

try {
    // Select posts sorted by creation date (latest posts first)
    $stmt = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Could not retrieve articles: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Simple Blog System</title>
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
                <li><a href="index.php" style="color: #ffffff;">Home</a></li>
                
                <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                    <!-- If logged in, show Dashboard and Logout -->
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php" class="logout-link">Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a></li>
                <?php else: ?>
                    <!-- If guest, show Login and Register -->
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container">
        
        <!-- Header Info -->
        <div style="margin-bottom: 40px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
            <h1>Welcome to BlogSystem</h1>
            <p style="color: #718096; font-size: 16px;">
                A simple blog site built using PHP & MySQL for Task 2.
            </p>
        </div>

        <!-- Error Alerts -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Post Listings Section -->
        <div class="post-list">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            Published on: <?php echo date('F d, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                        </div>
                        <div class="post-content">
                            <?php echo htmlspecialchars($post['content']); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty Placeholder -->
                <div class="empty-state">
                    <p style="font-size: 18px; margin-bottom: 10px;">No blog posts available yet.</p>
                    <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                        <a href="create-post.php" class="btn btn-primary">Create the first post!</a>
                    <?php else: ?>
                        <p style="font-size: 14px;">Please <a href="login.php" style="color: #3498db; text-decoration: none;">login</a> to publish the first article!</p>
                    <?php endif; ?>
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

</body>
</html>
