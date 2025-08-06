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

// Handle filters
$member_filter = $_GET['member'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Get attendance overview
$attendance_overview = getAttendanceOverview($pdo, $member_filter, $date_from, $date_to);

// Handle export
if (isset($_GET['export'])) {
    $year = $_GET['year'] ?? date('Y');
    $month = $_GET['month'] ?? date('n');
    
    $report_data = generateMonthlyReport($pdo, $year, $month);
    $filename = "attendance_report_{$year}_{$month}.csv";
    
    exportToExcel($report_data, $filename);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Overview - Spond Manager</title>
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
                        <i class="fas fa-chart-bar"></i> Attendance Overview
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="fas fa-download"></i> Export Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-filter"></i> Filters</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="member" class="form-label">Member Name</label>
                                <input type="text" class="form-control" id="member" name="member" 
                                       value="<?php echo htmlspecialchars($member_filter); ?>" 
                                       placeholder="Search by name...">
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Overview Statistics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Members</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($attendance_overview); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Avg Attendance</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $avg_rate = array_reduce($attendance_overview, function($sum, $member) { 
                                                return $sum + $member['attendance_rate']; 
                                            }, 0) / max(count($attendance_overview), 1);
                                            echo round($avg_rate, 1) . '%'; 
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Events</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $total_events = array_reduce($attendance_overview, function($sum, $member) { 
                                                return max($sum, $member['total_events']); 
                                            }, 0);
                                            echo $total_events; 
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">High Attendance</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $high_attendance = array_filter($attendance_overview, function($member) { 
                                                return $member['attendance_rate'] >= 80; 
                                            });
                                            echo count($high_attendance); 
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-trophy fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Overview Table -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Member Attendance Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="overviewTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Member</th>
                                        <th>Total Events</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Accepted</th>
                                        <th>Declined</th>
                                        <th>Unanswered</th>
                                        <th>Attendance Rate</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendance_overview as $member): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <?php 
                                                    $names = explode(' ', $member['member_name']);
                                                    echo strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : '')); 
                                                    ?>
                                                </div>
                                                <strong><?php echo htmlspecialchars($member['member_name']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $member['total_events']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $member['present_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger"><?php echo $member['absent_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $member['accepted_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning"><?php echo $member['declined_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $member['unanswered_count']; ?></span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar 
                                                    <?php 
                                                    if ($member['attendance_rate'] >= 80) echo 'bg-success';
                                                    elseif ($member['attendance_rate'] >= 60) echo 'bg-warning';
                                                    else echo 'bg-danger';
                                                    ?>" 
                                                    role="progressbar" 
                                                    style="width: <?php echo $member['attendance_rate']; ?>%" 
                                                    aria-valuenow="<?php echo $member['attendance_rate']; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?php echo $member['attendance_rate']; ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($member['attendance_rate'] >= 90): ?>
                                                <span class="badge bg-success"><i class="fas fa-star"></i> Excellent</span>
                                            <?php elseif ($member['attendance_rate'] >= 80): ?>
                                                <span class="badge bg-primary"><i class="fas fa-thumbs-up"></i> Good</span>
                                            <?php elseif ($member['attendance_rate'] >= 60): ?>
                                                <span class="badge bg-warning"><i class="fas fa-exclamation"></i> Average</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Poor</span>
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

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-download"></i> Export Attendance Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET">
                    <div class="modal-body">
                        <input type="hidden" name="export" value="1">
                        
                        <div class="mb-3">
                            <label for="export_year" class="form-label">Year</label>
                            <select class="form-select" name="year" id="export_year" required>
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == date('Y') ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="export_month" class="form-label">Month</label>
                            <select class="form-select" name="month" id="export_month" required>
                                <?php 
                                $months = [
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                ];
                                foreach ($months as $num => $name): 
                                ?>
                                <option value="<?php echo $num; ?>" <?php echo $num == date('n') ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> The report will include all events and attendance records for the selected month.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Sort table functionality
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('overviewTable');
            const headers = table.querySelectorAll('th');
            
            headers.forEach((header, index) => {
                if (index > 0 && index < headers.length - 1) { // Skip first and last column
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', () => sortTable(index));
                }
            });
        });
        
        function sortTable(column) {
            const table = document.getElementById('overviewTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            const sortedRows = rows.sort((a, b) => {
                const aVal = a.cells[column].textContent.trim();
                const bVal = b.cells[column].textContent.trim();
                
                // Check if values are numbers
                const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
                const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return bNum - aNum; // Descending for numbers
                }
                
                return aVal.localeCompare(bVal); // Ascending for text
            });
            
            // Clear and repopulate tbody
            tbody.innerHTML = '';
            sortedRows.forEach(row => tbody.appendChild(row));
        }
    </script>
</body>
</html>