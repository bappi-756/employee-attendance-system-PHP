<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include functions file
require_once '../includes/functions.php';

// Check if user is logged in as employee
if (!is_employee_logged_in()) {
    $_SESSION['error'] = "Please login to access the employee dashboard";
    redirect('../employee_login.php');
}

// Get employee details
$employee_id = $_SESSION['employee_id'];
$employee = get_employee($employee_id);

if (!$employee) {
    $_SESSION['error'] = "Employee not found";
    redirect('../employee_login.php');
}

// Set default filter values
$month_filter = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Calculate month start and end dates
$month_start = $month_filter . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

// Build query based on filters
$query = "SELECT * FROM attendance 
          WHERE employee_id = ? 
          AND attendance_date BETWEEN ? AND ?";
$params = [$employee_id, $month_start, $month_end];
$types = "iss";

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY attendance_date DESC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$attendance_records = mysqli_stmt_get_result($stmt);

// Get attendance statistics for the selected month
// Get present days
$query = "SELECT COUNT(*) as present_days FROM attendance 
          WHERE employee_id = ? AND attendance_date BETWEEN ? AND ? AND status = 'present'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $employee_id, $month_start, $month_end);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$present_days = mysqli_fetch_assoc($result)['present_days'];

// Get half days
$query = "SELECT COUNT(*) as half_days FROM attendance 
          WHERE employee_id = ? AND attendance_date BETWEEN ? AND ? AND status = 'half-day'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $employee_id, $month_start, $month_end);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$half_days = mysqli_fetch_assoc($result)['half_days'];

// Get absent days
$query = "SELECT COUNT(*) as absent_days FROM attendance 
          WHERE employee_id = ? AND attendance_date BETWEEN ? AND ? AND status = 'absent'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $employee_id, $month_start, $month_end);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$absent_days = mysqli_fetch_assoc($result)['absent_days'];

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-clipboard-list"></i> My Attendance History</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Monthly Statistics (<?php echo date('F Y', strtotime($month_start)); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h1 class="display-4"><?php echo $present_days; ?></h1>
                                <h5>Present Days</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning">
                            <div class="card-body">
                                <h1 class="display-4"><?php echo $half_days; ?></h1>
                                <h5>Half Days</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h1 class="display-4"><?php echo $absent_days; ?></h1>
                                <h5>Absent Days</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5>Filter Attendance</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <label for="month" class="form-label">Month</label>
                <input type="month" class="form-control" id="month" name="month" value="<?php echo $month_filter; ?>">
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="present" <?php echo ($status_filter == 'present') ? 'selected' : ''; ?>>Present</option>
                    <option value="absent" <?php echo ($status_filter == 'absent') ? 'selected' : ''; ?>>Absent</option>
                    <option value="half-day" <?php echo ($status_filter == 'half-day') ? 'selected' : ''; ?>>Half Day</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filter
                </button>
                <a href="attendance.php" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Attendance Records</h5>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($attendance_records) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Check-in (Morning)</th>
                            <th>Check-out (Morning)</th>
                            <th>Check-in (Afternoon)</th>
                            <th>Check-out (Afternoon)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($attendance_records)): ?>
                            <tr>
                                <td><?php echo date('d M Y (D)', strtotime($row['attendance_date'])); ?></td>
                                <td><?php echo $row['check_in_morning'] ? date('h:i A', strtotime($row['check_in_morning'])) : 'Not Recorded'; ?></td>
                                <td><?php echo $row['check_out_morning'] ? date('h:i A', strtotime($row['check_out_morning'])) : 'Not Recorded'; ?></td>
                                <td><?php echo $row['check_in_afternoon'] ? date('h:i A', strtotime($row['check_in_afternoon'])) : 'Not Recorded'; ?></td>
                                <td><?php echo $row['check_out_afternoon'] ? date('h:i A', strtotime($row['check_out_afternoon'])) : 'Not Recorded'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No attendance records found for the selected month.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 