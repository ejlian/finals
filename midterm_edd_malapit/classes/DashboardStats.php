<?php
class DashboardStats {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTotalOrders() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in getTotalOrders: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalUsers() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in getTotalUsers: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalRevenue() {
        try {
            $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in getTotalRevenue: " . $e->getMessage());
            return 0;
        }
    }

    public function getSalesDataLastWeek() {
        try {
            $stmt = $this->db->query("
                SELECT DATE(created_at) as date, 
                       COALESCE(SUM(total_amount), 0) as total
                FROM orders
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getSalesDataLastWeek: " . $e->getMessage());
            return [];
        }
    }

    public function getTopSellingProducts() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    s.id,
                    s.name,
                    s.image,
                    s.price,
                    COUNT(oi.shoe_id) as total_sold,
                    SUM(oi.quantity) as units_sold,
                    SUM(oi.quantity * oi.price) as total_revenue
                FROM shoes s
                LEFT JOIN order_items oi ON s.id = oi.shoe_id
                LEFT JOIN orders o ON oi.order_id = o.id
                GROUP BY s.id, s.name, s.image, s.price
                ORDER BY units_sold DESC, total_revenue DESC
                LIMIT 3
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getTopSellingProducts: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentOrders() {
        try {
            $stmt = $this->db->query("
                SELECT o.id, u.username, o.total_amount, o.created_at
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getRecentOrders: " . $e->getMessage());
            return [];
        }
    }
} 