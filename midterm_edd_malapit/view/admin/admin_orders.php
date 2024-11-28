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
        
        <div class="mb-3">
            <a href="?status=all" class="btn btn-outline-primary <?php echo $status === 'all' ? 'active' : ''; ?>">All Orders</a>
            <a href="?status=Pending" class="btn btn-outline-warning <?php echo $status === 'Pending' ? 'active' : ''; ?>">Pending</a>
            <a href="?status=Processing" class="btn btn-outline-info <?php echo $status === 'Processing' ? 'active' : ''; ?>">Processing</a>
            <a href="?status=Shipped" class="btn btn-outline-success <?php echo $status === 'Shipped' ? 'active' : ''; ?>">Shipped</a>
            <a href="?status=Delivered" class="btn btn-outline-secondary <?php echo $status === 'Delivered' ? 'active' : ''; ?>">Delivered</a>
        </div>

        <div class="row mb-4">
            <?php foreach ($statusCounts as $statusName => $count): ?>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($statusName); ?></h5>
                        <p class="card-text"><?php echo $count; ?> orders</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $order['order_status'] === 'Pending' ? 'warning' : ($order['order_status'] === 'Processing' ? 'info' : ($order['order_status'] === 'Shipped' ? 'primary' : 'success')); ?>">
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="new_status" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="Pending" <?php echo $order['order_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Processing" <?php echo $order['order_status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="Shipped" <?php echo $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </form>
                        <a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?>">Previous</a></li>
                <?php endif; ?>
                <li class="page-item"><a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?>">Next</a></li>
            </ul>
        </nav>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
</body>
</html>