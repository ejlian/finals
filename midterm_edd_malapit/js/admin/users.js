function viewOrderHistory(userId) {
    fetch(`../../api/admin/get_user_orders.php?user_id=${userId}`)
        .then(response => response.json())
        .then(orders => {
            const tbody = document.getElementById('orderHistoryBody');
            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td>${order.id}</td>
                    <td>${order.created_at}</td>
                    <td><span class="badge bg-${order.order_status === 'Pending' ? 'warning' : 'success'}">${order.order_status}</span></td>
                    <td>${order.total_items}</td>
                    <td>₱${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                </tr>
            `).join('');
            new bootstrap.Modal(document.getElementById('orderHistoryModal')).show();
        });
}

function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
    if (confirm(`Are you sure you want to ${currentStatus === 'active' ? 'suspend' : 'activate'} this user?`)) {
        fetch('../../api/admin/toggle_user_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update user status');
            }
        });
    }
} 