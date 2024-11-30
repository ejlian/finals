<?php
class ContentManager {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    public function addItem($name, $description, $price, $brand, $size, $stock, $image) {
        $stmt = $this->db->prepare("
            INSERT INTO shoes (name, description, price, brand, size, stock, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $description, $price, $brand, $size, $stock, $image]);
    }
    
    public function getAllItems() {
        $stmt = $this->db->prepare("SELECT * FROM shoes ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteItem($id) {
        try {
            // First, get the image filename to delete it from resources
            $stmt = $this->db->prepare("SELECT image FROM shoes WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            
            if ($item && $item['image']) {
                $imagePath = __DIR__ . '/../resources/' . $item['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Then delete the record from database
            $stmt = $this->db->prepare("DELETE FROM shoes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function updateItem($id, $data) {
        $sql = "UPDATE shoes SET ";
        $params = [];
        
        foreach ($data as $key => $value) {
            $sql .= "$key = ?, ";
            $params[] = $value;
        }
        
        $sql = rtrim($sql, ", ") . " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function getItem($id) {
        $stmt = $this->db->prepare("SELECT * FROM shoes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Add other methods as needed
} 