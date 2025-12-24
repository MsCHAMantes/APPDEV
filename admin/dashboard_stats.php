<?php
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
       
        $res = $conn->query("SELECT COUNT(*) as total FROM users");
        $data['users'] = ($res) ? $res->fetch_assoc()['total'] : 0;

        
        $res = $conn->query("SELECT COUNT(*) as total FROM orders");
        $data['orders'] = ($res) ? $res->fetch_assoc()['total'] : 0;

 
        $res = $conn->query("SELECT COUNT(*) as total FROM products");
        $data['products'] = ($res) ? $res->fetch_assoc()['total'] : 0;

       
        $res = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'");
        if($res) {
            $row = $res->fetch_assoc();
            $data['revenue'] = $row['total'] ?? 0;
        }

    } catch (Exception $e) {

    }
}

echo json_encode($data);
?>