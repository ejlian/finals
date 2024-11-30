function refreshDashboard() {
    fetch('ajax/get_dashboard_data.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalOrders').textContent = data.totalOrders;
            document.getElementById('totalRevenue').textContent = 
                '₱' + parseFloat(data.totalRevenue).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            document.getElementById('totalUsers').textContent = data.totalUsers;
        })
        .catch(error => console.error('Error:', error));
}

// Refresh dashboard every 5 minutes
setInterval(refreshDashboard, 300000); 