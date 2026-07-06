<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: edit-post.php
// Description: Refactored post editor with dynamic includes, validations, and ownership verification.

// Enforce auth session middleware
require_once "middleware/auth.php";

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

// Fetch the existing post details and author associations
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

    // SECURITY CHECK (RBAC): Enforce ownership permissions
    // Admin can edit any post, Editors can ONLY edit their own posts
    if ($_SESSION["role"] !== "admin" && (int)$post["user_id"] !== (int)$_SESSION["user_id"]) {
        $_SESSION["error"] = "Access Denied: You do not have permission to edit this article.";
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Post Edit Fetch Error: " . $e->getMessage());
    $_SESSION["error"] = "An internal system error occurred.";
    header("Location: dashboard.php");
    exit;
}

// Handle form submission to update values
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    // Server-Side Form Validation
    if (empty($title)) {
        $error = "Post title is required.";
    } elseif (empty($content)) {
        $error = "Post content is required.";
    } elseif (strlen($content) < 10) {
        $error = "Post content must be at least 10 characters long.";
    } else {
        try {
            // Update SQL using prepared statement
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
            error_log("Post Edit Update Query Error: " . $e->getMessage());
            $error = "An error occurred updating the database. Please try again later.";
        }
    }
}

// Layout parameters
$page_title = "Edit Post - Blog System";
$base_path = "./";

require_once "includes/header.php";
require_once "includes/navbar.php";
?>

<!-- Main Container -->
<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 bg-white rounded-3">
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

                    <!-- Post Edit Form -->
                    <form id="edit-post-form" action="edit-post.php?id=<?php echo $post['id']; ?>" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Post Title</label>
                            <!-- XSS Protection: sanitizing title values on display -->
                            <input type="text" name="title" id="title" class="form-control form-control-lg" placeholder="Enter post title" required value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label fw-semibold">Content</label>
                            <!-- XSS Protection: sanitizing content values on display -->
                            <textarea name="content" id="content" class="form-control" placeholder="Write your post content here..." rows="8" required><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2.5 fw-semibold shadow-sm">
                                <i class="bi bi-save-fill me-1"></i> Save Changes
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary px-4 py-2.5 fw-semibold">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Client-side Validation Handler -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const editForm = document.getElementById("edit-post-form");
    if (editForm) {
        editForm.addEventListener("submit", function(event) {
            const titleInput = document.getElementById("title").value.trim();
            const contentInput = document.getElementById("content").value.trim();
            const errorAlert = document.getElementById("js-error-alert");
            const errorMsg = document.getElementById("js-error-msg");
            
            let clientError = "";

            // Reset errors state
            errorAlert.classList.add("d-none");
            errorAlert.classList.remove("d-flex");

            // Validate lengths
            if (titleInput === "") {
                clientError = "Post title is required.";
            } else if (contentInput === "") {
                clientError = "Post content is required.";
            } else if (contentInput.length < 10) {
                clientError = "Post content must be at least 10 characters long.";
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
// Include page footer
require_once "includes/footer.php";
?>
