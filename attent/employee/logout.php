<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include functions file
require_once '../includes/functions.php';

// Unset employee session variables
unset($_SESSION['employee_id']);
unset($_SESSION['employee_name']);
unset($_SESSION['employee_photo']);

// Set success message
$_SESSION['success'] = "You have been successfully logged out";

// Redirect to login page
redirect('../employee_login.php');
?> 