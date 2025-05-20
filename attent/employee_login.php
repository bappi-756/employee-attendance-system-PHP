<?php
// Include header
require_once 'includes/header.php';

// Redirect if already logged in as employee
if (is_employee_logged_in()) {
    header("Location: employee/dashboard.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = sanitize($_POST['employee_id']);
    $password = $_POST['password'];
    
    // Validate form data
    if (empty($employee_id) || empty($password)) {
        $_SESSION['error'] = "Employee ID and password are required";
    } else {
        // Check if employee exists
        $query = "SELECT * FROM employees WHERE employee_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $employee_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $employee = mysqli_fetch_assoc($result);
            
            // Verify password (plain text comparison)
            if ($password === $employee['password']) {
                // Set session variables
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_name'] = $employee['full_name'];
                $_SESSION['employee_photo'] = $employee['photo'];
                
                // Redirect to employee dashboard
                header("Location: employee/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid password";
            }
        } else {
            $_SESSION['error'] = "Employee not found";
        }
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h4><i class="fas fa-user"></i> Employee Login</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employee_id" name="employee_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                <div class="mt-3 text-center">
                    <p class="text-muted">Please contact the administrator if you forgot your login credentials.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?> 