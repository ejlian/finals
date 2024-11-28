document.addEventListener('DOMContentLoaded', function() {
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  const viewDetailsButtons = document.querySelectorAll('.view-details');
  const shoeModal = document.getElementById('shoeModal');
  let currentShoeId = null;
  updateCartCount();

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
          document.getElementById('modalPrice').textContent = '₱' + parseFloat(price).toFixed(2);
          document.getElementById('modalImage').src = '../resources/' + image;
          document.getElementById('modalSize').textContent = size;
          document.getElementById('modalStock').textContent = stock;
      });
  });

  addToCartButtons.forEach(button => {
      button.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const name = this.getAttribute('data-name');
          addToCart(id, name);
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

  async function addToCart(shoeId, name) {
      try {
          const formData = new FormData();
          formData.append('shoe_id', shoeId);

          const response = await fetch('../ajax/add_to_cart.php', {
              method: 'POST',
              body: formData
          });

          const result = await response.json();
          
          if (result.success) {
              document.getElementById('cart-count').textContent = result.cartCount;
              alert(name + ' has been added to the cart!');
          } else {
              alert('Failed to add item to cart: ' + (result.message || 'Unknown error'));
          }
      } catch (error) {
          console.error('Error:', error);
          alert('An error occurred while adding to cart');
      }
  }

  async function updateCartCount() {
      try {
          const response = await fetch('../ajax/get_cart_count.php');
          const result = await response.json();
          if (result.success) {
              document.getElementById('cart-count').textContent = result.cartCount;
          }
      } catch (error) {
          console.error('Error:', error);
      }
  }
});