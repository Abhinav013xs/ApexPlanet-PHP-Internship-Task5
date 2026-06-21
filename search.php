<?php
// Project: PHP & MySQL Blog Management System (Task 3)
// File: search.php
// Description: Public search results page with pagination support.

session_start();
require_once "config/database.php";

$search_query = trim($_GET["q"] ?? "");
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 5; // Display only 5 posts per page
$offset = ($page - 1) * $limit;

$posts = [];
$total_posts = 0;
$total_pages = 0;
$error_message = "";

try {
    if (!empty($search_query)) {
        // 1. Get the total count of matching articles for pagination calculation
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :search OR content LIKE :search");
        $count_stmt->execute(['search' => '%' . $search_query . '%']);
        $total_posts = (int)$count_stmt->fetchColumn();
        
        // Calculate total pages
        $total_pages = ceil($total_posts / $limit);

        // 2. Fetch the paginated matching records
        $stmt = $conn->prepare("SELECT * FROM posts WHERE title LIKE :search OR content LIKE :search ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        
        // Bind parameters
        $stmt->bindValue(':search', '%' . $search_query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // If query is empty, redirect to home page
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error performing search: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Simple Blog System</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Responsive Navigation Bar -->
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
                    <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link logout-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus-fill"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        
        <!-- Search Results Header -->
        <div class="row align-items-center mb-5 border-bottom pb-3">
            <div class="col-md-6">
                <h1>Search Results</h1>
                <p class="text-muted mb-0">Showing results for: <strong class="text-primary">"<?php echo htmlspecialchars($search_query); ?>"</strong> (<?php echo $total_posts; ?> matches found)</p>
            </div>
            
            <!-- Floating Inline Search Form -->
            <div class="col-md-6 mt-3 mt-md-0">
                <form action="search.php" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Search blog posts..." required value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="btn btn-primary fw-semibold">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Error Alerts -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?php echo $error_message; ?></div>
            </div>
        <?php endif; ?>

        <!-- Post Cards Loop -->
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card shadow-sm border-0 rounded-3 mb-4 hover-card">
                            <div class="card-body p-4">
                                <h2 class="card-title h3 fw-bold mb-2"><?php echo htmlspecialchars($post['title']); ?></h2>
                                <p class="card-subtitle text-muted mb-3 fs-7">
                                    <i class="bi bi-calendar3"></i> Published on: <?php echo date('F d, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                                </p>
                                <div class="card-text post-content-preview">
                                    <?php echo htmlspecialchars($post['content']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination Navigation -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Search results page navigation" class="mt-5">
                            <ul class="pagination justify-content-center">
                                
                                <!-- Previous Button -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </a>
                                </li>

                                <!-- Page Number Links -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Button -->
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Empty Search State -->
                    <div class="text-center py-5 bg-white border rounded-3 shadow-sm">
                        <i class="bi bi-search-heart text-muted display-1"></i>
                        <h3 class="fw-bold mt-3">No posts found</h3>
                        <p class="text-muted">We couldn't find any articles matching your query. Try different words.</p>
                        <a href="index.php" class="btn btn-primary fw-semibold px-4 mt-2">Back to Homepage</a>
                    </div>
                <?php endif; ?>
                
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
