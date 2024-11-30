<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

try {
    $connection->beginTransaction();
    
    // First, validate all items are in stock
    $stmt = $connection->prepare("
        SELECT ci.shoe_id, ci.quantity, s.stock, s.name
        FROM cart_items ci 
        JOIN shoes s ON ci.shoe_id = s.id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as $item) {
        if ($item['quantity'] > $item['stock']) {
            throw new Exception("Insufficient stock for {$item['name']}");
        }
    }
    
    // Then proceed with the order
    $debug_shoe_id = 1;
    $stmt = $connection->prepare("SELECT stock FROM shoes WHERE id = ?");
    $stmt->execute([$debug_shoe_id]);
    $initial_stock = $stmt->fetchColumn();
    error_log("Initial stock for shoe $debug_shoe_id: $initial_stock");

    $stmt = $connection->prepare("
        SELECT ci.shoe_id, ci.quantity, s.stock 
        FROM cart_items ci 
        JOIN shoes s ON ci.shoe_id = s.id 
        WHERE ci.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $newStock = $item['stock'] - $item['quantity'];
        if ($newStock < 0) {
            throw new Exception("Insufficient stock for one or more items");
        }

        $updateStmt = $connection->prepare("UPDATE shoes SET stock = ? WHERE id = ?");
        $updateStmt->execute([$newStock, $item['shoe_id']]);
    }

    $connection->commit();
    $stmt->execute([$debug_shoe_id]);
    $final_stock = $stmt->fetchColumn();
    error_log("Final stock for shoe $debug_shoe_id: $final_stock");

    echo json_encode(['success' => true]);
   } 
    catch (Exception $e) {
    $connection->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
   } 