<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');
require_once(__DIR__ . '/../../classes/ProductManager.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->requireLogin();

if (isset($_GET['id'])) {
    $productManager = new ProductManager($connection);
    $product = $productManager->getProductById($_GET['id']);
    
    header('Content-Type: application/json');
    echo json_encode($product);
} 