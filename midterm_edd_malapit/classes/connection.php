<?php
$host = 'localhost';
$dbname = 'online_shoe_store';
$user = 'root';
$pass = '';

try {
    $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $user, $pass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>