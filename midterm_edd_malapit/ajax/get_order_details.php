<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

try {
    
    $stmt = $connection->prepare("
        SELECT o.*, 
               oi.quantity, oi.price as item_price,
               s.name as item_name, s.image as item_image
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN shoes s ON oi.shoe_id = s.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

   
    $order = [
        'id' => $items[0]['id'],
        'status' => $items[0]['status'],
        'created_at' => $items[0]['created_at'],
        'shipping_address' => $items[0]['shipping_address'],
        'phone' => $items[0]['phone'],
        'payment_method' => $items[0]['payment_method'],
        'delivery_method' => $items[0]['delivery_method'],
        'delivery_fee' => floatval($items[0]['delivery_fee']),
        'total_amount' => floatval($items[0]['total_amount']),
        'estimated_delivery' => $items[0]['estimated_delivery'],
        'items' => array_map(function($item) {
            return [
                'name' => $item['item_name'],
                'image' => $item['item_image'],
                'quantity' => intval($item['quantity']),
                'price' => floatval($item['item_price'])
            ];
        }, $items)
    ];

    echo json_encode(['success' => true, 'order' => $order]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}