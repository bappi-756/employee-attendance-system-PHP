<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to Bangladesh time (UTC+6)
date_default_timezone_set('Asia/Dhaka');

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

// Get today's date
$today = get_current_date();

// Check if attendance record exists for today
$query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "is", $employee_id, $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$attendance = null;
$morning_check_in = false;
$morning_check_out = false;
$afternoon_check_in = false;
$afternoon_check_out = false;

if (mysqli_num_rows($result) > 0) {
    $attendance = mysqli_fetch_assoc($result);
    $morning_check_in = !empty($attendance['check_in_morning']);
    $morning_check_out = !empty($attendance['check_out_morning']);
    $afternoon_check_in = !empty($attendance['check_in_afternoon']);
    $afternoon_check_out = !empty($attendance['check_out_afternoon']);
}

// Handle AJAX attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['ajax'])) {
    $action = $_POST['action'];
    $now = date('Y-m-d H:i:s');
    $current_hour = date('H');
    $response = array('success' => false);
    
    // If attendance record doesn't exist, create one
    if (!$attendance) {
        $query = "INSERT INTO attendance (employee_id, attendance_date) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $employee_id, $today);
        mysqli_stmt_execute($stmt);
        
        // Get the newly created attendance record
        $query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $employee_id, $today);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $attendance = mysqli_fetch_assoc($result);
    }
    
    // Update attendance record based on action
    $field = '';
    $message = '';
    
    switch ($action) {
        case 'check_in_morning':
            $field = 'check_in_morning';
            $message = 'Morning check-in recorded successfully';
            break;
        case 'check_out_morning':
            $field = 'check_out_morning';
            $message = 'Morning check-out recorded successfully';
            break;
        case 'check_in_afternoon':
            $field = 'check_in_afternoon';
            $message = 'Afternoon check-in recorded successfully';
            break;
        case 'check_out_afternoon':
            $field = 'check_out_afternoon';
            $message = 'Afternoon check-out recorded successfully';
            break;
    }
    
    if (!empty($field)) {
        $query = "UPDATE attendance SET $field = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $now, $attendance['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update status based on check-ins and check-outs
            $status = 'absent';
            
            // If all 4 check-ins/outs are recorded, mark as present
            if ($field === 'check_out_afternoon' || 
                (!empty($attendance['check_in_morning']) && 
                 !empty($attendance['check_out_morning']) && 
                 !empty($attendance['check_in_afternoon']) && 
                 !empty($attendance['check_out_afternoon']))) {
                $status = 'present';
            } 
            // If at least one check-in/out is recorded, mark as half-day
            elseif (!empty($attendance['check_in_morning']) || 
                    !empty($attendance['check_out_morning']) || 
                    !empty($attendance['check_in_afternoon']) || 
                    !empty($attendance['check_out_afternoon'])) {
                $status = 'half-day';
            }
            
            // Update status
            $query = "UPDATE attendance SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $status, $attendance['id']);
            mysqli_stmt_execute($stmt);
            
            // Return success response for AJAX
            $response['success'] = true;
            $response['message'] = $message;
            $response['time'] = date('h:i A', strtotime($now));
            $response['field'] = $field;
            
            echo json_encode($response);
            exit;
        } else {
            $response['message'] = "Error recording attendance";
            echo json_encode($response);
            exit;
        }
    }
    
    echo json_encode($response);
    exit;
}

