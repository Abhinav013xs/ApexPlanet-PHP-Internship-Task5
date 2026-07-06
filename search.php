<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: search.php
// Description: Upgraded public search results page with dynamic includes, pagination, and security hardening.

// Start the session with secure configurations
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

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
        // 1. Get the total count of matching articles for pagination calculation using Prepared Statement
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :search_title OR content LIKE :search_content");
        $count_stmt->execute([
            'search_title' => '%' . $search_query . '%',
            'search_content' => '%' . $search_query . '%'
        ]);
        $total_posts = (int)$count_stmt->fetchColumn();
        
        // Calculate total pages
        $total_pages = ceil($total_posts / $limit);

        // 2. Fetch the paginated matching records joined with author usernames
        $stmt = $conn->prepare("
            SELECT posts.*, users.username 
            FROM posts 
            LEFT JOIN users ON posts.user_id = users.id 
            WHERE posts.title LIKE :search_title OR posts.content LIKE :search_content 
            ORDER BY posts.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        // Bind parameters securely
        $stmt->bindValue(':search_title', '%' . $search_query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':search_content', '%' . $search_query . '%', PDO::PARAM_STR);
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
    error_log("Search Query Error: " . $e->getMessage());
    $error_message = "Could not perform search due to an internal system error.";
}

// Layout parameters
$title = "Search Results - Blog System";
$base_path = "./";
require_once "includes/header.php";
require_once "includes/navbar.php";
?>

<!-- Main Container -->
<main class="container my-5">
    
    <!-- Search Results Header -->
    <div class="row align-items-center mb-5 border-bottom pb-4 g-4">
        <div class="col-md-7">
            <h1 class="fw-bold text-dark">Search Results</h1>
            <p class="text-muted mb-0">Showing matches for: <strong class="text-primary">"<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>"</strong> (<?php echo $total_posts; ?> matches found)</p>
        </div>
        
        <!-- Floating Inline Search Form -->
        <div class="col-md-5">
            <form action="search.php" method="GET" class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search blog posts..." required value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Error Alerts -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><?php echo htmlspecialchars($error_message); ?></div>
        </div>
    <?php endif; ?>

    <!-- Post Cards Loop -->
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card shadow-sm border-0 rounded-3 mb-4 hover-card">
                        <div class="card-body p-4">
                            <!-- XSS Protection -->
                            <h2 class="card-title h3 fw-bold mb-2"><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            
                            <p class="card-subtitle text-muted mb-3 fs-7">
                                <i class="bi bi-person-fill text-primary"></i> By: <strong><?php echo htmlspecialchars($post['username'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span class="mx-2">|</span>
                                <i class="bi bi-calendar3 text-secondary"></i> Published on: <?php echo date('F d, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                            </p>
                            
                            <!-- XSS Protection -->
                            <div class="card-text post-content-preview">
                                <?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination Navigation -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Search results page navigation" class="mt-5">
                        <ul class="pagination justify-content-center shadow-sm d-inline-flex rounded">
                            
                            <!-- Previous Button -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link py-2 px-3" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>

                            <!-- Page Number Links -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                                    <a class="page-link py-2 px-3" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link py-2 px-3" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty Search State -->
                <div class="text-center py-5 bg-white border rounded-3 shadow-sm p-4">
                    <i class="bi bi-search-heart text-muted display-1 mb-3"></i>
                    <h3 class="fw-bold mt-2">No posts found</h3>
                    <p class="text-muted">We couldn't find any articles matching your query. Try different keywords.</p>
                    <a href="index.php" class="btn btn-primary fw-semibold px-4 mt-2">
                        <i class="bi bi-arrow-left"></i> Back to Homepage
                    </a>
                </div>
            <?php endif; ?>
            
        </div>
    </div>

</main>

<?php
require_once "includes/footer.php";
?>
