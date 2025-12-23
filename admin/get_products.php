<?php
session_start();

/* AUTH CHECK */
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

/* DB CONNECTION */
require '../config/db.php';
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* FETCH PRODUCTS */
try {
    $products = $con->query("
        SELECT p.id, p.name, p.price, p.quantity, p.status, p.image_path, p.created_at,
               c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products);
} catch (Exception $e) {
    echo json_encode([]);
}
?>