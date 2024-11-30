<?php
class OrderManager {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    public function getAllOrders($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT o.id, o.user_id, u.username, o.total_amount, o.order_status, o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getOrdersByStatus($status, $limit, $offset) {
        try {
            $query = "
                SELECT 
                    o.id,
                    o.total_amount,
                    o.created_at,
                    o.order_status,
                    c.first_name,
                    c.last_name,
                    GROUP_CONCAT(s.name SEPARATOR ', ') as items
                FROM orders o
                JOIN customers c ON o.user_id = c.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN shoes s ON oi.shoe_id = s.id
                WHERE o.order_status = :status
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    public function updateOrderStatus($orderId, $newStatus) {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET order_status = :status
            WHERE id = :id
        ");
        $stmt->bindValue(':status', $newStatus, PDO::PARAM_STR);
        $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getOrderDetails($orderId) {
        $stmt = $this->db->prepare("
            SELECT o.id, o.user_id, u.username, o.total_amount, o.order_status, o.created_at,
                   oi.product_id, p.name as product_name, oi.quantity, p.price
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            WHERE o.id = :id
        ");
        $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getOrderStatusCounts() {
        try {
            $stmt = $this->db->prepare("
                SELECT order_status, COUNT(*) as count
                FROM orders
                GROUP BY order_status
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $statusCounts = [];
            foreach ($results as $row) {
                $statusCounts[$row['order_status']] = $row['count'];
            }
            return $statusCounts;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    public function placeOrder($userId, $cartItems, $deliveryMethod, $paymentMethod, $customerInfo) {
        try {
            $this->db->beginTransaction();
            
            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    user_id, 
                    shipping_address,
                    phone,
                    payment_method,
                    delivery_method,
                    total_amount,
                    delivery_fee
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $totalAmount = $this->calculateTotal($cartItems);
            $deliveryFee = $this->calculateDeliveryFee($deliveryMethod);
            
            $stmt->execute([
                $userId,
                $customerInfo['address'],
                $customerInfo['phone'],
                $paymentMethod,
                $deliveryMethod,
                $totalAmount,
                $deliveryFee
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // Insert order items
            $this->insertOrderItems($orderId, $cartItems);
            
            // Update stock
            $this->updateStock($cartItems);
            
            // Clear cart
            $this->clearCart($userId);
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Order placement error: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function calculateTotal($cartItems) {
        return array_reduce($cartItems, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
    }
    
    private function calculateDeliveryFee($method) {
        return match($method) {
            'express' => 15.00,
            'bike' => 5.00,
            default => 5.00
        };
    }
    
    private function insertOrderItems($orderId, $cartItems) {
        $stmt = $this->db->prepare("
            INSERT INTO order_items (
                order_id,
                shoe_id,
                quantity,
                price
            ) VALUES (?, ?, ?, ?)
        ");
        
        foreach ($cartItems as $item) {
            $stmt->execute([
                $orderId,
                $item['shoe_id'],
                $item['quantity'],
                $item['price']
            ]);
        }
    }
    
    private function updateStock($cartItems) {
        $stmt = $this->db->prepare("
            UPDATE shoes 
            SET stock = stock - ? 
            WHERE id = ?
        ");
        
        foreach ($cartItems as $item) {
            $stmt->execute([
                $item['quantity'],
                $item['shoe_id']
            ]);
        }
    }
    
    private function clearCart($userId) {
        $stmt = $this->db->prepare("
            DELETE FROM cart_items 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
    }
} 