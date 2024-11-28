<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: view_login.php");
    exit();
}

try {
    $stmt = $connection->prepare("
        SELECT o.id, o.created_at, o.total_amount, o.payment_method, o.order_status,
               COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="view_shoe.php">Edd Shoe Store</a>
            <a href="view_shoe.php" class="btn btn-outline-light">Back to Shop</a>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h2>My Orders</h2>
        </div>
    </div>

    <div class="container">
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                You haven't placed any orders yet.
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3 class="mb-0">Order #<?php echo htmlspecialchars($order['id']); ?></h3>
                        <span class="badge bg-<?php echo $order['order_status'] === 'Pending' ? 'warning' : 'success'; ?>">
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </span>
                    </div>
                    
                    <div class="order-details">
                        <div>
                            <p><strong>Order Date:</strong><br>
                               <?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></p>
                            <p><strong>Payment Method:</strong><br>
                               <?php echo htmlspecialchars($order['payment_method']); ?></p>
                        </div>
                        <div>
                            <p class="price">₱<?php echo number_format(htmlspecialchars($order['total_amount']), 2); ?></p>
                            <p><strong>Items:</strong><br>
                               <?php echo $order['item_count']; ?> items</p>
                        </div>
                        <div class="view-details-btn">
                            <a href="view_order_detail.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 