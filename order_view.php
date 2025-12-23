<?php
    session_start();
    require './config/db.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: ./login.php');
        exit;
    }

    $userId = (int)$_SESSION['user_id'];
    $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($orderId <= 0) {
        header('Location: ./orders.php');
        exit;
    }

    // Fetch order ensuring it belongs to the user
    $order = null; $items = [];
    try {
        $stmt = $con->prepare("SELECT id, total_amount, order_status, payment_status, shipping_address, payment_method, created_at
                               FROM orders WHERE id = :id AND buyer_id = :uid");
        $stmt->execute([':id' => $orderId, ':uid' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) { header('Location: ./orders.php'); exit; }

        $it = $con->prepare("SELECT oi.product_id, oi.quantity, oi.unit_price, oi.total_price, p.name
                             FROM order_items oi JOIN products p ON oi.product_id = p.id
                             WHERE oi.order_id = :oid");
        $it->execute([':oid' => $orderId]);
        $items = $it->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        header('Location: ./orders.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars((string)$orderId); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; }
        header, footer { background: #2563eb; color: #fff; padding: 12px 16px; }
        nav a { color: #fff; margin-right: 10px; text-decoration: none; }
        main { max-width: 960px; margin: 16px auto; padding: 0 12px; }
        .card { background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; }
        .right { text-align: right; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <header>
        <strong>Order #<?php echo htmlspecialchars((string)$orderId); ?></strong>
        <nav style="float:right;">
            <a href="./index.php">Home</a>
            <a href="./shop.php">Shop</a>
            <a href="./cart.php">Cart</a>
            <a href="./orders.php">My Orders</a>
            <a href="./index.php">Dashboard</a>
            <a href="./logout.php?logout=true">Logout</a>
        </nav>
        <div style="clear:both"></div>
    </header>

    <main>
        <div class="card" style="margin-bottom: 12px;">
            <div><strong>Status:</strong> <?php echo htmlspecialchars((string)$order['order_status']); ?> (Payment: <?php echo htmlspecialchars((string)$order['payment_status']); ?>)</div>
            <div class="muted">Placed: <?php echo htmlspecialchars((string)$order['created_at']); ?></div>
            <div><strong>Total:</strong> ₱<?php echo htmlspecialchars(number_format((float)$order['total_amount'], 2)); ?></div>
            <div><strong>Shipping:</strong> <?php echo nl2br(htmlspecialchars((string)$order['shipping_address'])); ?></div>
            <div><strong>Payment Method:</strong> <?php echo htmlspecialchars((string)$order['payment_method']); ?></div>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="right">Qty</th>
                        <th class="right">Unit Price</th>
                        <th class="right">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['name']); ?></td>
                            <td class="right"><?php echo (int)$it['quantity']; ?></td>
                            <td class="right">₱<?php echo htmlspecialchars(number_format((float)$it['unit_price'], 2)); ?></td>
                            <td class="right">₱<?php echo htmlspecialchars(number_format((float)$it['total_price'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <small>&copy; <?php echo date('Y'); ?> My Shop</small>
    </footer>
</body>
</html>


