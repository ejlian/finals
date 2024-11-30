<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            throw new Exception("Invalid JSON data");
        }

        $connection->beginTransaction();

        $address = sprintf("%s, Brgy. %s, %s, %s %s",
            $data['address']['street'],
            $data['address']['barangay'],
            $data['address']['city'],
            $data['address']['province'],
            $data['address']['postal_code']
        );
        
        $payment_method = $data['payment_method'];
        $phone = $data['phone'];
        $delivery_method = $data['delivery_method'] ?? 'standard';

        if (empty($payment_method) || empty($address) || empty($phone)) {
            throw new Exception("Missing required fields");
        }

        $stmt = $connection->prepare("
            SELECT ci.*, s.price, s.name 
            FROM cart_items ci 
            JOIN shoes s ON ci.shoe_id = s.id 
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            throw new Exception("Cart is empty");
        }

        $total_amount = array_reduce($cart_items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        $deliveryMode = match($delivery_method) {
            'express' => new ExpressDelivery($order_id),
            'bike' => new BikeDelivery($order_id),
            default => new StandardDelivery($order_id)
        };

        $delivery_fee = $deliveryMode->calculateDeliveryFee();
        $estimated_time = $deliveryMode->getEstimatedTime();

        $total_with_delivery = $total_amount + $delivery_fee;

        $stmt = $connection->prepare("
            INSERT INTO orders (
                user_id, 
                payment_method, 
                shipping_address, 
                phone, 
                status, 
                total_amount,
                delivery_fee,
                estimated_delivery,
                delivery_method,
                created_at
            ) 
            VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, NOW()) ");
        
        $stmt->execute([
            $user_id,
            $payment_method,
            $address,
            $phone,
            $total_with_delivery,
            $delivery_fee,
            $estimated_time,
            $delivery_method
        ]);
        
        $order_id = $connection->lastInsertId();

        $stmt = $connection->prepare("
            INSERT INTO order_items (order_id, shoe_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['shoe_id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        $paymentMethod = match($payment_method) {
            'credit_card' => new CreditCardPayment(
                $total_with_delivery,
                $order_id,
                $connection,
                $data['card_number'] ?? '',
                $data['card_expiry'] ?? '',
                $data['card_cvv'] ?? ''
            ),
            default => new CashOnDeliveryPayment(
                $total_with_delivery,
                $order_id,
                $connection
            )
        };

        try {
            if (!$paymentMethod->processPayment()) {
                throw new Exception("Payment processing failed");
            }
            
            $connection->commit();
            
            $stmt = $connection->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'message' => 'Order placed successfully'
            ]);
        } catch (Exception $e) {
            $connection->rollBack();
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

    } catch (Exception $e) {
        $connection->rollBack();
        error_log("Order Error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid request method'
    ]);
}