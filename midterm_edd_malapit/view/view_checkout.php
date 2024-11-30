<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');
require_once(__DIR__ . '/../classes/OrderCalculator.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: view_login.php");
    exit();
}

// Get cart items and calculate initial totals
try {
    $stmt = $connection->prepare("
        SELECT ci.*, s.name, s.price, s.image 
        FROM cart_items ci 
        JOIN shoes s ON ci.shoe_id = s.id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $calculator = new OrderCalculator();
    $initialTotals = $calculator->calculateTotal($cartItems, 'standard');
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: view_cart.php?error=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/checkout.css">
</head>
<body>
    <div class="container mt-4">
        <form id="checkoutForm" method="POST" action="process_order.php">
            <div class="row">
                <div class="col-md-8">
                    <!-- Delivery Address Section (Moved to top) -->
                    <div class="card mb-4" id="addressSection">
                        <div class="card-header">
                            <h3>Delivery Address</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Street Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            <div class="mb-3">
                                <label for="barangay" class="form-label">BARANGAY</label>
                                <input type="text" class="form-control" id="barangay" name="barangay" required>
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Section (Moved to middle) -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Payment Method</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="creditCard" value="credit_card" required>
                                <label class="form-check-label" for="creditCard">
                                    Credit/Debit Card
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="cashPayment" value="cash">
                                <label class="form-check-label" for="cashPayment">
                                    Cash Payment
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="inStore" value="in_store">
                                <label class="form-check-label" for="inStore">
                                    In-Store Payment
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod">
                                <label class="form-check-label" for="cod">
                                    Cash on Delivery
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Method Section (Moved to bottom) -->
                    <div class="card mb-4" id="deliverySection">
                        <div class="card-header">
                            <h3>Delivery Method</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="standard" value="standard" required>
                                <label class="form-check-label" for="standard">
                                    Standard Delivery (₱5.00)
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="express" value="express">
                                <label class="form-check-label" for="express">
                                    Express Delivery (₱15.00)
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="pickup" value="pickup">
                                <label class="form-check-label" for="pickup">
                                    Store Pickup (Free)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Section -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Order Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span data-subtotal>₱<?php echo number_format($initialTotals['subtotal'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Delivery Fee</span>
                                <span id="deliveryFee">₱0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total</strong>
                                <strong id="totalAmount">₱<?php echo number_format($initialTotals['subtotal'], 2); ?></strong>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">PLACE ORDER</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="../js/checkout.js"></script>
</body>
</html>