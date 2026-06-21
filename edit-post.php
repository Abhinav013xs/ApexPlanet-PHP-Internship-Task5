<?php
// Project: PHP & MySQL Blog Management System (Task 3)
// File: edit-post.php
// Description: Form to modify existing blog articles using Bootstrap 5.

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
                        <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-arrow-left"></i></a>
                            <div>
                                <h2 class="fw-bold mb-0">Edit Post</h2>
                                <p class="text-muted mb-0">Modify your published article details</p>
                            </div>
                        </div>

                        <!-- Error Alerts -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Post Edit Form -->
                        <form action="edit-post.php?id=<?php echo $post['id']; ?>" method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label fw-semibold">Post Title</label>
                                <input type="text" name="title" id="title" class="form-control form-control-lg" placeholder="Enter post title" required value="<?php echo isset($title) ? htmlspecialchars($title) : htmlspecialchars($post['title']); ?>">
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label fw-semibold">Content</label>
                                <textarea name="content" id="content" class="form-control" placeholder="Write your post content here..." rows="8" required><?php echo isset($content) ? htmlspecialchars($content) : htmlspecialchars($post['content']); ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                    <i class="bi bi-save-fill"></i> Save Changes
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary px-4 py-2 fw-semibold">Cancel</a>
                            </div>
                        </form>
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
