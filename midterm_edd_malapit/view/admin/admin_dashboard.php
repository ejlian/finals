<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/DashboardStats.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$dashboardStats = new DashboardStats($connection);

$totalOrders = $dashboardStats->getTotalOrders();
$totalUsers = $dashboardStats->getTotalUsers();
$totalRevenue = $dashboardStats->getTotalRevenue();
$salesData = $dashboardStats->getSalesDataLastWeek();
$topSellingProducts = $dashboardStats->getTopSellingProducts();
$recentOrders = $dashboardStats->getRecentOrders();

// Fetch Recent Orders with Customer Details
$recentOrdersQuery = "
    SELECT o.id, o.total_amount, o.created_at, 
           c.first_name, c.last_name,
           GROUP_CONCAT(s.name SEPARATOR ', ') as items
    FROM orders o
    JOIN customers c ON o.user_id = c.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN shoes s ON oi.shoe_id = s.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
";

// Top Selling Products Query
$topProductsQuery = "
    SELECT s.id, s.name, s.image, s.price,
           COUNT(oi.id) as total_sales,
           SUM(oi.quantity) as units_sold
    FROM shoes s
    LEFT JOIN order_items oi ON s.id = oi.shoe_id
    GROUP BY s.id
    ORDER BY units_sold DESC
    LIMIT 5
";

try {
    $recentOrders = $connection->query($recentOrdersQuery)->fetchAll(PDO::FETCH_ASSOC);
    $topProducts = $connection->query($topProductsQuery)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../css/bootstrap.css">
    <link rel="stylesheet" href="../../css/variables.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Dashboard</h2>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Orders</h5>
                        <p class="card-text display-4"><?php echo $totalOrders; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text display-4"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Revenue</h5>
                        <p class="card-text display-4">₱<?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Chart and Top Products -->
        <div class="row mb-4">
            <!-- Sales Trend Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sales Trend (Last 7 Days)</h5>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Selling Products -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top Selling Products</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($topSellingProducts)): ?>
                            <?php foreach ($topSellingProducts as $product): ?>
                                <div class="product-card mb-3">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-4">
                                            <img src="../../resources/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 class="img-fluid rounded" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="col-8">
                                            <div class="card-body py-2">
                                                <h6 class="card-title mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                <p class="card-text mb-1">
                                                    <small class="text-muted">
                                                        Sold: <?php echo $product['units_sold'] ?? 0; ?> units
                                                    </small>
                                                </p>
                                                <p class="card-text">
                                                    <strong>₱<?php echo number_format($product['price'], 2); ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No products sold yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['items']); ?></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($salesData, 'date')); ?>,
                datasets: [{
                    label: 'Sales (₱)',
                    data: <?php echo json_encode(array_column($salesData, 'total')); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="../../js/bootstrap.bundle.min.js"></script>
</body>
</html>