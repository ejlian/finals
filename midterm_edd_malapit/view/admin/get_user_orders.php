<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/UserManager.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$userManager = new UserManager($connection);

if (isset($_GET['user_id'])) {
    $orders = $userManager->getUserOrderHistory($_GET['user_id']);
    header('Content-Type: application/json');
    echo json_encode($orders);
} 