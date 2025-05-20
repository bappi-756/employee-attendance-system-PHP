<?php
// Set timezone to Bangladesh time (UTC+6)
date_default_timezone_set('Asia/Dhaka');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once dirname(__DIR__) . '/config/db_connect.php';

/**
 * Sanitize user input
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Redirect to a specific page
 */
function redirect($location) {
    header("Location: $location");
    exit();
}

/**
 * Check if user is logged in as admin
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

/**
 * Check if user is logged in as employee
 */
function is_employee_logged_in() {
    return isset($_SESSION['employee_id']);
}

/**
 * Display error message
 */
function display_error($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

/**
 * Display success message
 */
function display_success($message) {
    return "<div class='alert alert-success'>$message</div>";
}

/**
 * Upload image file
 */
function upload_image($file, $target_dir = "uploads/") {
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is a actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ["success" => false, "message" => "File is not an image."];
    }
    
    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "File is too large. Max 5MB allowed."];
    }
    
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return ["success" => false, "message" => "Only JPG, JPEG, PNG files are allowed."];
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $new_filename];
    } else {
        return ["success" => false, "message" => "Error uploading file."];
    }
}

/**
 * Get current date in Y-m-d format
 */
function get_current_date() {
    return date('Y-m-d');
}

/**
 * Check if employee has already checked in for the day
 */
function has_checked_in($employee_id, $period = 'morning') {
    global $conn;
    $today = get_current_date();
    
    $check_field = ($period == 'morning') ? 'check_in_morning' : 'check_in_afternoon';
    
    $query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ? AND $check_field IS NOT NULL";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $employee_id, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
}

/**
 * Check if employee has already checked out for the day
 */
function has_checked_out($employee_id, $period = 'morning') {
    global $conn;
    $today = get_current_date();
    
    $check_field = ($period == 'morning') ? 'check_out_morning' : 'check_out_afternoon';
    
    $query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ? AND $check_field IS NOT NULL";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $employee_id, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
}

/**
 * Get employee details by ID
 */
function get_employee($employee_id) {
    global $conn;
    
    $query = "SELECT * FROM employees WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}
?> 