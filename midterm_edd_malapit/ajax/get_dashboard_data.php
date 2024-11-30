<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');

header('Content-Type: application/json');

try {
    $stats = [
        'totalOrders' => $connection->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'totalRevenue' => $connection->query("SELECT SUM(total_amount) FROM orders")->fetchColumn(),
        'totalUsers' => $connection->query("SELECT COUNT(*) FROM customers")->fetchColumn()
    ];
    
    echo json_encode($stats);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to fetch dashboard data']);
}