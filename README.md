# Blog Management System - Final Internship Project (Task 5)

A secure, modular, and responsive **Blog Management System** developed in PHP & MySQL during my Web Development Internship. This final release integrates all previous requirements (CRUD, Search, Pagination, prepared statements, role validations) and introduces user profiles, administrative account management, dynamic includes, custom error screens, and UX enhancements.

---

## 1. Project Overview & Features

*   **User Authentication & Security**: Custom registration and login views with client/server validation. Sessions are hardened against Session Fixation (ID regeneration) and Session Hijacking (strict cookies).
*   **Role-Based Access Control (RBAC)**: Supports `Admin` and `Editor` roles.
    *   **Admins**: Full control to create/edit/delete any post, view user statistics, browse user directories, change roles, and delete users.
    *   **Editors**: Can create articles and edit/delete only their own posts. They only have access to their own post statistics.
*   **Secure CRUD & Search Operations**: Prepared statements protect against SQL Injection. Outputs are escaped using UTF-8 HTML entities to mitigate Cross-Site Scripting (XSS).
*   **Modern Interactive Dashboard**: Displays dynamic statistics cards, quick action panels, paginated tables, and inline search filters.
*   **User Profile Manager**: Allows registered users to change their account details, update usernames, and reset passwords with strength and duplicate checks.
*   **Modular Architecture**: Reusable includes for headers, footers, and navbars. Client assets are organized cleanly under `assets/`.
*   **Friendly Error Handling**: Custom `404 Not Found` and `403 Access Denied` views handle routing and permissions checks without leaking backend exceptions.

---

## 2. Tech Stack

*   **Backend**: PHP 8 (Procedural with PDO)
*   **Database**: MySQL
*   **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Bootstrap Icons
*   **Fonts**: Inter (Google Fonts)

---

## 3. Project Folder Structure

```text
blog-project/
│
├── admin/
│   ├── users.php         # User directory management (Admin only)
│   ├── roles.php         # User roles configuration form (Admin only)
│   └── delete-user.php   # Account deletion controller (Admin only)
│
├── config/
│   └── database.php      # PDO database connection setup (emulation disabled)
│
├── middleware/
│   ├── auth.php          # Session active validation check
│   └── admin.php         # Admin authorization permission guard
│
├── includes/
│   ├── header.php        # Unified header template with style references
│   ├── navbar.php        # Dynamic navigation bar component (identifies active route)
│   └── footer.php        # Dynamic copyright footer template
│
├── assets/
│   ├── css/
│   │   └── style.css     # Theme and layout styling rules
│   ├── js/
│   │   └── script.js     # Deletion confirmations and validations
│   └── images/           # Local media assets
│
├── profile.php           # User profile and password resets
├── dashboard.php         # Main administrative workspace
├── register.php          # User registration view
├── login.php             # User login panel
├── logout.php            # Session termination logic
├── create-post.php       # Form to add a new article
├── edit-post.php         # Form to update a post
├── delete-post.php       # Controller to remove a post
├── index.php             # Public homepage listing articles
├── search.php            # Dedicated public search page
├── 404.php               # Custom 404 page
├── 403.php               # Custom 403 access denied page
└── README.md             # Project documentation (this file)
```

---

## 4. Database Setup & Seeding

1. Start **Apache** and **MySQL** in your **XAMPP Control Panel**.
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) and create a database named `blog`.
3. Import the database schema and Task 4/5 updates. Create the tables and seed accounts by running the SQL queries below:

```sql
CREATE DATABASE IF NOT EXISTS `blog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `blog`;

-- 1. Create Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'editor') DEFAULT 'editor',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create Posts Table
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `content` TEXT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_posts_users FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Seed Default Administrator Account (username: 'admin', password: 'admin123')
INSERT INTO `users` (`id`, `username`, `password`, `role`) 
VALUES (1, 'admin', '$2y$10$MvNfX7pX7qVlQjFfRk5TdeY0JqL2c0V2YyX.1kY8O7t8wz9o9u9zG', 'admin')
ON DUPLICATE KEY UPDATE `role` = 'admin';
```

---

## 5. Installation & Run Steps

1. Clone or copy this repository into your XAMPP server root directory (usually `C:\xampp\htdocs\Blog`).
2. Alternatively, run the built-in PHP development server in the project directory:
   ```bash
   php -S localhost:8000
   ```
3. Open your browser and navigate to **`http://localhost:8000`** (or `http://localhost/Blog`).
4. **Login as default Admin**:
   - Username: `admin`
   - Password: `admin123`
5. **Login as default Editor** (Register a new account or change a role in the Admin Panel).

---

## 6. Security Implementation Details

*   **Prepared Statements**: Emulation is disabled (`PDO::ATTR_EMULATE_PREPARES => false`) to enforce server-side compilation of SQL syntax.
*   **XSS Mitigation**: Standard `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` prevents malicious scripts from executing in active sessions.
*   **Password Cryptography**: Employs industry-standard bcrypt hashing (`password_hash` with `PASSWORD_BCRYPT`).
*   **Session Hardening**: Cookies have `httponly`, `samesite=Strict`, and `secure` properties enabled to defend against CSRF and XSS-based hijacking.

---

## 7. Future Improvements

*   **Rich Text Editor Integration**: Include a WYSIWYG editor (such as Quill or TinyMCE) with HTML sanitization on the server side.
*   **Image Uploads**: Implement a secure image upload mechanism for blog thumbnails.
*   **Password Reset via Email**: Setup PHP Mailer to support secure email-based verification tokens for forgotten credentials.
