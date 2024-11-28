<?php
class UserManager {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function getAllUsers() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.created_at,
                    COUNT(o.id) as total_orders
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateUser($id, $username, $email) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET username = ?, email = ?
                WHERE id = ?
            ");
            return $stmt->execute([$username, $email, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteUser($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function searchUsers($query) {
        try {
            $searchTerm = "%$query%";
            $stmt = $this->db->prepare("
                SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.created_at,
                    COUNT(o.id) as total_orders
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE u.username LIKE ? OR u.email LIKE ?
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ");
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUserOrderHistory($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    o.id,
                    o.created_at,
                    o.order_status,
                    SUM(oi.quantity * p.price) as total_amount,
                    COUNT(oi.id) as total_items
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                WHERE o.user_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function toggleUserStatus($userId, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET status = ?
                WHERE id = ?
            ");
            return $stmt->execute([$status, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
} 