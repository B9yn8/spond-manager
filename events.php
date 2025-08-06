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
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query conditions
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($date_from) {
    $where_conditions[] = "event_date >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "event_date <= ?";
    $params[] = $date_to;
}

$where_clause = empty($where_conditions) ? "" : "WHERE " . implode(" AND ", $where_conditions);

// Get events
$stmt = $pdo->prepare("
    SELECT e.*, 
           COUNT(a.id) as total_responses,
           COUNT(CASE WHEN a.status = 'accepted' THEN 1 END) as accepted_count,
           COUNT(CASE WHEN a.status = 'declined' THEN 1 END) as declined_count,
           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count
    FROM events e
    LEFT JOIN attendance a ON e.id = a.event_id
    $where_clause
    GROUP BY e.id
    ORDER BY e.event_date DESC, e.event_time DESC
");
$stmt->execute($params);
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Spond Manager</title>
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
                        <i class="fas fa-calendar-alt"></i> Events Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                                <i class="fas fa-plus"></i> Create Event
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="syncSpondEvents()">
                                <i class="fas fa-sync-alt"></i> Sync Events
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-filter"></i> Filter Events</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="upcoming" <?php echo $status_filter == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="ongoing" <?php echo $status_filter == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3">
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

                <!-- Events Grid -->
                <div class="row">
                    <?php foreach ($events as $event): ?>
                    <div class="col-xl-4 col-lg-6 mb-4">
                        <div class="card h-100 shadow event-<?php echo $event['status']; ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold">
                                    <?php echo htmlspecialchars($event['name']); ?>
                                </h6>
                                <span class="badge bg-<?php echo getStatusColor($event['status']); ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-calendar text-primary me-2"></i>
                                            <small><?php echo date('M j, Y', strtotime($event['event_date'])); ?></small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-primary me-2"></i>
                                            <small><?php echo date('g:i A', strtotime($event['event_time'])); ?></small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                            <small><?php echo htmlspecialchars($event['location']); ?></small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-users text-primary me-2"></i>
                                            <small><?php echo $event['total_responses']; ?> responses</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Stats -->
                                <div class="row mb-3">
                                    <div class="col-3 text-center">
                                        <div class="badge bg-success mb-1"><?php echo $event['accepted_count']; ?></div>
                                        <div class="small text-muted">Accepted</div>
                                    </div>
                                    <div class="col-3 text-center">
                                        <div class="badge bg-danger mb-1"><?php echo $event['declined_count']; ?></div>
                                        <div class="small text-muted">Declined</div>
                                    </div>
                                    <div class="col-3 text-center">
                                        <div class="badge bg-primary mb-1"><?php echo $event['present_count']; ?></div>
                                        <div class="small text-muted">Present</div>
                                    </div>
                                    <div class="col-3 text-center">
                                        <div class="badge bg-secondary mb-1"><?php echo $event['total_responses']; ?></div>
                                        <div class="small text-muted">Total</div>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="progress mb-3" style="height: 8px;">
                                    <?php 
                                    $acceptance_rate = $event['total_responses'] > 0 ? 
                                        ($event['accepted_count'] / $event['total_responses']) * 100 : 0;
                                    ?>
                                    <div class="progress-bar bg-success" 
                                         style="width: <?php echo $acceptance_rate; ?>%"
                                         title="<?php echo round($acceptance_rate, 1); ?>% acceptance rate"></div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <a href="attendance.php?event_id=<?php echo $event['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-users"></i> Manage Attendance
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit Event
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="duplicateEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-copy"></i> Duplicate
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($events)): ?>
                    <div class="col-12">
                        <div class="card text-center">
                            <div class="card-body py-5">
                                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Events Found</h4>
                                <p class="text-muted">No events match your current filter criteria.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                                    <i class="fas fa-plus"></i> Create Your First Event
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Watermark -->
                <div class="watermark">
                    Created By <strong>Belli Dev</strong>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="create_event.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event_name" class="form-label">Event Name *</label>
                                    <input type="text" class="form-control" id="event_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event_location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="event_location" name="location">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="event_description" class="form-label">Description</label>
                            <textarea class="form-control" id="event_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event_date" class="form-label">Event Date *</label>
                                    <input type="date" class="form-control" id="event_date" name="event_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event_time" class="form-label">Event Time *</label>
                                    <input type="time" class="form-control" id="event_time" name="event_time" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="event_status" class="form-label">Status</label>
                            <select class="form-select" id="event_status" name="status">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/events.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'upcoming': return 'primary';
        case 'ongoing': return 'warning';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>