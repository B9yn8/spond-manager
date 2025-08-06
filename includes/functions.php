<?php
/*
 * 🔒 Spond Manager - Created by Belli Dev
 * © 2025 Belli Dev. All rights reserved.
 * You are not allowed to copy, modify, redistribute, or sell this software
 * without explicit written permission from the author.
 * Violators will be prosecuted under applicable laws.
 */



// includes/functions.php

/**
 * User Authentication Functions
 */
function authenticateUser($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function saveUserSpondCredentials($pdo, $user_id, $spond_username, $spond_password) {
    // Encrypt Spond credentials (basic encryption - use proper encryption in production)
    $encrypted_password = base64_encode($spond_password);
    
    $stmt = $pdo->prepare("UPDATE users SET spond_username = ?, spond_password = ? WHERE id = ?");
    return $stmt->execute([$spond_username, $encrypted_password, $user_id]);
}

function getUserSpondCredentials($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT spond_username, spond_password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $creds = $stmt->fetch();
    
    if ($creds && $creds['spond_password']) {
        $creds['spond_password'] = base64_decode($creds['spond_password']);
    }
    
    return $creds;
}

/**
 * Spond API Integration Functions
 */
function syncSpondEvents($pdo, $user_id) {
    $credentials = getUserSpondCredentials($pdo, $user_id);
    if (!$credentials || !$credentials['spond_username']) {
        return ['success' => false, 'message' => 'Spond credentials not found'];
    }
    
    // Simulate Spond API call (replace with actual Python/Spond integration)
    $mock_events = [
        [
            'id' => 'spond_001',
            'name' => 'Weekly Training',
            'description' => 'Regular training session',
            'start_time' => '2024-08-10 18:00:00',
            'end_time' => '2024-08-10 20:00:00',
            'location' => 'Sports Center',
            'responses' => [
                ['member_id' => 'mem_001', 'name' => 'John Doe', 'status' => 'accepted'],
                ['member_id' => 'mem_002', 'name' => 'Jane Smith', 'status' => 'declined']
            ]
        ],
        [
            'id' => 'spond_002',
            'name' => 'Tournament Match',
            'description' => 'Championship game',
            'start_time' => '2024-08-12 15:00:00',
            'end_time' => '2024-08-12 17:00:00',
            'location' => 'Stadium',
            'responses' => [
                ['member_id' => 'mem_001', 'name' => 'John Doe', 'status' => 'accepted'],
                ['member_id' => 'mem_003', 'name' => 'Mike Johnson', 'status' => 'unanswered']
            ]
        ]
    ];
    
    $synced_events = 0;
    $synced_members = 0;
    $synced_attendance = 0;
    
    foreach ($mock_events as $event_data) {
        // Insert/update event
        $event_date = date('Y-m-d', strtotime($event_data['start_time']));
        $event_time = date('H:i:s', strtotime($event_data['start_time']));
        
        $stmt = $pdo->prepare("
            INSERT INTO events (spond_event_id, name, description, event_date, event_time, location)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            description = VALUES(description),
            event_date = VALUES(event_date),
            event_time = VALUES(event_time),
            location = VALUES(location),
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            $event_data['id'],
            $event_data['name'],
            $event_data['description'],
            $event_date,
            $event_time,
            $event_data['location']
        ]);
        $synced_events++;
        
        // Get event ID
        $event_id = getEventIdBySpondId($pdo, $event_data['id']);
        
        // Process responses
        foreach ($event_data['responses'] as $response) {
            // Insert/update member
            $names = explode(' ', $response['name'], 2);
            $first_name = $names[0];
            $last_name = isset($names[1]) ? $names[1] : '';
            
            $stmt = $pdo->prepare("
                INSERT INTO members (spond_member_id, first_name, last_name)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                first_name = VALUES(first_name),
                last_name = VALUES(last_name),
                updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$response['member_id'], $first_name, $last_name]);
            $synced_members++;
            
            // Get member ID
            $member_id = getMemberIdBySpondId($pdo, $response['member_id']);
            
            // Insert/update attendance
            $stmt = $pdo->prepare("
                INSERT INTO attendance (event_id, member_id, status, response_time)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                response_time = VALUES(response_time),
                updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$event_id, $member_id, $response['status']]);
            $synced_attendance++;
        }
    }
    
    // Log sync
    $stmt = $pdo->prepare("
        INSERT INTO sync_logs (user_id, sync_type, status, message)
        VALUES (?, 'full', 'success', ?)
    ");
    $message = "Synced: $synced_events events, $synced_members members, $synced_attendance attendance records";
    $stmt->execute([$user_id, $message]);
    
    return [
        'success' => true,
        'message' => $message,
        'stats' => [
            'events' => $synced_events,
            'members' => $synced_members,
            'attendance' => $synced_attendance
        ]
    ];
}

