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
    
    public function getOrdersByStatus($status, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT o.id, o.user_id, u.username, o.total_amount, o.order_status, o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.order_status = :status
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    
    public function placeOrder($userId, $cartItems, $deliveryMethod, $paymentMethod) {
        try {
            $this->db->beginTransaction();
            
            // 1. Validate stock
            $this->validateStock($cartItems);
            
            // 2. Calculate totals
            $calculator = new OrderCalculator();
            $totals = $calculator->calculateTotal($cartItems, $deliveryMethod);
            
            // 3. Create order
            $orderId = $this->createOrder($userId, $totals, $deliveryMethod, $paymentMethod);
            
            // 4. Create order items
            $this->createOrderItems($orderId, $cartItems);
            
            // 5. Update stock
            $this->updateStock($cartItems);
            
            // 6. Clear cart
            $this->clearCart($userId);
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function validateStock($cartItems) {
        foreach ($cartItems as $item) {
            $stmt = $this->db->prepare("SELECT stock FROM shoes WHERE id = ?");
            $stmt->execute([$item['shoe_id']]);
            $currentStock = $stmt->fetchColumn();
            
            if ($currentStock < $item['quantity']) {
                throw new Exception("Insufficient stock for product ID: " . $item['shoe_id']);
            }
        }
    }

    private function createOrder($userId, $totals, $deliveryMethod, $paymentMethod) {
        $stmt = $this->db->prepare("
            INSERT INTO orders (
                user_id, 
                total_amount, 
                delivery_fee,
                delivery_method,
                payment_method,
                order_status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())
        ");
        
        $stmt->execute([
            $userId,
            $totals['total'],
            $totals['delivery_fee'],
            $deliveryMethod,
            $paymentMethod
        ]);
        
        return $this->db->lastInsertId();
    }

    private function createOrderItems($orderId, $cartItems) {
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