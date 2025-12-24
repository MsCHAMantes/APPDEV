<?php
session_start();
require './config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$items = [];
$totals = ['subtotal' => 0.0, 'count' => 0];
try {
    $stmt = $con->prepare("SELECT c.product_id, c.quantity, p.name, p.price, p.quantity AS stock
                           FROM cart c JOIN products p ON c.product_id = p.id
                           WHERE c.user_id = :uid");
    $stmt->execute([':uid' => $userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $row) {
        $totals['subtotal'] += ((float)$row['price']) * (int)$row['quantity'];
        $totals['count'] += (int)$row['quantity'];
    }
} catch (Exception $e) {
    $items = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Shopping Cart</title>
<style>
body {
    background-color: #fff8f6;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
main {
    flex: 1;
    max-width: 900px;
    margin: 20px auto 140px; 
    padding: 0 20px;
}
h1 {
    text-align: center;
    font-weight: 300;
    font-size: 2rem;
    margin-bottom: 30px;
    color: #5e3e3e;
}
table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
}
th, td {
    padding: 14px 12px;
    border-bottom: 1px solid #ddd;
    vertical-align: middle;
}
th {
    text-align: left;
    font-weight: 500;
    color: #5e3e3e;
}
.product-info b { display: block; font-size: 1rem; }
.stock-count { font-size: 0.85rem; color: #888; }
.price-col, .total-col { text-align: right; width: 120px; }
.quantity-col { text-align: center; width: 80px; }
input[type="number"] { width: 60px; padding: 5px; border-radius: 6px; border: 1px solid #ccc; font-size: 1rem; }
.btn-pill {
    background-color: #fffaf9;
    border: 1px solid #b38b8b;
    color: #5e3e3e;
    padding: 12px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-size: 1rem;
    display: inline-block;
    transition: 0.3s;
    cursor: pointer;
}
.btn-pill:hover { background-color: #f5ecea; }
.btn-remove {
    background-color: #2b6df2;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
}
.cart-summary {
    position: fixed;
    bottom: 0;
    left: 30;
    width: 100%;
    background: #fff;
    border-top: 2px solid #ccc;
    padding: 16px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
    z-index: 999;
}
.cart-summary .totals {
    text-align: right;
    font-size: 1rem;
    margin-right: 50px; 
}

.cart-summary .totals div { margin-bottom: 6px; }
.cart-summary .totals a.btn-pill { margin-left: 12px; }
</style>
</head>
<body>

<main>
    <h1>Your Shopping Cart</h1>
<form id="update-form" action="update_cart.php" method="POST">
    <table>
        <thead>
            <tr>
                    <th>Product</th>
                    <th class="price-col">Price</th>
                    <th class="quantity-col">Quantity</th>
                    <th class="total-col">Line Total</th>
                    <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $row): ?>
<?php $line_total = ((float)$row['price']) * (int)$row['quantity']; ?>
            <tr>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td>₱<?= number_format($row['price'], 2); ?></td>
                <td>
                    <input type="number" name="qty[<?= $row['product_id']; ?>]" value="<?= $row['quantity']; ?>" min="1">
                </td>
                <td class="total-col">₱<?= number_format($line_total, 2); ?></td>
                <td>
                    <form action="update_cart.php" method="POST" style="display:inline;">
                        <input type="hidden" name="remove_id" value="<?= $row['product_id']; ?>">
                        <button type="submit" class="btn-remove">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</form>
</main>
<div class="cart-summary">
    <div>
        <a href="product.php" class="btn-pill">Continue Shopping</a>
    </div>
    <div class="totals">
        <div>Items: <?= htmlspecialchars($totals['count']); ?></div>
        <div><strong>Subtotal: ₱<?= number_format((float)$totals['subtotal'], 2); ?></strong></div>
        <div style="margin-top: 6px;">      
            <a href="checkout.php" class="btn-pill">Checkout</a>
        </div>
    </div>
</div>

</body>
</html>
