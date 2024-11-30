<?php
require_once(__DIR__ . '/../../../classes/SessionManager.php');
require_once(__DIR__ . '/../../../classes/connection.php');
require_once(__DIR__ . '/../../../classes/AdminAuth.php');

SessionManager::startSession();

$adminAuth = new AdminAuth($connection);
$adminDetails = $adminAuth->getCurrentAdmin();
?>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_orders.php">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="activity_logs.php">Activity Logs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_payment_methods.php">Payment Methods</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="verify_payment.php">Verify Payments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="store_pickup_queue.php">Store Pickup Queue</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <button class="btn nav-link dropdown-toggle d-flex align-items-center" 
                            type="button" 
                            id="profileDropdown" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <?php 
                        $profilePic = $adminDetails['profile_picture'] ?? null;
                        $profilePicUrl = $profilePic 
                            ? "../../resources/admin/" . htmlspecialchars($profilePic)
                            : "../../resources/default-profile.png";
                        ?>
                        <img src="<?php echo $profilePicUrl; ?>" 
                             class="rounded-circle profile-icon" 
                             alt="Profile"
                             style="width: 32px; height: 32px; object-fit: cover;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="admin_profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="admin_logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Add this at the end of the file -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> 