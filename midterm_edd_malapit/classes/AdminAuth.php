<?php
require_once(__DIR__ . '/SessionManager.php');
require_once 'ActivityLogger.php';

class AdminAuth {
    private $db;
    private $activityLogger;
    
    public function __construct($connection) {
        $this->db = $connection;
        $this->activityLogger = new ActivityLogger($connection);
        SessionManager::startSession();
    }
    
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['admin_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: admin.php");
            exit();
        }
    }
    
    public function getAdminDetails() {
        if (!isset($_SESSION['admin_id'])) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAdminDetails: " . $e->getMessage());
            return null;
        }
    }
    
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            $this->activityLogger->log($_SESSION['admin_id'], 'Logout', 'Admin logged out');
        }
        session_unset();
        session_destroy();
        header("Location: admin.php");
        exit();
    }
    
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Log the successful login
                $this->activityLogger->log($admin['id'], 'Login', 'Admin logged in successfully');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAdminById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAdminById: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProfile($adminId, $username, $email) {
        try {
            // Check if username is already taken by another admin
            $stmt = $this->db->prepare("
                SELECT id FROM admins 
                WHERE username = ? AND id != ?
            ");
            $stmt->execute([$username, $adminId]);
            if ($stmt->fetch()) {
                return false; // Username already taken
            }

            // Update profile
            $stmt = $this->db->prepare("
                UPDATE admins 
                SET username = ?, email = ? 
                WHERE id = ?
            ");
            $result = $stmt->execute([$username, $email, $adminId]);
            
            if ($result) {
                $_SESSION['admin_username'] = $username;
                $this->activityLogger->log($adminId, 'Profile Update', 'Profile information updated');
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }
    
    public function changePassword($adminId, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin || !password_verify($currentPassword, $admin['password'])) {
                return false;
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $adminId]);
            
            if ($result) {
                $this->activityLogger->log($adminId, 'Password Change', 'Password was changed');
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateProfilePicture($adminId, $file) {
        try {
            $uploadDir = __DIR__ . '/../uploads/admin/profile_pictures/';
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            
            // Validate file type
            if (!in_array($fileExtension, $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG and GIF allowed.'];
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
            }
            
            // Generate unique filename
            $newFilename = 'admin_' . $adminId . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFilename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old profile picture if exists
                $stmt = $this->db->prepare("SELECT profile_picture FROM admins WHERE id = ?");
                $stmt->execute([$adminId]);
                $oldPicture = $stmt->fetchColumn();
                
                if ($oldPicture && file_exists($uploadDir . $oldPicture)) {
                    unlink($uploadDir . $oldPicture);
                }
                
                // Update database
                $stmt = $this->db->prepare("UPDATE admins SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$newFilename, $adminId]);
                
                $this->activityLogger->log($adminId, 'Profile Picture', 'Profile picture updated');
                
                return ['success' => true, 'message' => 'Profile picture updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Error uploading file'];
        } catch (PDOException $e) {
            error_log("Error updating profile picture: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    public function getCurrentAdmin() {
        if (!isset($_SESSION['admin_id'])) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 