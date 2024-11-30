<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_order_id'])) {
    header("Location: view_login.php");
    exit();
}

require_once(__DIR__ . '/../classes/connection.php');

// Get order details if needed
$orderId = $_SESSION['last_order_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/thankyou.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-img">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h1 class="card-title text-success mb-4">
                            <i class="fas fa-check-circle"></i> Thank You!
                        </h1>
                        <h4 class="mb-4">Your order #<?php echo $orderId; ?> has been successfully placed!</h4>
                        <p class="mb-4">We'll send you an email with your order details shortly.</p>
                        <div class="mt-4">
                            <a href="view_orders.php" class="btn btn-primary me-2">View Orders</a>
                            <a href="view_shoe.php" class="btn btn-secondary">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>