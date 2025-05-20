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

// Handle employee deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $employee_id = intval($_GET['delete']);
    
    // Get employee photo before deletion
    $query = "SELECT photo FROM employees WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $employee = mysqli_fetch_assoc($result);
        $photo = $employee['photo'];
        
        // Delete employee
        $query = "DELETE FROM employees WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $employee_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Delete employee photo if not default
            if ($photo !== 'default.jpg') {
                $photo_path = "../uploads/" . $photo;
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            }
            
            $_SESSION['success'] = "Employee deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting employee";
        }
    } else {
        $_SESSION['error'] = "Employee not found";
    }
    
    // Use absolute path for redirection to ensure proper navigation
    header("Location: employees.php");
    exit();
}

// Get all employees
$query = "SELECT * FROM employees ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-users"></i> Manage Employees</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="add_employee.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add New Employee
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Employee List</h5>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Employee ID</th>
                            <th>Position</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <img src="../uploads/<?php echo $row['photo']; ?>" alt="Profile" width="50" height="50" class="rounded-circle">
                                </td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['employee_id']; ?></td>
                                <td><?php echo $row['position']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="employees.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this employee?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No employees found. <a href="add_employee.php">Add an employee</a> to get started.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 