<?php
    session_start();
    require './config/db.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: ./login.php');
        exit;
    }

    $userId = (int)$_SESSION['user_id'];

    // Fetch orders for this buyer. Schema uses buyer_id, order_status, total_amount
    $orders = [];
    try {
        $stmt = $con->prepare("SELECT id, total_amount, order_status, payment_status, created_at
                               FROM orders WHERE buyer_id = :uid ORDER BY id DESC");
        $stmt->execute([':uid' => $userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $orders = [];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; }
        header, footer { background: #2563eb; color: #fff; padding: 12px 16px; }
        nav a { color: #fff; margin-right: 10px; text-decoration: none; }
        main { max-width: 1000px; margin: 16px auto; padding: 0 12px; }
        table { width: 100%; background: #fff; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #eee; text-align: left; }
        .right { text-align: right; }
        .button { background: #2563eb; color: #fff; padding: 6px 10px; border-radius: 6px; text-decoration: none; }
    </style>
</head>
<body>
    <header>
        <strong>My Orders</strong>
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
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th class="right">Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)$o['id']); ?></td>
                            <td><?php echo htmlspecialchars((string)$o['created_at']); ?></td>
                            <td><?php echo htmlspecialchars((string)$o['order_status']); ?></td>
                            <td><?php echo htmlspecialchars((string)$o['payment_status']); ?></td>
                            <td class="right">₱<?php echo htmlspecialchars(number_format((float)$o['total_amount'], 2)); ?></td>
                            <td><a class="button" href="./order_view.php?id=<?php echo htmlspecialchars((string)$o['id']); ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No orders yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <small>&copy; <?php echo date('Y'); ?> My Shop</small>
    </footer>
</body>
</html>


