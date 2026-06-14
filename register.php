<?php
// Project: PHP & MySQL Blog Management System (Task 2)
// File: register.php
// Description: Handle user signup and credential creation.

// Start the session to keep track of any messages
session_start();

// Include database connection configuration
require_once "config/database.php";

$error = "";
$success = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and trim form inputs to remove extra white spaces
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    
    // Basic validation checks
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if username is already taken in the database
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $check_stmt->execute(['username' => $username]);
            
            if ($check_stmt->rowCount() > 0) {
                $error = "Username is already taken. Please select another.";
            } else {
                // Securely hash the user password using PHP's native bcrypt implementation
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert the new user into the database
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                $insert_stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password
                ]);
                
                $success = "Registration successful! You can now <a href='login.php'>log in here</a>.";
            }
        } catch (PDOException $e) {
            $error = "Error saving to database: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blog Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Simple Navigation Bar -->
    <nav>
        <div class="container nav-container">
            <div class="logo">
                <a href="index.php">BlogSystem</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" style="color: #ffffff;">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container">
        <div class="form-box">
            <h1>Create Account</h1>
            <p style="color: #718096; margin-bottom: 20px;">Join the internship blog network</p>

            <!-- Error Alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Success Alerts -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="register.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="username">Choose Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Create Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
            </form>

            <p style="text-align: center; margin-top: 20px; font-size: 14px; color: #4a5568;">
                Already have an account? <a href="login.php" style="color: #3498db; text-decoration: none; font-weight: 600;">Login here</a>
            </p>
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
