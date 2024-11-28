<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['shoe_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $shoe_id = $_POST['shoe_id'];
    $action = $_POST['action'];
    
    if ($action === 'increase') {
        $stmt = $connection->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND shoe_id = ?");
    } else {
        $stmt = $connection->prepare("
            UPDATE cart_items 
            SET quantity = GREATEST(quantity - 1, 0) 
            WHERE user_id = ? AND shoe_id = ?");
   }
    
    $stmt->execute([$_SESSION['user_id'], $shoe_id]);
    
    $stmt = $connection->prepare("DELETE FROM cart_items WHERE user_id = ? AND shoe_id = ? AND quantity <= 0");
    $stmt->execute([$_SESSION['user_id'], $shoe_id]);
    
    $stmt = $connection->prepare("
        SELECT SUM(ci.quantity * s.price) as total, SUM(ci.quantity) as count
        FROM cart_items ci 
        JOIN shoes s ON ci.shoe_id = s.id 
        WHERE ci.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'total' => number_format($result['total'] ?? 0, 2),
        'cartCount' => $result['count'] ?? 0
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 