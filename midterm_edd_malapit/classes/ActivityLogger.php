<?php
class ActivityLogger {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function log($adminId, $action, $details = '') {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            $stmt = $this->db->prepare("
                INSERT INTO admin_activity_logs (admin_id, action, details, ip_address)
                VALUES (?, ?, ?, ?)
            ");
            $result = $stmt->execute([$adminId, $action, $details, $ipAddress]);
            
            if (!$result) {
                error_log("Failed to log activity: " . print_r($stmt->errorInfo(), true));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    public function getRecentLogs($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT aal.*, a.username 
            FROM admin_activity_logs aal
            JOIN admins a ON aal.admin_id = a.id
            ORDER BY aal.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}