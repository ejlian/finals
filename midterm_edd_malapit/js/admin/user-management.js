function viewUserDetails(userId) {
    const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
    
    fetch(`../../ajax/get_user_details.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            const content = `
                <div class="user-profile mb-4">
                    <h6>Personal Information</h6>
                    <p><strong>Name:</strong> ${data.user.first_name} ${data.user.last_name}</p>
                    <p><strong>Email:</strong> ${data.user.email}</p>
                    <p><strong>Phone:</strong> ${data.user.phone}</p>
                    <p><strong>Address:</strong> ${data.user.address}</p>
                </div>
                <div class="order-history">
                    <h6>Recent Orders</h6>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.orders.map(order => `
                                <tr>
                                    <td>#${order.id}</td>
                                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                    <td>${order.items}</td>
                                    <td>₱${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                    <td><span class="badge bg-${getStatusColor(order.status)}">${order.status}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('userDetailsContent').innerHTML = content;
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading user details');
        });
}

function getStatusColor(status) {
    switch(status) {
        case 'Pending': return 'warning';
        case 'Processing': return 'info';
        case 'Completed': return 'success';
        case 'Cancelled': return 'danger';
        default: return 'secondary';
    }
} 