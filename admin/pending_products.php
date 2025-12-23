<?php
session_start();
require '../config/db.php';
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pending = $con->query("
    SELECT p.id, p.name, p.price, p.quantity, p.status, p.image, p.created_at,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'pending'
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
