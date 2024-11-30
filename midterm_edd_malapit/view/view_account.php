<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: view_login.php");
    exit();
}
try {
 
    $stmt = $connection->prepare("
        SELECT first_name, last_name, email 
        FROM customers 
        WHERE id = ? ");
    $stmt->execute([$_SESSION['user_id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($customer) {
        $username = $customer['first_name'] . ' ' . $customer['last_name'];
    } else {
        $username = 'Guest';
    }

    $orderStmt = $connection->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
    $orderStmt->execute([$_SESSION['user_id']]);
    $orderCount = $orderStmt->fetch(PDO::FETCH_ASSOC)['order_count'] ?? 0;

    $cartStmt = $connection->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE user_id = ?");
    $cartStmt->execute([$_SESSION['user_id']]);
    $cartCount = $cartStmt->fetch(PDO::FETCH_ASSOC)['cart_count'] ?? 0;

} catch (PDOException $e) {
    error_log($e->getMessage());
    $username = 'Guest';
    $orderCount = 0;
    $cartCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="view_shoe.php">EDD SHOE STORE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="view_cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge rounded-pill" id="cart-count"><?php echo $cartCount; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_orders.php">
                            <i class="fas fa-box"></i>
                            <span class="badge rounded-pill" id="order-count"><?php echo $orderCount; ?></span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="view_account.php">My Account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="view_logout.php">Logout</a></li>
                        </ul>
                     </li>
                 </ul>
             </div>
         </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="account-section">
                    <div class="profile-header">
                        <h2 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>
                            Welcome, <?php echo htmlspecialchars($username); ?>!
                        </h2>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo htmlspecialchars($customer['email'] ?? ''); ?>
                        </p>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $orderCount; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $cartCount; ?></div>
                                <div class="stat-label">Items in Cart</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="view_shoe.php" class="btn btn-primary me-2">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                        <a href="view_orders.php" class="btn btn-outline-primary">
                            <i class="fas fa-box me-2"></i>View My Orders
                        </a>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
    <script src="../js/header.js"></script>
</body>
</html> 