// Handle regular form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !isset($_POST['ajax'])) {
    $action = $_POST['action'];
    $now = date('Y-m-d H:i:s');
    $current_hour = date('H');
    
    // Check if it's morning or afternoon
    $is_morning = $current_hour < 12;
    
    // If attendance record doesn't exist, create one
    if (!$attendance) {
        $query = "INSERT INTO attendance (employee_id, attendance_date) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $employee_id, $today);
        mysqli_stmt_execute($stmt);
        
        // Get the newly created attendance record
        $query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $employee_id, $today);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $attendance = mysqli_fetch_assoc($result);
    }
    
    // Update attendance record based on action
    $field = '';
    $message = '';
    
    switch ($action) {
        case 'check_in_morning':
            $field = 'check_in_morning';
            $message = 'Morning check-in recorded successfully';
            break;
        case 'check_out_morning':
            $field = 'check_out_morning';
            $message = 'Morning check-out recorded successfully';
            break;
        case 'check_in_afternoon':
            $field = 'check_in_afternoon';
            $message = 'Afternoon check-in recorded successfully';
            break;
        case 'check_out_afternoon':
            $field = 'check_out_afternoon';
            $message = 'Afternoon check-out recorded successfully';
            break;
    }
    
    if (!empty($field)) {
        $query = "UPDATE attendance SET $field = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $now, $attendance['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update status based on check-ins and check-outs
            $status = 'absent';
            
            // If all 4 check-ins/outs are recorded, mark as present
            if ($field === 'check_out_afternoon' || 
                (!empty($attendance['check_in_morning']) && 
                 !empty($attendance['check_out_morning']) && 
                 !empty($attendance['check_in_afternoon']) && 
                 !empty($attendance['check_out_afternoon']))) {
                $status = 'present';
            } 
            // If at least one check-in/out is recorded, mark as half-day
            elseif (!empty($attendance['check_in_morning']) || 
                    !empty($attendance['check_out_morning']) || 
                    !empty($attendance['check_in_afternoon']) || 
                    !empty($attendance['check_out_afternoon'])) {
                $status = 'half-day';
            }
            
            // Update status
            $query = "UPDATE attendance SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $status, $attendance['id']);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['success'] = $message;
            redirect('dashboard.php');
        } else {
            $_SESSION['error'] = "Error recording attendance";
        }
    }
}

// Get attendance statistics for current month
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

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

// Get recent attendance records
$query = "SELECT * FROM attendance 
          WHERE employee_id = ? 
          ORDER BY attendance_date DESC 
          LIMIT 5";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$recent_attendance = mysqli_stmt_get_result($stmt);

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-tachometer-alt"></i> Employee Dashboard</h2>
        <p>Welcome, <strong><?php echo $employee['full_name']; ?></strong>! Here's your attendance overview.</p>
        <div class="text-end">
            <div id="current-time" class="badge bg-primary p-2">
                <i class="fas fa-clock me-1"></i> <span id="live-time"></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Profile Information</h5>
            </div>
            <div class="card-body text-center">
                <img src="../uploads/<?php echo $employee['photo']; ?>" alt="Profile" class="img-fluid rounded-circle profile-img mb-3">
                <h4><?php echo $employee['full_name']; ?></h4>
                <p><strong>Employee ID:</strong> <?php echo $employee['employee_id']; ?></p>
                <p><strong>Position:</strong> <?php echo $employee['position']; ?></p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Monthly Statistics (<?php echo date('F Y'); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h4 class="text-success"><?php echo $present_days; ?></h4>
                        <p>Present</p>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-warning"><?php echo $half_days; ?></h4>
                        <p>Half Day</p>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-danger"><?php echo $absent_days; ?></h4>
                        <p>Absent</p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="attendance.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-calendar"></i> View Full History
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Today's Attendance (<?php echo date('d M Y'); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6>Morning Session</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <h5>Check-in</h5>
                                    <div id="morning-check-in-container">
                                        <?php if ($morning_check_in): ?>
                                            <span class="badge bg-success">Recorded at <?php echo date('h:i A', strtotime($attendance['check_in_morning'])); ?></span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary attendance-btn" data-action="check_in_morning" <?php echo (date('H') >= 12) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-sign-in-alt"></i> Check-in
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <h5>Check-out</h5>
                                    <div id="morning-check-out-container">
                                        <?php if ($morning_check_out): ?>
                                            <span class="badge bg-success">Recorded at <?php echo date('h:i A', strtotime($attendance['check_out_morning'])); ?></span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary attendance-btn" data-action="check_out_morning" <?php echo (!$morning_check_in || date('H') >= 12) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-sign-out-alt"></i> Check-out
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6>Afternoon Session</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <h5>Check-in</h5>
                                    <div id="afternoon-check-in-container">
                                        <?php if ($afternoon_check_in): ?>
                                            <span class="badge bg-success">Recorded at <?php echo date('h:i A', strtotime($attendance['check_in_afternoon'])); ?></span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary attendance-btn" data-action="check_in_afternoon" <?php echo (date('H') < 12) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-sign-in-alt"></i> Check-in
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <h5>Check-out</h5>
                                    <div id="afternoon-check-out-container">
                                        <?php if ($afternoon_check_out): ?>
                                            <span class="badge bg-success">Recorded at <?php echo date('h:i A', strtotime($attendance['check_out_afternoon'])); ?></span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary attendance-btn" data-action="check_out_afternoon" <?php echo (!$afternoon_check_in || date('H') < 12) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-sign-out-alt"></i> Check-out
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Morning check-in/out is available until 12:00 PM. Afternoon check-in/out is available after 12:00 PM.
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Recent Attendance History</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($recent_attendance) > 0): ?>
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
                                <?php while ($row = mysqli_fetch_assoc($recent_attendance)): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($row['attendance_date'])); ?></td>
                                        <td><?php echo $row['check_in_morning'] ? date('h:i A', strtotime($row['check_in_morning'])) : 'Not Recorded'; ?></td>
                                        <td><?php echo $row['check_out_morning'] ? date('h:i A', strtotime($row['check_out_morning'])) : 'Not Recorded'; ?></td>
                                        <td><?php echo $row['check_in_afternoon'] ? date('h:i A', strtotime($row['check_in_afternoon'])) : 'Not Recorded'; ?></td>
                                        <td><?php echo $row['check_out_afternoon'] ? date('h:i A', strtotime($row['check_out_afternoon'])) : 'Not Recorded'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="attendance.php" class="btn btn-primary">
                            <i class="fas fa-calendar"></i> View Full Attendance History
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No attendance records found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Live clock - Bangladesh time (UTC+6)
function updateClock() {
    // Create date object with Bangladesh time (UTC+6)
    const now = new Date();
    // Adjust to Bangladesh time (UTC+6)
    const bangladeshTime = new Date(now.getTime() + (6*60*60*1000 + now.getTimezoneOffset()*60*1000));
    
    let hours = bangladeshTime.getHours();
    const minutes = bangladeshTime.getMinutes().toString().padStart(2, '0');
    const seconds = bangladeshTime.getSeconds().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    
    document.getElementById('live-time').textContent = 
        hours + ':' + minutes + ':' + seconds + ' ' + ampm + ' (Bangladesh)';
    
    setTimeout(updateClock, 1000);
}

