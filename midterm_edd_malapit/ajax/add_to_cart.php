
<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['shoe_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
 
    $stmt = $connection->prepare("SELECT * FROM cart_items WHERE user_id = ? AND shoe_id = ?");
    $stmt->execute([$_SESSION['user_id'], $_POST['shoe_id']]);
    $existingItem = $stmt->fetch();

    if ($existingItem) {
        $stmt = $connection->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND shoe_id = ?");
        $stmt->execute([$_SESSION['user_id'], $_POST['shoe_id']]);
    } else {
        $stmt = $connection->prepare("INSERT INTO cart_items (user_id, shoe_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['user_id'], $_POST['shoe_id']]);
    }

    $stmt = $connection->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $cartCount = $result['total'] ?? 0;

    echo json_encode(['success' => true, 'cartCount' => $cartCount]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 