<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/ProductManager.php');
require_once(__DIR__ . '/../../classes/ActivityLogger.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$response = ['success' => false];

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['product_id'])) {
    $productManager = new ProductManager($connection);
    $activityLogger = new ActivityLogger($connection);
    
    if ($productManager->deleteProduct($data['product_id'])) {
        $activityLogger->log($_SESSION['admin_id'], 'Delete Product', "Deleted product #{$data['product_id']}");
        $response['success'] = true;
    }
}

echo json_encode($response); 