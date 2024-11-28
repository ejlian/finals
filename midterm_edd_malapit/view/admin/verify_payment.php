<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/PaymentMethod.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$paymentMethod = new CashOnDeliveryPayment(0, null, $connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? null;
    
    if ($orderId && $status) {
        $paymentMethod->updateOrderPaymentStatus($orderId, $status);
    }
}

// Get pending payments
$stmt = $connection->prepare("
    SELECT o.*, pm.name as payment_method_name, pm.type as payment_type
    FROM orders o
    JOIN payment_methods pm ON o.payment_method_id = pm.id
    WHERE o.payment_status = 'pending'
    ORDER BY o.created_at DESC
");
$stmt->execute();
$pendingPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Payments</title>
    <link rel="stylesheet" href="../../css/bootstrap.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Pending Payments</h2>
        
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Payment Method</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingPayments as $payment): ?>
                        <tr>
                            <td>#<?php echo $payment['id']; ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method_name']); ?></td>
                            <td>₱<?php echo number_format($payment['total_amount'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $payment['id']; ?>">
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        Verify Payment
                                    </button>
                                </form>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $payment['id']; ?>">
                                    <input type="hidden" name="status" value="failed">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Mark Failed
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 