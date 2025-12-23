<?php
    $host = 'localhost';
    $server = 'root';
    $password = '';
    $db = 'ecommerce_db';

    try {
        $con = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $server, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }
?>