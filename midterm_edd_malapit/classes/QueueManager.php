<?php
class QueueManager {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    public function getStorePickupQueue() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    o.id,
                    o.created_at,
                    o.total_amount,
                    o.order_status,
                    o.payment_status,
                    u.username,
                    u.phone,
                    pm.name as payment_method,
                    COUNT(oi.id) as item_count
                FROM orders o
                JOIN users u ON o.user_id = u.id
                JOIN payment_methods pm ON o.payment_method_id = pm.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.delivery_method = 'store_pickup'
                AND o.order_status IN ('Processing', 'Ready')
                GROUP BY o.id
                ORDER BY 
                    CASE o.order_status
                        WHEN 'Ready' THEN 1
                        WHEN 'Processing' THEN 2
                        ELSE 3
                    END,
                    o.created_at ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    public function getQueueDetails($orderId) {
        try {
            // Get order items
            $stmt = $this->db->prepare("
                SELECT 
                    oi.*,
                    p.name as product_name,
                    p.sku,
                    s.size
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN shoes s ON p.shoe_id = s.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    public function updateQueueStatus($orderId, $status, $notes = '') {
        try {
            $this->db->beginTransaction();
            
            // Update order status
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET order_status = ?,
                    updated_at = CURRENT_TIMESTAMP,
                    notes = CONCAT(COALESCE(notes, ''), ?\n)
                WHERE id = ?
            ");
            
            $timestamp = date('Y-m-d H:i:s');
            $noteEntry = $notes ? "\n[$timestamp] $notes" : '';
            
            $success = $stmt->execute([$status, $noteEntry, $orderId]);
            
            if ($success) {
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
} 