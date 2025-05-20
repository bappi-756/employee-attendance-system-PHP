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

// Get statistics
$total_employees = 0;
$present_today = 0;
$absent_today = 0;
$half_day_today = 0;

// Get total employees
$query = "SELECT COUNT(*) as total FROM employees";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_employees = $row['total'];
}

// Get today's date
$today = get_current_date();

// Get present employees today
$query = "SELECT COUNT(*) as present FROM attendance WHERE attendance_date = ? AND status = 'present'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $present_today = $row['present'];
}

// Get absent employees today
$query = "SELECT COUNT(*) as absent FROM attendance WHERE attendance_date = ? AND status = 'absent'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $absent_today = $row['absent'];
}

// Get half-day employees today
$query = "SELECT COUNT(*) as half_day FROM attendance WHERE attendance_date = ? AND status = 'half-day'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $half_day_today = $row['half_day'];
}

// Get recent attendance records
$query = "SELECT a.*, e.full_name, e.employee_id as emp_id, e.photo 
          FROM attendance a 
          JOIN employees e ON a.employee_id = e.id 
          WHERE a.attendance_date = ? 
          ORDER BY a.id DESC 
          LIMIT 10";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$recent_attendance = mysqli_stmt_get_result($stmt);

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
        <div>
            <span class="badge bg-primary p-2">
                <i class="fas fa-calendar me-1"></i> Today: <?php echo date('d M Y', strtotime($today)); ?>
            </span>
            <span class="ms-2 text-dark">
                Welcome, <strong><?php echo $_SESSION['admin_username']; ?></strong>!
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Total Employees Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <h5>TOTAL EMPLOYEES</h5>
                            <h1><?php echo $total_employees; ?></h1>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-primary stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="employees.php" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-arrow-right me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Present Today Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <h5>PRESENT TODAY</h5>
                            <h1><?php echo $present_today; ?></h1>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="attendance.php?status=present" class="btn btn-sm btn-success w-100">
                            <i class="fas fa-arrow-right me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Half-Day Today Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-warning h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <h5>HALF-DAY TODAY</h5>
                            <h1><?php echo $half_day_today; ?></h1>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-adjust fa-2x text-warning stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="attendance.php?status=half-day" class="btn btn-sm btn-warning w-100">
                            <i class="fas fa-arrow-right me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Absent Today Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-danger h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <h5>ABSENT TODAY</h5>
                            <h1><?php echo $absent_today; ?></h1>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-danger stat-card-icon"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="attendance.php?status=absent" class="btn btn-sm btn-danger w-100">
                            <i class="fas fa-arrow-right me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt me-2"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="add_employee.php" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus me-1"></i> Add New Employee
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="attendance.php" class="btn btn-info w-100 text-white">
                                <i class="fas fa-clipboard-list me-1"></i> View All Attendance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="employees.php" class="btn btn-success w-100">
                                <i class="fas fa-users-cog me-1"></i> Manage Employees
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button onclick="window.print()" class="btn btn-secondary w-100">
                                <i class="fas fa-print me-1"></i> Print Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance Records -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-clipboard-list me-2"></i> Recent Attendance Records (<?php echo date('d M Y', strtotime($today)); ?>)
                    </div>
                    <a href="attendance.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recent_attendance) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Check-in (Morning)</th>
                                        <th>Check-out (Morning)</th>
                                        <th>Check-in (Afternoon)</th>
                                        <th>Check-out (Afternoon)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($recent_attendance)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../uploads/<?php echo $row['photo']; ?>" alt="Profile" width="40" height="40" class="rounded-circle me-2">
                                                    <div>
                                                        <div class="fw-bold"><?php echo $row['full_name']; ?></div>
                                                        <div class="small text-muted"><?php echo $row['emp_id']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($row['check_in_morning']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('h:i A', strtotime($row['check_in_morning'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Not Recorded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['check_out_morning']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('h:i A', strtotime($row['check_out_morning'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Not Recorded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['check_in_afternoon']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('h:i A', strtotime($row['check_in_afternoon'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Not Recorded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['check_out_afternoon']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('h:i A', strtotime($row['check_out_afternoon'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Not Recorded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="attendance.php?employee=<?php echo $row['employee_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-history"></i> History
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No attendance records found for today.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 