=<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'view_login.php';</script>";
    exit();
}

try {
    $stmt = $connection->prepare("SELECT id FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        session_destroy();
        echo "<script>window.location.href = 'view_login.php';</script>";
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    session_destroy();
    echo "<script>window.location.href = 'view_login.php';</script>";
    exit();
}

try {
    $stmt = $connection->prepare("
        SELECT id, name, description, price, image, size, stock 
        FROM shoes 
        LIMIT 4
    ");
    $stmt->execute();
    $shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

function getUserCart($connection, $userId) {
    $stmt = $connection->prepare("
        SELECT ci.*, s.name, s.price, s.image 
        FROM cart_items ci 
        JOIN shoes s ON ci.shoe_id = s.id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST['action'])) {
    $response = ['success' => false, 'message' => '', 'cartCount' => 0];
    
    try {
        switch ($_POST['action']) {
            case 'add':
                $shoeId = $_POST['shoe_id'];
                $stmt = $connection->prepare("
                    SELECT id, quantity FROM cart_items 
                    WHERE user_id = ? AND shoe_id = ?");
                $stmt->execute([$_SESSION['user_id'], $shoeId]);
                $existingItem = $stmt->fetch();

                if ($existingItem) {
                    $stmt = $connection->prepare("
                        UPDATE cart_items 
                        SET quantity = quantity + 1 
                        WHERE id = ?");
                    $stmt->execute([$existingItem['id']]);
                } else {
                    $stmt = $connection->prepare("
                        INSERT INTO cart_items (user_id, shoe_id, quantity) 
                        VALUES (?, ?, 1)");
                    $stmt->execute([$_SESSION['user_id'], $shoeId]);
                }

                $stmt = $connection->prepare("
                    SELECT SUM(quantity) as count 
                    FROM cart_items 
                    WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $cartCount = $stmt->fetch()['count'] ?? 0;

                $response = [
                    'success' => true,
                    'message' => 'Item added to cart',
                    'cartCount' => $cartCount
                ];
                break;
        }
    } catch (Exception $e) {
        $response['message'] = "Error processing request";
        error_log($e->getMessage());
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

try {
    $stmt = $connection->prepare("
        SELECT SUM(quantity) as count 
        FROM cart_items 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $initialCartCount = $stmt->fetch()['count'] ?? 0;
} catch (Exception $e) {
    $initialCartCount = 0;
    error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Online Shoe Store</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/shoe.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body class="bg-img">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">EDD SHOE STORE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link" href="view_cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span class="badge bg-danger rounded-pill" id="cart-count">0</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link" href="view_orders.php">
                            <i class="fas fa-box"></i> Orders
                            <span class="badge bg-primary rounded-pill" id="order-count">0</span>
                        </a>
                    </div>

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

    <div class="container mt-3">
        <div class="shop-all-container">
            <a href="view_shop_all.php" class="btn btn-primary">Shop All</a>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <?php foreach ($shoes as $shoe): ?>
                <div class="col-md-3">
                    <div class="card">
                        <img src="../resources/<?php echo htmlspecialchars($shoe['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($shoe['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($shoe['name']); ?></h5>
                            <p class="price">₱<?php echo number_format(htmlspecialchars($shoe['price']), 2); ?></p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="addToCart(<?php echo $shoe['id']; ?>, '<?php echo htmlspecialchars($shoe['name']); ?>', <?php echo $shoe['price']; ?>)">
                                    Add to Cart
                                </button>
                                <button class="btn btn-secondary view-details" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#shoeModal"
                                    data-id="<?php echo $shoe['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($shoe['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($shoe['description']); ?>"
                                    data-price="<?php echo htmlspecialchars($shoe['price']); ?>"
                                    data-image="<?php echo htmlspecialchars($shoe['image']); ?>"
                                    data-size="<?php echo htmlspecialchars($shoe['size']); ?>"
                                    data-stock="<?php echo htmlspecialchars($shoe['stock']); ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="shoeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shoeModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="" class="img-fluid mb-3">
                    <p id="modalDescription"></p>
                    <p class="fw-bold">Price: ₱<span id="modalPrice"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Size: <span id="modalSize"></span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Available: <span id="modalStock"></span></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary modal-add-to-cart">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
    <script src="../js/header.js"></script>
    <script src="../js/shoe.js"></script>
</body>
</html>