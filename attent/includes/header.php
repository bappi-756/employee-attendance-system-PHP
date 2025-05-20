<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSS IT WORLD - Attendance System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --card-border-radius: 0.75rem;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Poppins', sans-serif;
            color: #5a5c69;
        }
        
        .navbar {
            background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
            padding: 0.75rem 1rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #ffffff !important;
            font-size: 1.5rem;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }
        
        .navbar-nav .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .card {
            border: none;
            border-radius: var(--card-border-radius);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: linear-gradient(90deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            border-radius: var(--card-border-radius) var(--card-border-radius) 0 0 !important;
            font-weight: 600;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
        }
        
        .card-header i {
            margin-right: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color) 0%, #224abe 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #224abe 0%, var(--primary-color) 100%);
        }
        
        .btn-success {
            background: linear-gradient(90deg, var(--secondary-color) 0%, #13855c 100%);
            border: none;
        }
        
        .btn-success:hover {
            background: linear-gradient(90deg, #13855c 0%, var(--secondary-color) 100%);
        }
        
        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 1.25rem 0;
            margin-top: 2.5rem;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fff;
        }
        
        .alert {
            border-radius: var(--card-border-radius);
            border-left: 4px solid;
        }
        
        .alert-success {
            border-left-color: var(--secondary-color);
        }
        
        .alert-danger {
            border-left-color: var(--danger-color);
        }
        
        .table {
            border-radius: var(--card-border-radius);
            overflow: hidden;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
        }
        
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        
        /* Admin dashboard specific styles */
        .stat-card {
            border-left: 4px solid;
            border-radius: var(--card-border-radius);
        }
        
        .stat-card-primary {
            border-left-color: var(--primary-color);
        }
        
        .stat-card-success {
            border-left-color: var(--secondary-color);
        }
        
        .stat-card-warning {
            border-left-color: var(--warning-color);
        }
        
        .stat-card-danger {
            border-left-color: var(--danger-color);
        }
        
        .stat-card .card-body {
            padding: 1.25rem;
        }
        
        .stat-card h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .stat-card h5 {
            color: #b7b9cc;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .stat-card-icon {
            font-size: 2rem;
            opacity: 0.3;
        }
        
        /* Admin forms */
        .form-control {
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            border: 1px solid #d1d3e2;
        }
        
        .form-control:focus {
            border-color: #bac8f3;
            box-shadow: none;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <?php
            // Determine if we're in a subdirectory
            $base_url = '';
            if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
                strpos($_SERVER['PHP_SELF'], '/employee/') !== false) {
                $base_url = '../';
            }
            ?>
            <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
                <i class="fas fa-clock"></i> SSS IT WORLD
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_admin_logged_in()): ?>
                        <!-- Admin Navigation -->
                        <?php
                        $admin_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '' : 'admin/';
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url . $admin_path; ?>admin/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url . $admin_path; ?>admin/employees.php">
                                <i class="fas fa-users"></i> Employees
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url . $admin_path; ?>admin/attendance.php">
                                <i class="fas fa-clipboard-list"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url . $admin_path; ?>admin/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php elseif (is_employee_logged_in()): ?>
                        <!-- Employee Navigation -->
                        <?php
                        $employee_path = (strpos($_SERVER['PHP_SELF'], '/employee/') !== false) ? '' : 'employee/';
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url . $employee_path; ?>employee/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url . $employee_path; ?>employee/attendance.php">
                                <i class="fas fa-clipboard-list"></i> My Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url . $employee_path; ?>employee/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Public Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>index.php">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin_login.php">
                                <i class="fas fa-user-shield"></i> Admin Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>employee_login.php">
                                <i class="fas fa-user"></i> Employee Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        // Display session messages if they exist
        if (isset($_SESSION['success'])) {
            echo display_success($_SESSION['success']);
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['error'])) {
            echo display_error($_SESSION['error']);
            unset($_SESSION['error']);
        }
        ?>
    </div>
</body>
</html> 