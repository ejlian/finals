document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateOrderCount();
});

function updateCartCount() {
    fetch('../ajax/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('cart-count').textContent = data.cartCount;
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateOrderCount() {
    fetch('../ajax/get_order_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('order-count').textContent = data.orderCount;
            }
        })
        .catch(error => console.error('Error:', error));
}

setInterval(() => {
    updateCartCount();
    updateOrderCount();
}, 30000); 