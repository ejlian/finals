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
                SELECT p.name, COUNT(oi.product_id) as total_sold
                FROM order_items oi
                JOIN shoes p ON oi.product_id = p.id
                GROUP BY p.id, p.name
                ORDER BY total_sold DESC
                LIMIT 5
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