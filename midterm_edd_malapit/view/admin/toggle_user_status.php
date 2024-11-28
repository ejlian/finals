<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/UserManager.php');
require_once(__DIR__ . '/../../classes/ActivityLogger.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$userManager = new UserManager($connection);
$activityLogger = new ActivityLogger($connection);

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id']) && isset($data['status'])) {
    $success = $userManager->toggleUserStatus($data['user_id'], $data['status']);
    if ($success) {
        $activityLogger->log(
            $_SESSION['admin_id'], 
            'Update User Status', 
            "Changed user #{$data['user_id']} status to {$data['status']}"
        );
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
} 