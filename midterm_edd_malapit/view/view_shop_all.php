<?php
session_start();
require_once(__DIR__ . '/../classes/connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: view_login.php");
    exit();
}

try {
    $stmt = $connection->prepare("SELECT * FROM shoes");
    $stmt->execute();
    $shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop All</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/shopall.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
</head>
<body class="bg-img">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="view_shoe.php">Edd Shoe Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="view_cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cart-count" class="badge bg-danger">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <?php foreach ($shoes as $shoe): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="../resources/<?php echo htmlspecialchars($shoe['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($shoe['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($shoe['name']); ?></h5>
                            <p class="card-text">₱<?php echo number_format(htmlspecialchars($shoe['price']), 2); ?></p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary add-to-cart" 
                                    data-id="<?php echo $shoe['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($shoe['name']); ?>" 
                                    data-price="<?php echo htmlspecialchars($shoe['price']); ?>">
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
                    <p class="fw-bold">Price: <span id="modalPrice"></span></p>
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
    <script src="../js/shopall.js"></script>

</body>
</html>