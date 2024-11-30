<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');
require_once(__DIR__ . '/../classes/OrderManager.php');
require_once(__DIR__ . '/../classes/OrderCalculator.php');

if (!isset($_SESSION['user_id']) || empty($_POST)) {
    header("Location: view_cart.php");
    exit();
}

try {
    // Get cart items
    $stmt = $connection->prepare("
        SELECT ci.*, s.price, s.name 
        FROM cart_items ci 
        JOIN shoes s ON ci.shoe_id = s.id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        throw new Exception("Cart is empty");
    }

    // Create delivery address
    $address = sprintf("%s, %s, %s, %s",
        $_POST['street_address'],
        $_POST['barangay'],
        $_POST['city'],
        $_POST['phone']
    );

    // Initialize OrderManager
    $orderManager = new OrderManager($connection);
    
    // Place the order
    $orderId = $orderManager->placeOrder(
        $_SESSION['user_id'],
        $cartItems,
        $_POST['delivery_method'],
        $_POST['payment_method'],
        [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'address' => $address,
            'phone' => $_POST['phone']
        ]
    );

    if ($orderId) {
        // Clear the cart after successful order
        $stmt = $connection->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        // Store order ID in session for thank you page
        $_SESSION['last_order_id'] = $orderId;
        
        // Redirect to thank you page
        header("Location: view_thank_you.php");
        exit();
    }

} catch (Exception $e) {
    error_log("Order placement error: " . $e->getMessage());
    header("Location: view_checkout.php?error=" . urlencode($e->getMessage()));
    exit();
} 