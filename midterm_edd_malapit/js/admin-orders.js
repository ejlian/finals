document.addEventListener('DOMContentLoaded', function() {
    // Show success/error messages
    const showMessage = (message, type) => {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.mb-3'));
        
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    };

    // Handle status change
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const originalValue = this.getAttribute('data-original');
            const newValue = this.value;
            
            if (originalValue !== newValue) {
                this.form.submit();
            }
        });
    });
}); 