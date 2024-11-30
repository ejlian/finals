document.addEventListener('DOMContentLoaded', function() {const modalElement = document.getElementById('orderDetailsModal');
    if (!modalElement) return;
    const modal = new bootstrap.Modal(modalElement);
    
    document.querySelectorAll('.view-order-details').forEach(button => {
        button.addEventListener('click', async function() {
            const orderId = this.getAttribute('data-order-id');
            try {const response = await fetch(`../ajax/get_order_details.php?id=${orderId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                if (data.success) {
                    displayOrderDetails(data.order);
                    modal.show();
                } else {
                    alert('Error loading order details: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error loading order details. Please try again.');
            }
        });
    });
  
    function displayOrderDetails(order) {
        const content = document.getElementById('orderDetailsContent');
        const statusClass = {
            'pending': 'warning',
            'processing': 'info',
            'delivered': 'success',
            'cancelled': 'danger'
        }[order.status] || 'secondary';
  
        const html = `
            <div class="order-details">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Order #${order.id}</h4>
                    <span class="badge bg-${statusClass}">${order.status.toUpperCase()}</span>
                </div>
                
                <div class="order-items">
                    <h5>Items Ordered</h5>
                    <ul class="list-group">
                        ${order.items.map(item => `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <img src="/shoe_store/resources/${item.image}" alt="${item.name}" 
                                         style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                    ${item.name}
                                </div>
                                
                                <div>
                                    <span class="badge bg-primary">${item.quantity}x</span>
                                    $${(item.price * item.quantity).toFixed(2)}
                                </div>
                            </li>
                        `).join('')}
                    </ul>
                </div>
  
                <div class="delivery-info">
                    <h5>Delivery Information</h5>
                    <p><strong>Address:</strong> ${order.shipping_address}</p>
                    <p><strong>Phone:</strong> ${order.phone}</p>
                    <p><strong>Delivery Method:</strong> ${order.delivery_method}</p>
                    <p><strong>Estimated Delivery Time:</strong> ${order.estimated_delivery} minutes</p>
                </div>
  
                <div class="payment-info mt-3">
                    <h5>Payment Details</h5>
                    <table class="table table-bordered">
                        <tr>
                            <td>Subtotal:</td>
                            <td>$${(order.total_amount - order.delivery_fee).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td>Delivery Fee:</td>
                            <td>$${order.delivery_fee.toFixed(2)}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total:</strong></td>
                            <td><strong>$${order.total_amount.toFixed(2)}</strong></td>
                        </tr>
                    </table>
                    <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                </div>
            </div>`;
        
        content.innerHTML = html;
    }
  });