// Initialize clock
updateClock();

// AJAX attendance marking
$(document).ready(function() {
    $('.attendance-btn').click(function() {
        const action = $(this).data('action');
        
        $.ajax({
            url: 'dashboard.php',
            type: 'POST',
            data: {
                action: action,
                ajax: true
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('<div class="alert alert-success mt-3">' +
                      '<i class="fas fa-check-circle me-2"></i>' + response.message +
                      '</div>').insertBefore('.alert-info').delay(3000).fadeOut(500);
                    
                    // Update the button with the recorded time
                    const badge = '<span class="badge bg-success">Recorded at ' + response.time + '</span>';
                    
                    switch(response.field) {
                        case 'check_in_morning':
                            $('#morning-check-in-container').html(badge);
                            // Enable check-out button if it exists
                            if ($('#morning-check-out-container button').length) {
                                $('#morning-check-out-container button').prop('disabled', false);
                            }
                            break;
                        case 'check_out_morning':
                            $('#morning-check-out-container').html(badge);
                            break;
                        case 'check_in_afternoon':
                            $('#afternoon-check-in-container').html(badge);
                            // Enable check-out button if it exists
                            if ($('#afternoon-check-out-container button').length) {
                                $('#afternoon-check-out-container button').prop('disabled', false);
                            }
                            break;
                        case 'check_out_afternoon':
                            $('#afternoon-check-out-container').html(badge);
                            break;
                    }
                } else {
                    // Show error message
                    $('<div class="alert alert-danger mt-3">' +
                      '<i class="fas fa-exclamation-circle me-2"></i>' + (response.message || 'Error recording attendance') +
                      '</div>').insertBefore('.alert-info').delay(3000).fadeOut(500);
                }
            },
            error: function() {
                // Show error message
                $('<div class="alert alert-danger mt-3">' +
                  '<i class="fas fa-exclamation-circle me-2"></i>Error connecting to server' +
                  '</div>').insertBefore('.alert-info').delay(3000).fadeOut(500);
            }
        });
    });
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?> 