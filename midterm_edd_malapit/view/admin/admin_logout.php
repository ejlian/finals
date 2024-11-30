<?php
session_start();
require_once(__DIR__ . '/../../classes/connection.php');
require_once(__DIR__ . '/../../classes/AdminAuth.php');

$adminAuth = new AdminAuth($connection);
$adminAuth->logout();

header("Location: admin.php");
exit();
?> 