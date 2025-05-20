<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include functions file
require_once 'includes/functions.php';

// Redirect if already logged in
if (is_admin_logged_in()) {
    header("Location: admin/dashboard.php");
    exit();
} elseif (is_employee_logged_in()) {
    header("Location: employee/dashboard.php");
    exit();
}

// Include header
require_once 'includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header text-center">
                <h3><i class="fas fa-clock"></i> Welcome to SSS IT WORLD Attendance System</h3>
            </div>
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="fas fa-user-shield fa-4x mb-3 text-primary"></i>
                                <h4>Admin Login</h4>
                                <p>Login as administrator to manage employees and view attendance records.</p>
                                <a href="admin_login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Admin Login
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="fas fa-user fa-4x mb-3 text-primary"></i>
                                <h4>Employee Login</h4>
                                <p>Login as employee to mark your attendance and view your attendance history.</p>
                                <a href="employee_login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Employee Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h4>About the System</h4>
                    <p>
                        SSS IT WORLD Attendance System is a web-based application designed to track employee attendance efficiently.
                        Employees can check-in and check-out twice a day, while administrators can manage employee records and monitor attendance.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?> 