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

// Set default filter values
$date_filter = isset($_GET['date']) ? $_GET['date'] : get_current_date();
$employee_filter = isset($_GET['employee']) ? intval($_GET['employee']) : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query based on filters
$query = "SELECT a.*, e.full_name, e.employee_id as emp_id, e.photo, e.position 
          FROM attendance a 
          JOIN employees e ON a.employee_id = e.id 
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($date_filter)) {
    $query .= " AND a.attendance_date = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if (!empty($employee_filter)) {
    $query .= " AND a.employee_id = ?";
    $params[] = $employee_filter;
    $types .= "i";
}

if (!empty($status_filter)) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY a.attendance_date DESC, e.full_name ASC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$attendance_records = mysqli_stmt_get_result($stmt);

// Get all employees for filter dropdown
$query = "SELECT id, full_name, employee_id FROM employees ORDER BY full_name ASC";
$employees = mysqli_query($conn, $query);

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-clipboard-list"></i> Attendance Records</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5>Filter Attendance</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo $date_filter; ?>">
            </div>
            <div class="col-md-4">
                <label for="employee" class="form-label">Employee</label>
                <select class="form-select" id="employee" name="employee">
                    <option value="0">All Employees</option>
                    <?php while ($emp = mysqli_fetch_assoc($employees)): ?>
                        <option value="<?php echo $emp['id']; ?>" <?php echo ($employee_filter == $emp['id']) ? 'selected' : ''; ?>>
                            <?php echo $emp['full_name'] . ' (' . $emp['employee_id'] . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
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
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Check-in (Morning)</th>
                            <th>Check-out (Morning)</th>
                            <th>Check-in (Afternoon)</th>
                            <th>Check-out (Afternoon)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($attendance_records)): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($row['attendance_date'])); ?></td>
                                <td>
                                    <img src="../uploads/<?php echo $row['photo']; ?>" alt="Profile" width="30" height="30" class="rounded-circle me-2">
                                    <?php echo $row['full_name']; ?> (<?php echo $row['emp_id']; ?>)
                                </td>
                                <td><?php echo $row['position']; ?></td>
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
                No attendance records found for the selected filters.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 