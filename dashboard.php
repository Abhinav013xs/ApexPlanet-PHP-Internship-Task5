<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: dashboard.php
// Description: Upgraded author control panel with role-based dashboard KPIs, dynamic includes, and permissions.

// Enforce active login session middleware
require_once "middleware/auth.php";

// Include database connection
require_once "config/database.php";

$search_query = trim($_GET["search"] ?? "");
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 5; // Display only 5 posts per page
$offset = ($page - 1) * $limit;

$posts = [];
$total_posts_display = 0; // Total count of posts the user has access to view (for KPI card)
$total_users = 0; // Total registered users in the system (for Admin KPI card)
$total_pages = 0;

$success_message = "";
$error_message = "";

// Check session alerts
if (isset($_SESSION["success"])) {
    $success_message = $_SESSION["success"];
    unset($_SESSION["success"]);
}
if (isset($_SESSION["error"])) {
    $error_message = $_SESSION["error"];
    unset($_SESSION["error"]);
}

try {
    $user_id = $_SESSION["user_id"];
    $user_role = $_SESSION["role"];

    // 1. Calculate counts and limits based on user role (Admin sees all, Editor sees only own)
    if ($user_role === "admin") {
        // Fetch total registered users count for Admin stats
        $user_count_stmt = $conn->query("SELECT COUNT(*) FROM users");
        $total_users = (int)$user_count_stmt->fetchColumn();

        if (!empty($search_query)) {
            // Count matching posts for Admin
            $count_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :search_title OR content LIKE :search_content");
            $count_stmt->execute([
                'search_title' => '%' . $search_query . '%',
                'search_content' => '%' . $search_query . '%'
            ]);
            $total_posts_display = (int)$count_stmt->fetchColumn();
        } else {
            // Count all posts in the system for Admin
            $system_count = $conn->query("SELECT COUNT(*) FROM posts");
            $total_posts_display = (int)$system_count->fetchColumn();
        }
    } else {
        // Editor role
        if (!empty($search_query)) {
            // Count matching posts created by this editor
            $count_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :user_id AND (title LIKE :search_title OR content LIKE :search_content)");
            $count_stmt->execute([
                'user_id' => $user_id,
                'search_title' => '%' . $search_query . '%',
                'search_content' => '%' . $search_query . '%'
            ]);
            $total_posts_display = (int)$count_stmt->fetchColumn();
        } else {
            // Count posts created by this editor
            $count_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :user_id");
            $count_stmt->execute(['user_id' => $user_id]);
            $total_posts_display = (int)$count_stmt->fetchColumn();
        }
    }

    $total_pages = ceil($total_posts_display / $limit);

    // 2. Fetch paginated posts based on roles using Prepared Statements
    if ($user_role === "admin") {
        if (!empty($search_query)) {
            $stmt = $conn->prepare("
                SELECT posts.*, users.username 
                FROM posts 
                LEFT JOIN users ON posts.user_id = users.id 
                WHERE posts.title LIKE :search_title OR posts.content LIKE :search_content 
                ORDER BY posts.created_at DESC LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':search_title', '%' . $search_query . '%', PDO::PARAM_STR);
            $stmt->bindValue(':search_content', '%' . $search_query . '%', PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("
                SELECT posts.*, users.username 
                FROM posts 
                LEFT JOIN users ON posts.user_id = users.id 
                ORDER BY posts.created_at DESC LIMIT :limit OFFSET :offset
            ");
        }
    } else {
        // Editor sees only their own posts
        if (!empty($search_query)) {
            $stmt = $conn->prepare("
                SELECT posts.*, users.username 
                FROM posts 
                LEFT JOIN users ON posts.user_id = users.id 
                WHERE posts.user_id = :user_id AND (posts.title LIKE :search_title OR posts.content LIKE :search_content) 
                ORDER BY posts.created_at DESC LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':search_title', '%' . $search_query . '%', PDO::PARAM_STR);
            $stmt->bindValue(':search_content', '%' . $search_query . '%', PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("
                SELECT posts.*, users.username 
                FROM posts 
                LEFT JOIN users ON posts.user_id = users.id 
                WHERE posts.user_id = :user_id 
                ORDER BY posts.created_at DESC LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        }
    }

    // Bind common paging variables
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Dashboard Fetch Error: " . $e->getMessage());
    $error_message = "An internal error occurred retrieving your dashboard posts.";
}

// Layout parameters
$title = "Dashboard - Blog System";
$base_path = "./";

require_once "includes/header.php";
require_once "includes/navbar.php";
?>

<main class="container my-5">
    
    <!-- Welcome Header Panel -->
    <div class="card shadow-sm border-0 bg-white mb-4 overflow-hidden position-relative">
        <!-- Accent top border -->
        <div class="bg-primary position-absolute top-0 start-0 end-0" style="height: 6px;"></div>
        <div class="card-body p-4 pt-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h2 fw-bold text-dark mb-1">Author Control Panel</h1>
                <p class="text-muted mb-0">
                    Welcome back, <strong class="text-primary"><?php echo htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8'); ?></strong>! 
                    Logged in as: <span class="badge bg-dark text-capitalize px-3 py-1.5 fs-8"><?php echo htmlspecialchars($user_role, ENT_QUOTES, 'UTF-8'); ?></span>
                </p>
            </div>
            
            <!-- Quick Actions Container -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="create-post.php" class="btn btn-success fw-semibold px-4 py-2 d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Create New Post
                </a>
                <?php if ($user_role === "admin"): ?>
                    <a href="admin/users.php" class="btn btn-info text-white fw-semibold px-4 py-2 d-flex align-items-center gap-2">
                        <i class="bi bi-people-fill"></i> Manage Users
                    </a>
                <?php endif; ?>
                <a href="profile.php" class="btn btn-outline-secondary fw-semibold px-4 py-2 d-flex align-items-center gap-2">
                    <i class="bi bi-person-gear"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Success & Error Alerts -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div><?php echo htmlspecialchars($success_message); ?></div>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><?php echo htmlspecialchars($error_message); ?></div>
        </div>
    <?php endif; ?>

    <!-- KPI Statistic Cards & Search Bar Row -->
    <div class="row g-4 mb-4 align-items-stretch">
        
        <!-- Posts count KPI -->
        <div class="<?php echo ($user_role === 'admin') ? 'col-md-4' : 'col-md-5'; ?>">
            <div class="card shadow-sm border-0 rounded-3 stat-card bg-white h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold text-uppercase fs-7 d-block mb-1">
                            <?php echo ($user_role === "admin") ? "Total System Posts" : "My Published Posts"; ?>
                        </span>
                        <h3 class="display-6 fw-bold mb-0 text-dark"><?php echo $total_posts_display; ?></h3>
                    </div>
                    <i class="bi bi-file-earmark-post text-primary display-5"></i>
                </div>
            </div>
        </div>

        <!-- Users count KPI (Admin only) -->
        <?php if ($user_role === "admin"): ?>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 stat-card-users bg-white h-100">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted fw-semibold text-uppercase fs-7 d-block mb-1">Total System Users</span>
                            <h3 class="display-6 fw-bold mb-0 text-dark"><?php echo $total_users; ?></h3>
                        </div>
                        <i class="bi bi-people-fill text-success display-5"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="<?php echo ($user_role === 'admin') ? 'col-md-4' : 'col-md-7'; ?>">
            <div class="card shadow-sm border-0 rounded-3 bg-white h-100 d-flex align-items-center justify-content-center">
                <div class="card-body p-4 w-100 d-flex align-items-center">
                    <form action="dashboard.php" method="GET" class="w-100">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search articles..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <?php if (!empty($search_query)): ?>
                                <a href="dashboard.php" class="btn btn-outline-secondary" title="Clear Search"><i class="bi bi-x-circle"></i></a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary fw-semibold px-4">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Post Management Table -->
    <div class="card shadow-sm border-0 rounded-3 bg-white mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h3 class="h5 fw-bold mb-0 text-dark">
                <i class="bi bi-list-task text-primary me-2"></i> 
                <?php echo !empty($search_query) ? 'Search Results' : 'Recent Articles'; ?>
            </h3>
            <span class="badge bg-secondary px-3 py-2"><?php echo $total_posts_display; ?> articles</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive border-0">
                <?php if (count($posts) > 0): ?>
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 8%;" class="ps-4">ID</th>
                                <th style="width: 40%;">Title</th>
                                <?php if ($user_role === 'admin'): ?>
                                    <th style="width: 15%;">Author</th>
                                <?php endif; ?>
                                <th style="width: 20%;">Date Created</th>
                                <th style="width: 17%;" class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold">#<?php echo $post['id']; ?></td>
                                    <td>
                                        <!-- XSS protection on title -->
                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <?php if ($user_role === 'admin'): ?>
                                        <td>
                                            <!-- XSS protection on username -->
                                            <span class="text-muted"><i class="bi bi-person"></i> <?php echo htmlspecialchars($post['username'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="text-muted"><i class="bi bi-clock me-1"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <!-- Enforce actions based on permissions -->
                                        <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary fw-semibold me-1">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        
                                        <!-- Admin can delete any; Editor can delete only own (which are the only ones shown anyway) -->
                                        <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger fw-semibold btn-delete-confirm">
                                            <i class="bi bi-trash-fill"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- Empty State Table -->
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x text-muted display-2"></i>
                        <h4 class="fw-bold mt-3">No posts found</h4>
                        <p class="text-muted">We couldn't find any posts matching your search query or no posts have been created yet.</p>
                        <?php if (empty($search_query)): ?>
                            <a href="create-post.php" class="btn btn-primary fw-semibold px-4 mt-2">Publish Your First Post</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-outline-secondary fw-semibold px-4 mt-2">Clear Search</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pagination Navigation for Dashboard -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Dashboard posts page navigation">
            <ul class="pagination justify-content-center shadow-sm d-inline-flex rounded">
                
                <!-- Previous Button -->
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link py-2 px-3" href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>

                <!-- Page numbers -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                        <a class="page-link py-2 px-3" href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Button -->
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link py-2 px-3" href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                
            </ul>
        </nav>
    <?php endif; ?>

</main>

<?php
// Include footer
require_once "includes/footer.php";
?>
