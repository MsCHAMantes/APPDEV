<?php 
session_start();
require '../config/db.php';

// ==============================
// AUTH CHECK
// ==============================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// ==============================
// GET POST DATA
// ==============================
$product_id = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? null;

// Validate input
if (!$product_id || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // ==============================
    // UPDATE STATUS BASED ON ACTION
    // ==============================
    $new_status = $action === 'approve' ? 'active' : 'declined';

    $stmt = $con->prepare("UPDATE products SET status = :status WHERE id = :id");
    $stmt->execute([
        'status' => $new_status,
        'id' => $product_id
    ]);

    // ==============================
    // RETURN SUCCESS
    // ==============================
    echo json_encode(['success' => true, 'status' => $new_status]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
