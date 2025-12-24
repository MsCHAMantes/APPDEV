<?php
session_start();
require './config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

if (isset($_POST['order_id'])) {
    $orderId = (int)$_POST['order_id'];

    try {
        $stmt = $con->prepare("UPDATE orders SET order_status='Cancelled' WHERE id=:id AND buyer_id=:uid");
        $stmt->execute([':id' => $orderId, ':uid' => $userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order not found or already cancelled']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No order ID']);
}
