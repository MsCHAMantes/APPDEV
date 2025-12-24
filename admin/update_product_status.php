<?php 
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}


$product_id = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? null;


if (!$product_id || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {

    $new_status = $action === 'approve' ? 'active' : 'declined';

    $stmt = $con->prepare("UPDATE products SET status = :status WHERE id = :id");
    $stmt->execute([
        'status' => $new_status,
        'id' => $product_id
    ]);


    echo json_encode(['success' => true, 'status' => $new_status]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
