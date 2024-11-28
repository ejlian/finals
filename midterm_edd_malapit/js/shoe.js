document.addEventListener('DOMContentLoaded', function() {
  const viewDetailsButtons = document.querySelectorAll('.view-details');
  const shoeModal = document.getElementById('shoeModal');
  let currentShoeId = null;

  document.getElementById('cart-count').textContent = '<?php echo $initialCartCount; ?>';

  viewDetailsButtons.forEach(button => {
      button.addEventListener('click', function() {
          const name = this.getAttribute('data-name');
          const description = this.getAttribute('data-description');
          const price = this.getAttribute('data-price');
          const image = this.getAttribute('data-image');
          const size = this.getAttribute('data-size');
          const stock = this.getAttribute('data-stock');
          currentShoeId = this.getAttribute('data-id');

          document.getElementById('shoeModalLabel').textContent = name;
          document.getElementById('modalDescription').textContent = description;
          document.getElementById('modalPrice').textContent = parseFloat(price).toFixed(2);
          document.getElementById('modalImage').src = '../resources/' + image;
          document.getElementById('modalSize').textContent = size;
          document.getElementById('modalStock').textContent = stock;
      });
  });

  document.querySelector('.modal-add-to-cart').addEventListener('click', function() {
      if (currentShoeId) {
          const modalTitle = document.getElementById('shoeModalLabel').textContent;
          addToCart(currentShoeId, modalTitle);
          
          const modalInstance = bootstrap.Modal.getInstance(shoeModal);
          modalInstance.hide();
      }
  });
});