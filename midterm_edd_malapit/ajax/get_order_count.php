
<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $stmt = $connection->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $orderCount = $result['total'] ?? 0;

    echo json_encode(['success' => true, 'orderCount' => $orderCount]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 