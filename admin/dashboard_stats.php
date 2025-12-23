<?php
// dashboard_stats.php
header('Content-Type: application/json');
require_once "../config/db.php"; 

$data = [
    'users'    => 0,
    'orders'   => 0,
    'products' => 0,
    'revenue'  => 0
];

if ($conn) {
    try {
        // 1. Total Users
        $res = $conn->query("SELECT COUNT(*) as total FROM users");
        $data['users'] = ($res) ? $res->fetch_assoc()['total'] : 0;

        // 2. Total Orders
        $res = $conn->query("SELECT COUNT(*) as total FROM orders");
        $data['orders'] = ($res) ? $res->fetch_assoc()['total'] : 0;

        // 3. Total Products
        $res = $conn->query("SELECT COUNT(*) as total FROM products");
        $data['products'] = ($res) ? $res->fetch_assoc()['total'] : 0;

        // 4. Total Revenue (Matched logic with the main dashboard)
        $res = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'");
        if($res) {
            $row = $res->fetch_assoc();
            $data['revenue'] = $row['total'] ?? 0;
        }

    } catch (Exception $e) {
        // Log error if needed
    }
}

echo json_encode($data);
?>