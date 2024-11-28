<?php
class ProductManager {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function getAllProducts() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    name,
                    price,
                    stock,
                    image,
                    description AS brand  # Using description as brand temporarily
                FROM shoes 
                ORDER BY name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllProducts: " . $e->getMessage());
            return [];
        }
    }

    public function searchProducts($query) {
        try {
            $searchTerm = "%$query%";
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    name,
                    price,
                    stock,
                    image,
                    description AS brand  # Using description as brand temporarily
                FROM shoes 
                WHERE name LIKE ? OR description LIKE ?
                ORDER BY name ASC
            ");
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in searchProducts: " . $e->getMessage());
            return [];
        }
    }

    public function handleImageUpload($file) {
        $target_dir = __DIR__ . "/../../resources/";
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $new_filename;
        }
        return false;
    }

    public function addProduct($name, $brand, $price, $stock, $image) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO shoes (name, brand, price, stock, image) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$name, $brand, $price, $stock, $image]);
        } catch (PDOException $e) {
            error_log("Error in addProduct: " . $e->getMessage());
            return false;
        }
    }

    public function deleteProduct($id) {
        try {
            // First get the image filename
            $stmt = $this->db->prepare("SELECT image FROM shoes WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete the product
            $stmt = $this->db->prepare("DELETE FROM shoes WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result && $product && $product['image']) {
                // Delete the image file
                $image_path = __DIR__ . "/../../resources/" . $product['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in deleteProduct: " . $e->getMessage());
            return false;
        }
    }

    public function getProductById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM shoes 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getProductById: " . $e->getMessage());
            return null;
        }
    }

    public function updateProduct($id, $name, $description, $price, $stock) {
        try {
            $stmt = $this->db->prepare("
                UPDATE shoes 
                SET name = ?, 
                    description = ?, 
                    price = ?, 
                    stock = ?
                WHERE id = ?
            ");
            return $stmt->execute([$name, $description, $price, $stock, $id]);
        } catch (PDOException $e) {
            error_log("Error in updateProduct: " . $e->getMessage());
            return false;
        }
    }
} 