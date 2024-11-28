<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: view_orders.php");
    exit();
}

try {
   
    $stmt = $connection->prepare("
        SELECT o.*, oi.*, s.name as shoe_name, s.image, s.size,
               o.shipping_address, o.phone, o.payment_method, 
               o.delivery_method, o.total_amount, o.delivery_fee
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN shoes s ON oi.shoe_id = s.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orderItems)) {
        header("Location: view_orders.php");
        exit();
    }

    $order = $orderItems[0]; 
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
    <title>Order Details</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/orderdetail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-section">
                            <h4>Delivery Information</h4>
                            <p><strong>Shipping Address:</strong><br>
                               <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                            <p><strong>Phone:</strong><br>
                               <?php echo htmlspecialchars($order['phone']); ?></p>
                            <p><strong>Delivery Method:</strong><br>
                               <?php echo htmlspecialchars($order['delivery_method']); ?></p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-section">
                            <h4>Payment Information</h4>
                            <p><strong>Payment Method:</strong><br>
                               <?php echo htmlspecialchars($order['payment_method']); ?></p>
                            <p><strong>Total Amount:</strong><br>
                               ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                            <p><strong>Delivery Fee:</strong><br>
                               ₱<?php echo number_format($order['delivery_fee'], 2); ?></p>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4 mb-3">Order Items</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="item-cell">
                                            <img src="../resources/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['shoe_name']); ?>"
                                                 class="order-item-image">
                                            <span><?php echo htmlspecialchars($item['shoe_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['size']); ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer">
                <a href="view_orders.php" class="btn btn-secondary">Back to Orders</a>
            </div>
        </div>
    </div>
</body>
</html> 