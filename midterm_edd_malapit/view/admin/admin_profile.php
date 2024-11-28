<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$admin = $adminAuth->getCurrentAdmin();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'update_profile') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        
        $stmt = $connection->prepare("
            UPDATE admins 
            SET username = ?, email = ? 
            WHERE id = ?
        ");
        
        if ($stmt->execute([$username, $email, $_SESSION['admin_id']])) {
            $message = 'Profile updated successfully!';
            $admin = $adminAuth->getAdminById($_SESSION['admin_id']);
        } else {
            $message = 'Error updating profile.';
        }
    }
    
    if ($_POST['action'] === 'change_password') {
        if (isset($_POST['new_password'])) {
            if ($adminAuth->changePassword(
                $_SESSION['admin_id'],
                $_POST['current_password'],
                $_POST['new_password']
            )) {
                $message = 'Password changed successfully!';
            } else {
                $message = 'Error changing password. Please check your current password.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="../../css/bootstrap.css">
    <link rel="stylesheet" href="../../css/variables.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Update Profile</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="text-center mb-4">
                                <?php 
                                $profilePic = $admin['profile_picture'] ?? null;
                                $profilePicUrl = $profilePic 
                                    ? "../../uploads/admin/profile_pictures/" . htmlspecialchars($profilePic)
                                    : "../../resources/images/default-profile.png";
                                ?>
                                <img src="<?php echo $profilePicUrl; ?>" 
                                     class="rounded-circle profile-picture mb-3" 
                                     alt="Profile Picture"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data" class="mb-4">
                                <input type="hidden" name="action" value="update_picture">
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Update Profile Picture</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="profile_picture" 
                                           name="profile_picture" 
                                           accept="image/*"
                                           required>
                                    <div class="form-text">Maximum file size: 5MB. Allowed types: JPG, PNG, GIF</div>
                                </div>
                                <button type="submit" class="btn btn-secondary">Upload Picture</button>
                            </form>
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" 
                                       name="username" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Change Password</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
</body>
</html> 