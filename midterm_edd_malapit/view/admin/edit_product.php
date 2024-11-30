<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/ProductManager.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$productManager = new ProductManager($connection);
$message = '';

if (!isset($_GET['id'])) {
    header('Location: admin_products.php');
    exit;
}

$product = $productManager->getProductById($_GET['id']);

if (!$product) {
    header('Location: admin_products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $productManager->updateProduct(
        $_GET['id'],
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['stock']
    );

    if ($result) {
        $message = 'Product updated successfully!';
    } else {
        $message = 'Error updating product.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../../css/bootstrap.css">
    <link rel="stylesheet" href="../../css/variables.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Product</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" step="0.01" 
                               value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" name="stock" 
                               value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Image</label><br>
                        <img src="../../resources/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="Current product image"
                             style="width: 100px; height: 100px; object-fit: cover;">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
</body>
</html> 