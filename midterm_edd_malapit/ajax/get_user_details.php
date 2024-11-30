<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');
require_once(__DIR__ . '/../classes/AdminAuth.php');

header('Content-Type: application/json');

$adminAuth = new AdminAuth($connection);
if (!$adminAuth->isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    // Get user details
    $stmt = $connection->prepare("
        SELECT * FROM customers WHERE id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Get user's orders
    $stmt = $connection->prepare("
        SELECT 
            o.*,
            GROUP_CONCAT(s.name SEPARATOR ', ') as items
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN shoes s ON oi.shoe_id = s.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$_GET['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'user' => $user,
        'orders' => $orders
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Server error']);
}