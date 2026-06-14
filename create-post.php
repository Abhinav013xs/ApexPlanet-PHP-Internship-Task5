<?php
// Project: PHP & MySQL Blog Management System (Task 2)
// File: create-post.php
// Description: Form to create and save new blog articles.

// Start session
session_start();

// Verify user authentication
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "config/database.php";

$error = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    // Basic Validation
    if (empty($title) || empty($content)) {
        $error = "Please fill in both the title and the content.";
    } else {
        try {
            // Prepare insert statement
            $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (:title, :content)");
            
            // Execute with bound values
            $stmt->execute([
                'title' => $title,
                'content' => $content
            ]);

            // Save success message in session and redirect to dashboard
            $_SESSION["success"] = "Post created successfully!";
            header("Location: dashboard.php");
            exit;
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
    <title>Create Post - Blog Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <div class="container nav-container">
            <div class="logo">
                <a href="index.php">BlogSystem</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php" class="logout-link">Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container">
        <div class="form-box form-box-large">
            <h1>Create New Post</h1>
            <p style="color: #718096; margin-bottom: 25px;">Publish a new article to the public homepage</p>

            <!-- Error Alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Post Creation Form -->
            <form action="create-post.php" method="POST">
                <div class="form-group">
                    <label for="title">Post Title</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="Enter post title" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" class="form-control" placeholder="Write your post content here..." required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Publish Post</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
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
