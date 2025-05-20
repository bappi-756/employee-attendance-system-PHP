<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include functions file
require_once '../includes/functions.php';

// Check if user is logged in as admin
if (!is_admin_logged_in()) {
    $_SESSION['error'] = "Please login to access the admin dashboard";
    redirect('../admin_login.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = sanitize($_POST['full_name']);
    $employee_id = sanitize($_POST['employee_id']);
    $position = sanitize($_POST['position']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form data
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($employee_id)) {
        $errors[] = "Employee ID is required";
    } else {
        // Check if employee ID already exists
        $query = "SELECT id FROM employees WHERE employee_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $employee_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Employee ID already exists";
        }
    }
    
    if (empty($position)) {
        $errors[] = "Position is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Handle photo upload
    $photo = 'default.jpg';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_image($_FILES['photo'], '../uploads/');
        
        if ($upload_result['success']) {
            $photo = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // If no errors, insert employee
    if (empty($errors)) {
        // Store plain text password
        $plain_password = $password;
        
        // Insert employee
        $query = "INSERT INTO employees (employee_id, full_name, position, photo, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $employee_id, $full_name, $position, $photo, $plain_password);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Employee added successfully";
            redirect('employees.php');
        } else {
            $_SESSION['error'] = "Error adding employee: " . mysqli_error($conn);
        }
    } else {
        // Set error message
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-user-plus"></i> Add New Employee</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="employees.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Employees
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Employee Information</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="employee_id" class="form-label">Employee ID</label>
                    <input type="text" class="form-control" id="employee_id" name="employee_id" required value="<?php echo isset($_POST['employee_id']) ? $_POST['employee_id'] : ''; ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="position" class="form-label">Position</label>
                    <input type="text" class="form-control" id="position" name="position" required value="<?php echo isset($_POST['position']) ? $_POST['position'] : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="photo" class="form-label">Photo</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg, image/png, image/jpg">
                    <small class="text-muted">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Employee
                </button>
                <a href="employees.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 