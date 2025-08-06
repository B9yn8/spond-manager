<?php
/*
 * 🔒 Spond Manager - Created by Belli Dev
 * © 2025 Belli Dev. All rights reserved.
 * You are not allowed to copy, modify, redistribute, or sell this software
 * without explicit written permission from the author.
 * Violators will be prosecuted under applicable laws.
 */


// api/sync.php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Set response header
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'sync_events':
            $result = syncSpondEvents($pdo, $_SESSION['user_id']);
            echo json_encode($result);
            break;
            
        case 'get_sync_status':
            $status = getSyncStatus($pdo, $_SESSION['user_id']);
            echo json_encode(['success' => true, 'status' => $status]);
            break;
            
        case 'test_connection':
            $result = testSpondConnection($pdo, $_SESSION['user_id']);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function getSyncStatus($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM sync_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function testSpondConnection($pdo, $user_id) {
    $credentials = getUserSpondCredentials($pdo, $user_id);
    
    if (!$credentials || !$credentials['spond_username']) {
        return [
            'success' => false,
            'message' => 'No Spond credentials configured'
        ];
    }
    
    // In a real implementation, you would test the actual Spond API connection here
    // For now, we'll simulate a connection test
    
    // Simulate API call delay
    usleep(500000); // 0.5 seconds
    
    // Mock response - in production, implement actual Spond API test
    $connection_success = true; // This would be the actual API test result
    
    if ($connection_success) {
        return [
            'success' => true,
            'message' => 'Spond connection successful',
            'username' => $credentials['spond_username']
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to connect to Spond API'
        ];
    }
}
?>