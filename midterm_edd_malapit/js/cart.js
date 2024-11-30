function addToCart(id, name, price)
   {fetch('../ajax/add_to_cart.php', { method: 'POST',headers: {
            'Content-Type': 'application/x-www-form-urlencoded',},body: `shoe_id=${id}` })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cart-count').textContent = data.cartCount;
            alert(`${name} has been added to the cart!`);
        } else {
            alert('Error adding item to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding item to cart');
    });
}

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

function updateQuantity(shoeId, action) {
    fetch('../ajax/update_cart_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `shoe_id=${shoeId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating quantity');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating quantity');
    });
}

function removeFromCart(shoeId) {
    if (confirm('Are you sure you want to remove this item?')) {
        fetch('../ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `shoe_id=${shoeId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing item');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});