<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include functions file
require_once '../includes/functions.php';

// Unset admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Set success message
$_SESSION['success'] = "You have been successfully logged out";

// Redirect to login page
redirect('../admin_login.php');
?> 