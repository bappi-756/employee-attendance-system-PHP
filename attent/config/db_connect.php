<?php
// Set timezone to Bangladesh time (UTC+6)
date_default_timezone_set('Asia/Dhaka');

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "attendance_system";

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?> 