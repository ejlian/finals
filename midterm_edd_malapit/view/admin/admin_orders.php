<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/OrderManager.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$orderManager = new OrderManager($connection);

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

if ($status === 'all') {
    $orders = $orderManager->getAllOrders($limit, $offset);
} else {
    $orders = $orderManager->getOrdersByStatus($status, $limit, $offset);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $orderManager->updateOrderStatus($_POST['order_id'], $_POST['new_status']);
    header("Location: admin_orders.php?status=$status&page=$page");
    exit();
}

// Add these status counts at the top
$statusCounts = $orderManager->getOrderStatusCounts();

function getStatusColor($status) {
    return match($status) {
        'Pending' => 'warning',
        'Processing' => 'info',
        'Shipped' => 'success',
        'Delivered' => 'secondary',
        default => 'primary'
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link rel="stylesheet" href="../../css/bootstrap.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container mt-4">
        <h2>Order Management</h2>
        
        <!-- Status filter buttons -->
        <div class="mb-3">
            <a href="?status=all" class="btn btn-outline-primary <?php echo $status === 'all' ? 'active' : ''; ?>">
                All Orders (<?php echo array_sum($statusCounts); ?>)
            </a>
            <?php foreach ($statusCounts as $statusName => $count): ?>
                <a href="?status=<?php echo $statusName; ?>" 
                   class="btn btn-outline-<?php echo getStatusColor($statusName); ?> 
                   <?php echo $status === $statusName ? 'active' : ''; ?>">
                    <?php echo $statusName; ?> (<?php echo $count; ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                No orders found for the selected status.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['items']); ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <form action="" method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="new_status" class="form-select form-select-sm" 
                                            onchange="confirmStatusChange(this.form)">
                                        <?php foreach (['Pending', 'Processing', 'Shipped', 'Delivered'] as $orderStatus): ?>
                                            <option value="<?php echo $orderStatus; ?>" 
                                                    <?php echo $order['order_status'] === $orderStatus ? 'selected' : ''; ?>>
                                                <?php echo $orderStatus; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="view_order_detail.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmStatusChange(form) {
        if (confirm('Are you sure you want to update this order\'s status?')) {
            form.submit();
        } else {
            // Reset to original value if cancelled
            const select = form.querySelector('select');
            select.value = select.getAttribute('data-original');
        }
    }

    // Store original values when page loads
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-form select').forEach(select => {
            select.setAttribute('data-original', select.value);
        });
    });
    </script>
</body>
</html>