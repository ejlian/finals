document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    const deliveryMethodInputs = document.querySelectorAll('input[name="delivery_method"]');
    const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    const deliverySection = document.getElementById('deliverySection');
    const addressSection = document.getElementById('addressSection');
    const deliveryFeeElement = document.getElementById('deliveryFee');
    const totalAmountElement = document.getElementById('totalAmount');
    
    // Get initial subtotal from the displayed value
    const subtotal = parseFloat(totalAmountElement.textContent.replace('₱', ''));

    function updateTotal() {
        // Get the subtotal value (remove the ₱ symbol and convert to float)
        const subtotal = parseFloat(document.querySelector('[data-subtotal]').textContent.replace('₱', '').replace(',', ''));
        
        // Get selected delivery method fee
        const selectedDelivery = document.querySelector('input[name="delivery_method"]:checked');
        let deliveryFee = 0;

        if (selectedDelivery) {
            switch (selectedDelivery.value) {
                case 'standard':
                    deliveryFee = 5.00;
                    break;
                case 'express':
                    deliveryFee = 15.00;
                    break;
                case 'pickup':
                    deliveryFee = 0.00;
                    break;
            }
        }

        // Update delivery fee display
        document.getElementById('deliveryFee').textContent = `₱${deliveryFee.toFixed(2)}`;
        
        // Calculate and update total
        const total = subtotal + deliveryFee;
        document.getElementById('totalAmount').textContent = `₱${total.toFixed(2)}`;
    }

    // Handle payment method changes
    paymentMethodInputs.forEach(input => {
        input.addEventListener('change', function() {
            const isInStore = this.value === 'in_store';
            deliverySection.style.display = isInStore ? 'none' : 'block';
            addressSection.style.display = isInStore ? 'none' : 'block';
            
            if (isInStore) {
                document.querySelectorAll('#deliverySection input, #addressSection input').forEach(input => {
                    input.required = false;
                });
            } else {
                document.querySelectorAll('#deliverySection input[type="radio"], #addressSection input').forEach(input => {
                    input.required = true;
                });
            }
            
            updateTotal();
        });
    });

    // Handle delivery method changes
    deliveryMethodInputs.forEach(input => {
        input.addEventListener('change', updateTotal);
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        const selectedDelivery = document.querySelector('input[name="delivery_method"]:checked');
        
        if (!selectedPayment) {
            alert('Please select a payment method');
            return;
        }
        
        if (selectedPayment.value !== 'in_store' && !selectedDelivery) {
            alert('Please select a delivery method');
            return;
        }
        
        // If all validations pass, submit the form
        this.submit();
    });
}); 