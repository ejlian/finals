<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: view_orders.php");
    exit();
}

try {
    $stmt = $connection->prepare("
        SELECT o.*, 
               oi.quantity, oi.price as item_price,
               s.name as shoe_name, s.image, s.size
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN shoes s ON oi.shoe_id = s.id
        WHERE o.id = ? AND o.user_id = ?
        LIMIT 1
    ");
    
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header("Location: view_orders.php");
        exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: view_orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo htmlspecialchars($_GET['id']); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/orderdetail.css">
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="view_shoe.php">My Shoe Store</a>
        </div>
    </nav>
 
    <div class="page-header">
        <div class="container">
            <h2>Order Details</h2>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Order #<?php echo htmlspecialchars($_GET['id']); ?></h2>
                <span class="badge bg-<?php echo $order['order_status'] === 'Pending' ? 'warning' : 'success'; ?>">
                    <?php echo htmlspecialchars($order['order_status']); ?>
                </span>
            </div>
            
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Delivery Information</h5>
                        <p><strong>Shipping Address:</strong><br>
                        <?php echo htmlspecialchars($order['shipping_address'] ?? 'Not provided'); ?></p>
                        
                        <p><strong>Phone:</strong><br>
                        <?php echo htmlspecialchars($order['phone'] ?? 'Not provided'); ?></p>
                        
                        <p><strong>Delivery Method:</strong><br>
                        <?php echo ucfirst(htmlspecialchars($order['delivery_method'] ?? 'standard')); ?></p>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Payment Information</h5>
                        <p><strong>Payment Method:</strong><br>
                        <?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></p>
                        
                        <p><strong>Total Amount:</strong><br>
                        ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                        
                        <p><strong>Delivery Fee:</strong><br>
                        ₱<?php echo number_format($order['delivery_fee'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html> 