<?php
session_start();
require './config/db.php';

if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$orderId = (int)$_GET['order_id'];

// Fetch order info
$stmt = $con->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

// Fetch order items
// Fetch order items with product names
$stmt = $con->prepare("
    SELECT oi.*, p.name AS product_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = :order_id
");
$stmt->execute([':order_id' => $orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Success</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #fff8f6;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 700px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
h1 { color: #5e3e3e; text-align: center; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: left;
}
.summary {
    margin-top: 20px;
    text-align: right;
    font-weight: bold;
}
.btn {
    display: inline-block;
    margin-top: 20px;
    background-color: #fffaf9;
    border: 1px solid #b38b8b;
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    color: #5e3e3e;
}
</style>
</head>
<body>

<div class="container">
    <h1>✅ Order Placed Successfully!</h1>
    <p>Order ID: <strong>#<?= $order['id']; ?></strong></p>
    <p>Payment Method: <strong><?= htmlspecialchars($order['payment_method']); ?></strong></p>
    <p>Shipping Address: <?= htmlspecialchars($order['shipping_address']); ?></p>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']); ?></td>
<td><?= (int)$row['quantity']; ?></td>
<td>₱<?= number_format((float)$row['total_price'], 2); ?></td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary">
        Total: ₱<?= number_format((float)$order['total_amount'], 2); ?>
    </div>

    <a href="product.php" class="btn">Back to Shop</a>
</div>

</body>
</html>
