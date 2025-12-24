<?php
session_start();
require './config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$items = [];
$totals = ['subtotal' => 0.0, 'count' => 0];
$shipping_fee = 50.00;
$isSingleProduct = false;
$backLink = 'cart.php'; 

if (isset($_GET['product_id'])) {
    $productId = (int)$_GET['product_id'];
    $stmt = $con->prepare("SELECT id, name, price FROM products WHERE id = ? AND status='active'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    $items[] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => 1
    ];

    $isSingleProduct = true;
    $backLink = "product.php?id={$productId}"; 

} else {
    try {
        $stmt = $con->prepare("
            SELECT c.product_id, c.quantity, p.name, p.price
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $items = [];
    }
}

foreach ($items as $row) {
    $totals['subtotal'] += ((float)$row['price']) * (int)$row['quantity'];
    $totals['count'] += (int)$row['quantity'];
}
$totals['total'] = $totals['subtotal'] + $shipping_fee;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['shipping_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if (!in_array($payment_method, ['Cash on Delivery', 'GCASH'])) {
        die("Invalid payment method.");
    }

    try {
        $con->beginTransaction();


 $stmt = $con->prepare("
    INSERT INTO orders 
    (buyer_id, total_amount, shipping_address, payment_method, order_status, payment_status, created_at, updated_at) 
    VALUES (:buyer_id, :total_amount, :shipping_address, :payment_method, 'pending', 'pending', NOW(), NOW())
");

$stmt->execute([
    ':buyer_id' => $userId,
    ':total_amount' => $totals['total'], 
    ':shipping_address' => $address,
    ':payment_method' => $payment_method
]);


        $orderId = $con->lastInsertId();

        $itemStmt = $con->prepare("
    INSERT INTO order_items
    (order_id, product_id, quantity, unit_price, total_price)
    VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)
");

foreach ($items as $row) {
    $qty = (int)$row['quantity'];
    $price = (float)$row['price'];
    $itemStmt->execute([
        ':order_id' => $orderId,
        ':product_id' => (int)$row['product_id'],
        ':quantity' => $qty,
        ':unit_price' => $price,
        ':total_price' => $qty * $price
    ]);
}

        if (!$isSingleProduct) {
            $stmt = $con->prepare("DELETE FROM cart WHERE user_id = :uid");
            $stmt->execute([':uid' => $userId]);
        }

        $con->commit();

        header("Location: order_success.php?order_id=$orderId");
        exit;

    } catch (Exception $e) {
        $con->rollBack();
        die("Error placing order: " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
<style>
body {
    background-color: #fff8f6;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

main {
    max-width: 900px;
    margin: 30px auto 140px;
    padding: 0 20px;
}

h1 {
    text-align: center;
    font-weight: 300;
    font-size: 2rem;
    margin-bottom: 30px;
    color: #5e3e3e;
}

.checkout-container {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
}

.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    padding: 24px;
    flex: 1;
    min-width: 280px;
}

.card h2 {
    font-size: 1.1rem;
    margin-top: 0;
    margin-bottom: 20px;
    color: #5e3e3e;
    font-weight: 500;
}

label {
    display: block;
    font-size: 0.85rem;
    margin-bottom: 6px;
    color: #444;
}

textarea {
    width: 100%;
    height: 110px;
    border-radius: 8px;
    border: 1px solid #ccc;
    padding: 10px;
    font-size: 0.95rem;
    margin-bottom: 18px;
    resize: none;
}

select {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
    background-color: #fafafa;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

th, td {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

th {
    text-align: left;
    color: #5e3e3e;
    font-weight: 500;
}

.text-right {
    text-align: right;
}

.summary-row td {
    font-weight: 600;
     
}

.checkout-footer {
    position: fixed;
    bottom: 0;
    width: 70%;
    background: #fff;
    border-top: 2px solid #ddd;
    box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
    padding: 18px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-pill {
    background-color: #fffaf9;
    border: 1px solid #b38b8b;
    color: #5e3e3e;
    padding: 12px 34px;
    border-radius: 50px;
    font-size: 1rem;
    cursor: pointer;
    transition: 0.3s;
    text-decoration: none;
}

.btn-pill:hover {
    background-color: #f5ecea;
}

.total-box {
    text-align: right;
    font-size: 1rem;
    margin-right: 50px;
}

.total-box div {
    margin-bottom: 6px;
}
</style>
</head>
<body>

<main>
    <h1>Checkout</h1>
<form method="POST" class="checkout-container">
    <div class="card">
        <h2>Shipping Information</h2>

        <label>Shipping Address</label>
        <textarea name="shipping_address" placeholder="Enter complete delivery address" required></textarea>

        <label>Payment Method</label>
        <select name="payment_method" required>
            <option value="Cash on Delivery">Cash on Delivery</option>
            <option value="GCASH">GCASH</option>
        </select>
    </div>
    <div class="card">
        <h2>Order Summary</h2>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td class="text-right"><?= (int)$row['quantity']; ?></td>
                        <td class="text-right">₱<?= number_format($row['price'] * $row['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Your cart is empty.</td>
                    </tr>
                <?php endif; ?>

                <tr class="summary-row">
                    <td colspan="2" class="text-right">Subtotal</td>
                    <td class="text-right">₱<?= number_format($totals['subtotal'], 2); ?></td>
                </tr>
                <tr class="summary-row">
                    <td colspan="2" class="text-right">Shipping</td>
                    <td class="text-right">₱<?= number_format($shipping_fee, 2); ?></td>
                </tr>
                <tr class="summary-row">
                    <td colspan="2" class="text-right">Total</td>
                    <td class="text-right">₱<?= number_format($totals['total'], 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
<div class="checkout-footer">
    <a href="<?= htmlspecialchars($backLink); ?>" class="btn-pill">
        <?= $isSingleProduct ? 'Cancel Order' : 'Back to Cart'; ?>
    </a>

    <div class="total-box">
        <div><strong>Total: ₱<?= number_format($totals['total'], 2); ?></strong></div>
        <button type="submit" class="btn-pill">Place Order</button>
    </div>
</div>


</form>



      
</main>


</body>
</html>

