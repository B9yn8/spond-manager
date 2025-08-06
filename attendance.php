<?php
/*
 * 🔒 Spond Manager - Created by Belli Dev
 * © 2025 Belli Dev. All rights reserved.
 * You are not allowed to copy, modify, redistribute, or sell this software
 * without explicit written permission from the author.
 * Violators will be prosecuted under applicable laws.
 */


session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    header('Location: events.php');
    exit();
}

$event = getEventById($pdo, $event_id);
if (!$event) {
    header('Location: events.php');
    exit();
}

$attendance_records = getEventAttendance($pdo, $event_id);
$all_members = getAllMembers($pdo);

// Handle POST requests
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            $attendance_id = $_POST['attendance_id'];
            $status = $_POST['status'];
            $notes = $_POST['notes'] ?? null;
            updateAttendanceStatus($pdo, $attendance_id, $status, $notes);
            break;
            
        case 'check_in':
            $attendance_id = $_POST['attendance_id'];
            checkInMember($pdo, $attendance_id);
            break;
            
        case 'manual_entry':
            $member_id = $_POST['member_id'];
            $status = $_POST['status'];
            $notes = $_POST['notes'] ?? null;
            addManualAttendanceEntry($pdo, $event_id, $member_id, $status, $notes);
            break;
    }
    
    // Refresh page to show changes
    header("Location: attendance.php?event_id=$event_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - <?php echo htmlspecialchars($event['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-users"></i> Attendance Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#manualEntryModal">
                                <i class="fas fa-plus"></i> Add Manual Entry
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkCheckIn()">
                                <i class="fas fa-check-double"></i> Bulk Check-in
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Event Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Event Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4><?php echo htmlspecialchars($event['name']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($event['description']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($event['event_time'])); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?php echo $event['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo array_reduce($attendance_records, function($count, $record) { return $record['status'] == 'present' ? $count + 1 : $count; }, 0); ?></h3>
                                <p class="text-muted mb-0">Present</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-danger"><?php echo array_reduce($attendance_records, function($count, $record) { return $record['status'] == 'absent' ? $count + 1 : $count; }, 0); ?></h3>
                                <p class="text-muted mb-0">Absent</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary"><?php echo array_reduce($attendance_records, function($count, $record) { return $record['status'] == 'accepted' ? $count + 1 : $count; }, 0); ?></h3>
                                <p class="text-muted mb-0">Accepted</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning"><?php echo array_reduce($attendance_records, function($count, $record) { return $record['status'] == 'unanswered' ? $count + 1 : $count; }, 0); ?></h3>
                                <p class="text-muted mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Attendance List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th>Member</th>
                                        <th>Email</th>
                                        <th>Response Status</th>
                                        <th>Attendance Status</th>
                                        <th>Check-in Time</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendance_records as $record): ?>
                                    <?php $status_info = formatStatus($record['status']); ?>
                                    <tr data-attendance-id="<?php echo $record['id']; ?>">
                                        <td>
                                            <input type="checkbox" class="member-checkbox" value="<?php echo $record['id']; ?>">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <?php echo strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_info['class']; ?>">
                                                <i class="fas fa-<?php echo $status_info['icon']; ?>"></i>
                                                <?php echo $status_info['text']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" onchange="updateStatus(<?php echo $record['id']; ?>, this.value)">
                                                <option value="unanswered" <?php echo $record['status'] == 'unanswered' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="present" <?php echo $record['status'] == 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo $record['status'] == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                <option value="accepted" <?php echo $record['status'] == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                                <option value="declined" <?php echo $record['status'] == 'declined' ? 'selected' : ''; ?>>Declined</option>
                                            </select>
                                        </td>
                                        <td>
                                            <?php if ($record['check_in_time']): ?>
                                                <small class="text-success">
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo date('H:i', strtotime($record['check_in_time'])); ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Not checked in</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" 
                                                   value="<?php echo htmlspecialchars($record['notes']); ?>"
                                                   onchange="updateNotes(<?php echo $record['id']; ?>, this.value)"
                                                   placeholder="Add notes...">
                                        </td>
                                        <td>
                                            <?php if (!$record['checked_in']): ?>
                                            <button class="btn btn-success btn-sm" onclick="checkIn(<?php echo $record['id']; ?>)">
                                                <i class="fas fa-check"></i> Check-in
                                            </button>
                                            <?php else: ?>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Checked in</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Watermark -->
                <div class="watermark">
                    Created By <strong>Belli Dev</strong>
                </div>
            </main>
        </div>
    </div>

    <!-- Manual Entry Modal -->
    <div class="modal fade" id="manualEntryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Manual Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="manual_entry">
                        
                        <div class="mb-3">
                            <label for="member_id" class="form-label">Member</label>
                            <select class="form-select" name="member_id" required>
                                <option value="">Select member...</option>
                                <?php foreach ($all_members as $member): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="accepted">Accepted</option>
                                <option value="declined">Declined</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/attendance.js"></script>
</body>
</html>