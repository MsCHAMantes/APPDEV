<?php
session_start();
require './config/db.php';

// ===== AUTH CHECK =====
if (!isset($_SESSION['buyer_id'])) {
    header('Location: ./login.php');
    exit;
}

$userId = (int)$_SESSION['buyer_id']; // Correct session key

// ===== ONLY POST =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./checkout.php');
    exit;
}

// ===== CSRF CHECK =====
$csrf = $_POST['csrf'] ?? '';
if ($csrf !== md5(session_id() . 'checkout')) {
    header('Location: ./checkout.php');
    exit;
}

// ===== INPUTS =====
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$paymentMethod = trim($_POST['payment_method'] ?? 'cod');

if ($shippingAddress === '') {
    header('Location: ./checkout.php');
    exit;
}

try {
    $con->beginTransaction();

    // ===== GET CART ITEMS =====
    $stmt = $con->prepare("
        SELECT c.product_id, c.quantity AS cart_qty, p.name, p.price, p.quantity AS stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = :uid
        FOR UPDATE
    ");
    $stmt->execute([':uid' => $userId]);
    $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart)) {
        $con->rollBack();
        header('Location: ./cart.php');
        exit;
    }

    // ===== CALCULATE TOTAL =====
    $total = 0;
    foreach ($cart as $row) {
        $qty = min((int)$row['cart_qty'], (int)$row['stock']);
        if ($qty <= 0) continue;
        $total += $qty * (float)$row['price'];
    }

    if ($total <= 0) {
        $con->rollBack();
        header('Location: ./cart.php');
        exit;
    }

    // ===== INSERT ORDER =====
    $orderStmt = $con->prepare("
        INSERT INTO orders
        (buyer_id, total_amount, shipping_address, payment_method, order_status, payment_status, created_at, updated_at)
        VALUES
        (:buyer_id, :total_amount, :shipping_address, :payment_method, 'pending', 'pending', NOW(), NOW())
    ");
    $orderStmt->execute([
        ':buyer_id' => $userId, // ✅ Correct variable
        ':total_amount' => $total,
        ':shipping_address' => $shippingAddress,
        ':payment_method' => $paymentMethod
    ]);

    $orderId = (int)$con->lastInsertId();

    // ===== INSERT PAYMENT TRANSACTION =====
    $txnStmt = $con->prepare("
        INSERT INTO payment_transactions
        (order_id, transaction_id, amount, currency, status, payment_method, created_at)
        VALUES
        (:order_id, :transaction_id, :amount, 'PHP', :status, :payment_method, NOW())
    ");

    $txnId = ($paymentMethod === 'cod')
        ? 'COD-' . $orderId . '-' . time()
        : 'PM-' . bin2hex(random_bytes(6));

    $txnStmt->execute([
        ':order_id' => $orderId,
        ':transaction_id' => $txnId,
        ':amount' => $total,
        ':status' => 'pending',
        ':payment_method' => $paymentMethod
    ]);

    // ===== INSERT ORDER ITEMS & DECREMENT STOCK =====
    $itemStmt = $con->prepare("
        INSERT INTO order_items
        (order_id, product_id, product_name, quantity, unit_price, total_price)
        VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price, :total_price)
    ");
    $decStmt = $con->prepare("
        UPDATE products SET quantity = quantity - :q WHERE id = :pid AND quantity >= :q
    ");

    foreach ($cart as $row) {
        $qty = min((int)$row['cart_qty'], (int)$row['stock']);
        if ($qty <= 0) continue;

        $lineTotal = $qty * (float)$row['price'];

        $itemStmt->execute([
            ':order_id' => $orderId,
            ':product_id' => (int)$row['product_id'],
            ':product_name' => $row['name'],
            ':quantity' => $qty,
            ':unit_price' => (float)$row['price'],
            ':total_price' => $lineTotal
        ]);

        $decStmt->execute([
            ':q' => $qty,
            ':pid' => (int)$row['product_id']
        ]);
    }

    // ===== CLEAR CART =====
    $del = $con->prepare("DELETE FROM cart WHERE user_id = :uid");
    $del->execute([':uid' => $userId]);

    $con->commit();

    // ===== REDIRECT TO ORDER VIEW =====
    header('Location: ./order_view.php?id=' . urlencode($orderId));
    exit;

} catch (Exception $e) {
    if ($con->inTransaction()) $con->rollBack();
    die("Error placing order: " . $e->getMessage());
}
