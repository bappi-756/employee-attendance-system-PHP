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

// Check if employee ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Employee ID is required";
    redirect('employees.php');
}

$employee_id = intval($_GET['id']);

// Get employee details
$query = "SELECT * FROM employees WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "Employee not found";
    redirect('employees.php');
}

$employee = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = sanitize($_POST['full_name']);
    $emp_id = sanitize($_POST['employee_id']);
    $position = sanitize($_POST['position']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form data
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($emp_id)) {
        $errors[] = "Employee ID is required";
    } else {
        // Check if employee ID already exists for other employees
        $query = "SELECT id FROM employees WHERE employee_id = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $emp_id, $employee_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Employee ID already exists";
        }
    }
    
    if (empty($position)) {
        $errors[] = "Position is required";
    }
    
    // Handle photo upload
    $photo = $employee['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_image($_FILES['photo'], '../uploads/');
        
        if ($upload_result['success']) {
            // Delete old photo if not default
            if ($photo !== 'default.jpg') {
                $old_photo_path = "../uploads/" . $photo;
                if (file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }
            
            $photo = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // If no errors, update employee
    if (empty($errors)) {
        // Check if password is being updated
        if (!empty($password)) {
            // Validate password
            if (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters long";
            } elseif ($password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            } else {
                // Store plain text password
                $plain_password = $password;
                
                // Update employee with new password
                $query = "UPDATE employees SET employee_id = ?, full_name = ?, position = ?, photo = ?, password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "sssssi", $emp_id, $full_name, $position, $photo, $plain_password, $employee_id);
            }
        } else {
            // Update employee without changing password
            $query = "UPDATE employees SET employee_id = ?, full_name = ?, position = ?, photo = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $emp_id, $full_name, $position, $photo, $employee_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Employee updated successfully";
            redirect('employees.php');
        } else {
            $_SESSION['error'] = "Error updating employee: " . mysqli_error($conn);
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
        <h2><i class="fas fa-user-edit"></i> Edit Employee</h2>
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
        <div class="row mb-4">
            <div class="col-md-3 text-center">
                <img src="../uploads/<?php echo $employee['photo']; ?>" alt="Profile" class="img-fluid rounded-circle profile-img mb-3">
                <h5><?php echo $employee['full_name']; ?></h5>
                <p class="text-muted"><?php echo $employee['position']; ?></p>
            </div>
            <div class="col-md-9">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo $employee['full_name']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="employee_id" name="employee_id" required value="<?php echo $employee['employee_id']; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" required value="<?php echo $employee['position']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg, image/png, image/jpg">
                            <small class="text-muted">Leave empty to keep current photo</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Leave empty to keep current password</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Employee
                        </button>
                        <a href="employees.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 