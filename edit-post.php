<?php
// Project: PHP & MySQL Blog Management System (Task 2)
// File: edit-post.php
// Description: Form to modify existing blog articles.

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
$post_id = $_GET["id"] ?? "";

// If ID is missing, redirect back to dashboard
if (empty($post_id)) {
    $_SESSION["error"] = "No post ID specified.";
    header("Location: dashboard.php");
    exit;
}

// Fetch the existing post details
try {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no post exists with that ID, redirect back
    if (!$post) {
        $_SESSION["error"] = "Post not found.";
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION["error"] = "Database Error: " . $e->getMessage();
    header("Location: dashboard.php");
    exit;
}

// Handle form submission to update values
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    // Validation
    if (empty($title) || empty($content)) {
        $error = "Please fill in both the title and the content.";
    } else {
        try {
            // Update SQL statement
            $update_stmt = $conn->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id");
            $update_stmt->execute([
                'title' => $title,
                'content' => $content,
                'id' => $post_id
            ]);

            // Set success message and redirect
            $_SESSION["success"] = "Post updated successfully!";
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
    <title>Edit Post - Blog Management System</title>
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
            <h1>Edit Post</h1>
            <p style="color: #718096; margin-bottom: 25px;">Modify your published article details</p>

            <!-- Error Alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Post Edit Form -->
            <form action="edit-post.php?id=<?php echo $post['id']; ?>" method="POST">
                <div class="form-group">
                    <label for="title">Post Title</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="Enter post title" required value="<?php echo isset($title) ? htmlspecialchars($title) : htmlspecialchars($post['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" class="form-control" placeholder="Write your post content here..." required><?php echo isset($content) ? htmlspecialchars($content) : htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
