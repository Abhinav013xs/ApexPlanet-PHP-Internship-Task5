# Task 3: Advanced Blog Management System

Welcome to **Task 3** of the Web Development Internship. This task upgrades the Blog Management System to incorporate **Bootstrap 5**, integrated **Search**, **Pagination**, and a complete **Dashboard Administration** panel.

---

## 1. Project Folder Structure

Ensure your project files are placed exactly as shown below:

```text
blog-project/
├── index.php             # Public homepage listing blog posts with pagination
├── register.php          # User registration view styled with Bootstrap 5
├── login.php             # User login view styled with Bootstrap 5
├── logout.php            # Session termination logic
├── dashboard.php         # Author control panel with stats, paginated tables, and search
├── create-post.php       # Form to write a new blog post
├── edit-post.php         # Form to modify an existing blog post
├── delete-post.php       # Controller to delete a post
├── search.php            # Dedicated public search results page with pagination
├── config/
│   └── database.php      # PDO database connection handler
├── css/
│   └── style.css         # Custom CSS overrides for hover animations
├── js/
│   └── script.js         # JavaScript file providing delete confirmation boxes
└── README.md             # Project instruction guide (this file)
```

---

## 2. Dynamic Pagination Explanation (5 Posts Per Page)
We implement pagination dynamically in SQL and PHP. Here is a step-by-step logic guide:

1. **Active Page Detection**: Check the URL query string `page` (e.g. `?page=2`). If missing or non-numeric, it defaults to Page `1`.
2. **Limit & Offset Calculation**:
   - Limit: `$limit = 5;`
   - Offset: `$offset = ($page - 1) * $limit;` (Page 1 starts at index 0, Page 2 at index 5, etc.).
3. **Database Count Query**: Run a `SELECT COUNT(*)` query to count the total rows matching the selection constraints (or matching the search terms).
4. **Calculated Total Pages**: Determine total pages by dividing total records by page size, rounded up: `$total_pages = ceil($total_records / $limit);`
5. **Paginated Data Retrieval**: Query records utilizing MySQL clauses `LIMIT :limit OFFSET :offset`. Placeholders are bound as integer types in PDO:
   ```php
   $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
   $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
   ```
6. **Query Persistence**: When pagination links are clicked, the active search query is URL-encoded and appended (`?q=query&page=2`) using `http_build_query()` or string concats to ensure pages align with search filters.

---

## 3. Dynamic Search Functionality Explanation
The search bar uses standard relational database filters:

1. **Collect Input**: The form sends inputs using a GET request (e.g. `search.php?q=keyword` or `dashboard.php?search=keyword`).
2. **LIKE Queries**: The database filters records using the SQL wildcards query:
   ```sql
   SELECT * FROM posts WHERE title LIKE :search OR content LIKE :search
   ```
3. **Bound placeholders**: The keyword is bound as `'%keyword%'` using PDO to prevent SQL injection.
4. **Zero State Check**: If the query returns zero rows, the application renders a clean Bootstrap "No posts found" message.

---

## 4. Bootstrap 5 CDN Links Used
The views load external style resources via CDN paths in header blocks:

*   **CSS Stylesheet**:
    ```html
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    ```
*   **Icon Library**:
    ```html
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    ```
*   **JavaScript Bundle**:
    ```html
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    ```

---

## 5. Running the Project in XAMPP

1. Ensure your **XAMPP Control Panel** is open with **Apache** and **MySQL** services started.
2. Place the project files inside: `C:\xampp\htdocs\blog-project\`.
3. Open your browser and navigate to:
   ```text
   http://localhost:8000
   ```
   *(Or if running through XAMPP Apache, use `http://localhost/blog-project/index.php`)*
