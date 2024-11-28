<?php
require_once('classes/connection.php');

// Main views
include('view/view_shoe.php');
include('view/view_login.php');
include('view/view_cart.php');
include('view/view_checkout.php');
include('view/view_logout.php');
include('view/view_product_detail.php');
include('view/view_shop_all.php');
include('view/view_thank_you.php');
include('view/view_place_order.php');
include('view/view_orders.php');
include('view/view_order_detail.php');
include('view/view_account.php');

// Admin views
include('view/admin/admin_dashboard.php');
include('view/admin/admin_orders.php');
include('view/admin/admin_users.php');
include('view/admin/admin.php');
include('view/admin/admin_profile.php');
?>