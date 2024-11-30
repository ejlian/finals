<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $userId = $_POST['user_id'];
        
        switch ($_POST['action']) {
            case 'deactivate':
                $stmt = $connection->prepare("UPDATE customers SET is_active = 0 WHERE id = ?");
                $stmt->execute([$userId]);
                break;
                
            case 'activate':
                $stmt = $connection->prepare("UPDATE customers SET is_active = 1 WHERE id = ?");
                $stmt->execute([$userId]);
                break;
                
            case 'delete':
                // Only allow deletion if user has no orders
                $stmt = $connection->prepare("DELETE FROM customers WHERE id = ? AND NOT EXISTS (SELECT 1 FROM orders WHERE user_id = ?)");
                $stmt->execute([$userId, $userId]);
                break;
        }
        
        header("Location: admin_users.php?message=User updated successfully");
        exit();
    }
}

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $query = "
        SELECT 
            c.*,
            COUNT(DISTINCT o.id) as total_orders,
            COALESCE(SUM(o.total_amount), 0) as total_spent,
            CASE WHEN c.is_active = 1 THEN 'Active' ELSE 'Inactive' END as status
        FROM customers c
        LEFT JOIN orders o ON c.id = o.user_id
        WHERE 
            (c.first_name LIKE :search 
            OR c.last_name LIKE :search
            OR c.email LIKE :search)
        GROUP BY c.id
        ORDER BY c.id DESC
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bindValue(':search', "%$search%");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="../../css/bootstrap.css">
    <link rel="stylesheet" href="../../css/variables.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>User Management</h2>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name or email" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="admin_users.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $user['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['total_orders']); ?></td>
                                <td>₱<?php echo number_format($user['total_spent'], 2); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                            View
                                        </button>
                                        
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to <?php echo $user['is_active'] ? 'deactivate' : 'activate'; ?> this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="<?php echo $user['is_active'] ? 'deactivate' : 'activate'; ?>">
                                            <button type="submit" class="btn btn-sm btn-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>">
                                                <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                        
                                        <?php if ($user['total_orders'] == 0): ?>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin/user-management.js"></script>
</body>
</html>