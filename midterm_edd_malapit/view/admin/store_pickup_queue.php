<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/QueueManager.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

$queueManager = new QueueManager($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $notes = $_POST['notes'] ?? '';
    
    if ($orderId && $status) {
        $success = $queueManager->updateQueueStatus($orderId, $status, $notes);
        if ($success) {
            header("Location: store_pickup_queue.php?success=1");
            exit;
        }
    }
}

$queueOrders = $queueManager->getStorePickupQueue();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Store Pickup Queue</title>
    <link rel="stylesheet" href="../../css/bootstrap.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Store Pickup Queue</h2>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Queue updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <table class="table" id="queueTable">
                    <thead>
                        <tr>
                            <th>Queue #</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queueOrders as $index => $order): ?>
                        <tr class="<?php echo $order['order_status'] === 'Ready' ? 'table-success' : ''; ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['username']); ?>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
                            </td>
                            <td>
                                <?php echo $order['item_count']; ?> items
                                <button class="btn btn-sm btn-link" 
                                        onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    View Details
                                </button>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($order['payment_method']); ?>
                                <br>
                                <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                            <td>
                                <button class="btn btn-success btn-sm" 
                                        onclick="updateStatus(<?php echo $order['id']; ?>, 'Ready')">
                                    Mark Ready
                                </button>
                                <button class="btn btn-primary btn-sm" 
                                        onclick="updateStatus(<?php echo $order['id']; ?>, 'Completed')">
                                    Complete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewOrderDetails(orderId) {
        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        modal.show();
        
        fetch(`ajax/get_order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                let content = '<table class="table table-sm">';
                content += '<thead><tr><th>Product</th><th>Size</th><th>Qty</th></tr></thead><tbody>';
                
                data.forEach(item => {
                    content += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.size}</td>
                            <td>${item.quantity}</td>
                        </tr>
                    `;
                });
                
                content += '</tbody></table>';
                document.getElementById('orderDetailsContent').innerHTML = content;
            });
    }

    function updateStatus(orderId, status) {
        const notes = prompt("Add notes (optional):");
        
        const form = document.createElement('form');
        form.method = 'POST';
        
        const fields = {
            order_id: orderId,
            status: status,
            notes: notes || ''
        };
        
        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
    </script>
</body>
</html> 