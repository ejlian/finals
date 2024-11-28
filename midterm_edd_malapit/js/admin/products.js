function editProduct(productId) {
    fetch(`get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editName').value = product.name;
            document.getElementById('editBrand').value = product.brand;
            document.getElementById('editPrice').value = product.price;
            document.getElementById('editStock').value = product.stock;
            
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        });
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch('delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete product');
            }
        });
    }
} 