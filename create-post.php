<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: create-post.php
// Description: Refactored post creator with dynamic includes, validations, and secure session parameters.

// Enforce auth session middleware
require_once "middleware/auth.php";

// Include database connection
require_once "config/database.php";

$error = "";
$title = "";
$content = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $user_id = $_SESSION["user_id"];

    // Server-Side Form Validation
    if (empty($title)) {
        $error = "Post title is required.";
    } elseif (empty($content)) {
        $error = "Post content is required.";
    } elseif (strlen($content) < 10) {
        $error = "Post content must be at least 10 characters long.";
    } else {
        try {
            // Prepare insert statement, binding user_id from session for authorship tracking
            $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'user_id' => $user_id
            ]);

            // Save success flag and redirect
            $_SESSION["success"] = "Post created successfully!";
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            error_log("Post Create Query Error: " . $e->getMessage());
            $error = "An error occurred writing to the database. Please try again later.";
        }
    }
}

// Layout parameters
$page_title = "Create Post - Blog System";
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
                            <h2 class="fw-bold mb-0">Create New Post</h2>
                            <p class="text-muted mb-0">Publish a new article to the public homepage feed</p>
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

                    <!-- Post Creation Form -->
                    <form id="create-post-form" action="create-post.php" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Post Title</label>
                            <!-- XSS Protection: sanitizing title value on display -->
                            <input type="text" name="title" id="title" class="form-control form-control-lg" placeholder="Enter an engaging title" required value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label fw-semibold">Content</label>
                            <!-- XSS Protection: sanitizing content value on display -->
                            <textarea name="content" id="content" class="form-control" placeholder="Write your post content here..." rows="8" required><?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2.5 fw-semibold shadow-sm">
                                <i class="bi bi-send-fill me-1"></i> Publish Post
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
    const createForm = document.getElementById("create-post-form");
    if (createForm) {
        createForm.addEventListener("submit", function(event) {
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
