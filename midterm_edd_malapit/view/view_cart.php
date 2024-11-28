<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: view_login.php");
    exit();
}

try { $stmt = $connection->prepare("
        SELECT ci.*, s.name, s.price, s.image, s.size 
        FROM cart_items ci 
        JOIN shoes s ON ci.shoe_id = s.id 
        WHERE ci.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = array_reduce($cartItems, function($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);
} catch (Exception $e) {
    error_log($e->getMessage());
    $cartItems = [];
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2 class="mb-4">Shopping Cart</h2>
        
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="view_shoe.php">Continue shopping</a>
            </div>
        <?php else: ?>
            
            <div class="row">
                <div class="col-md-8">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                  
                                    <div class="col-md-2">
                                        <img src="../resources/<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             class="img-fluid" style="max-width: 100px;">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="text-muted mb-0">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="updateQuantity(<?php echo $item['shoe_id']; ?>, 'decrease')">−</button>
                                            <div class="quantity-display">
                                                <?php echo $item['quantity']; ?>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="updateQuantity(<?php echo $item['shoe_id']; ?>, 'increase')">+</button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <span class="h5">₱<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                 </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Items</span>
                                <span><?php echo array_sum(array_column($cartItems, 'quantity')); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total</strong>
                                <strong>₱<?php echo number_format($total, 2); ?></strong>
                            </div>
                            <a href="view_checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
</body>
</html>