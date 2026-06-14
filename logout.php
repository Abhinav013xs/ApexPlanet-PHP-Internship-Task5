<?php
// Project: PHP & MySQL Blog Management System (Task 2)
// File: logout.php
// Description: Terminate user authentication state.

// Start the session to gain access to it
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session on the server
session_destroy();

// Redirect back to the public homepage
header("Location: index.php");
exit;
?>