/**
 * Database Helper Functions
 */
function getEventIdBySpondId($pdo, $spond_id) {
    $stmt = $pdo->prepare("SELECT id FROM events WHERE spond_event_id = ?");
    $stmt->execute([$spond_id]);
    $result = $stmt->fetch();
    return $result ? $result['id'] : null;
}

function getMemberIdBySpondId($pdo, $spond_id) {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE spond_member_id = ?");
    $stmt->execute([$spond_id]);
    $result = $stmt->fetch();
    return $result ? $result['id'] : null;
}

function getRecentEvents($pdo, $limit = 10) {
    $stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date DESC, event_time DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getTotalEvents($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
    $result = $stmt->fetch();
    return $result['count'];
}

function getTotalAttendees($pdo) {
    $stmt = $pdo->query("SELECT COUNT(DISTINCT member_id) as count FROM attendance");
    $result = $stmt->fetch();
    return $result['count'];
}

function getEventsThisMonth($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE MONTH(event_date) = MONTH(CURRENT_DATE()) AND YEAR(event_date) = YEAR(CURRENT_DATE())");
    $result = $stmt->fetch();
    return $result['count'];
}

function getPendingAttendances($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE status = 'unanswered'");
    $result = $stmt->fetch();
    return $result['count'];
}

function getEventAttendeeCount($pdo, $event_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $result = $stmt->fetch();
    return $result['count'];
}

/**
 * Attendance Management Functions
 */
function getEventAttendance($pdo, $event_id) {
    $stmt = $pdo->prepare("
        SELECT a.*, m.first_name, m.last_name, m.email, e.name as event_name, e.event_date, e.event_time
        FROM attendance a
        JOIN members m ON a.member_id = m.id
        JOIN events e ON a.event_id = e.id
        WHERE a.event_id = ?
        ORDER BY m.last_name, m.first_name
    ");
    $stmt->execute([$event_id]);
    return $stmt->fetchAll();
}

function updateAttendanceStatus($pdo, $attendance_id, $status, $notes = null) {
    $stmt = $pdo->prepare("
        UPDATE attendance 
        SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    return $stmt->execute([$status, $notes, $attendance_id]);
}

function checkInMember($pdo, $attendance_id) {
    $stmt = $pdo->prepare("
        UPDATE attendance 
        SET checked_in = TRUE, check_in_time = CURRENT_TIMESTAMP, status = 'present'
        WHERE id = ?
    ");
    return $stmt->execute([$attendance_id]);
}

function getAttendanceOverview($pdo, $member_filter = null, $date_from = null, $date_to = null) {
    $where_conditions = [];
    $params = [];
    
    if ($member_filter) {
        $where_conditions[] = "(m.first_name LIKE ? OR m.last_name LIKE ?)";
        $params[] = "%$member_filter%";
        $params[] = "%$member_filter%";
    }
    
    if ($date_from) {
        $where_conditions[] = "e.event_date >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $where_conditions[] = "e.event_date <= ?";
        $params[] = $date_to;
    }
    
    $where_clause = empty($where_conditions) ? "" : "WHERE " . implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            m.id as member_id,
            CONCAT(m.first_name, ' ', m.last_name) as member_name,
            COUNT(a.id) as total_events,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN a.status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
            SUM(CASE WHEN a.status = 'declined' THEN 1 ELSE 0 END) as declined_count,
            SUM(CASE WHEN a.status = 'unanswered' THEN 1 ELSE 0 END) as unanswered_count,
            ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_rate
        FROM members m
        LEFT JOIN attendance a ON m.id = a.member_id
        LEFT JOIN events e ON a.event_id = e.id
        $where_clause
        GROUP BY m.id, m.first_name, m.last_name
        ORDER BY m.last_name, m.first_name
    ");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function generateMonthlyReport($pdo, $year, $month) {
    $stmt = $pdo->prepare("
        SELECT 
            e.name as event_name,
            e.event_date,
            e.event_time,
            e.location,
            CONCAT(m.first_name, ' ', m.last_name) as member_name,
            m.email,
            m.phone,
            a.status,
            a.checked_in,
            a.check_in_time,
            a.notes
        FROM events e
        LEFT JOIN attendance a ON e.id = a.event_id
        LEFT JOIN members m ON a.member_id = m.id
        WHERE YEAR(e.event_date) = ? AND MONTH(e.event_date) = ?
        ORDER BY e.event_date, e.event_time, m.last_name, m.first_name
    ");
    $stmt->execute([$year, $month]);
    return $stmt->fetchAll();
}

/**
 * Excel Export Functions
 */
function exportToExcel($data, $filename) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Simple CSV export (can be enhanced with proper Excel library)
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Write headers
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

/**
 * Manual Entry Functions
 */
function addManualAttendanceEntry($pdo, $event_id, $member_id, $status, $notes = null) {
    $stmt = $pdo->prepare("
        INSERT INTO attendance (event_id, member_id, status, notes, response_time)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        status = VALUES(status),
        notes = VALUES(notes),
        updated_at = CURRENT_TIMESTAMP
    ");
    return $stmt->execute([$event_id, $member_id, $status, $notes]);
}

function getAllMembers($pdo) {
    $stmt = $pdo->query("SELECT * FROM members ORDER BY last_name, first_name");
    return $stmt->fetchAll();
}

function getAllEvents($pdo, $limit = null) {
    $sql = "SELECT * FROM events ORDER BY event_date DESC, event_time DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getEventById($pdo, $event_id) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    return $stmt->fetch();
}

function getMemberById($pdo, $member_id) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    return $stmt->fetch();
}

/**
 * Utility Functions
 */
function formatStatus($status) {
    $statuses = [
        'accepted' => ['class' => 'success', 'icon' => 'check', 'text' => 'Accepted'],
        'declined' => ['class' => 'danger', 'icon' => 'times', 'text' => 'Declined'],
        'unanswered' => ['class' => 'warning', 'icon' => 'question', 'text' => 'Unanswered'],
        'present' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Present'],
        'absent' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Absent']
    ];
    
    return $statuses[$status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => ucfirst($status)];
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function logActivity($pdo, $user_id, $action, $details = null) {
    $stmt = $pdo->prepare("
        INSERT INTO sync_logs (user_id, sync_type, status, message, created_at)
        VALUES (?, 'activity', 'success', ?, NOW())
    ");
    $message = $action . ($details ? ": $details" : "");
    return $stmt->execute([$user_id, $message]);
}

/**
 * Settings Functions
 */
function getUserSetting($pdo, $user_id, $key, $default = null) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE user_id = ? AND setting_key = ?");
    $stmt->execute([$user_id, $key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function setUserSetting($pdo, $user_id, $key, $value) {
    $stmt = $pdo->prepare("
        INSERT INTO settings (user_id, setting_key, setting_value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    return $stmt->execute([$user_id, $key, $value]);
}

/**
 * API Response Helper
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>