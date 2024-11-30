<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    try {
        $stmt = $connection->prepare("
            UPDATE orders 
            SET order_status = ?, 
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        
        // Log the status change
        $logStmt = $connection->prepare("
            INSERT INTO admin_activity_logs (admin_id, action, details)
            VALUES (?, 'update_order_status', ?)
        ");
        
        $details = "Updated Order #{$_POST['order_id']} status to {$_POST['status']}";
        $logStmt->execute([$_SESSION['admin_id'], $details]);
        
        $_SESSION['success_message'] = "Order status updated successfully";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "Failed to update order status";
    }
}

header("Location: admin_orders.php");
exit();
?> 