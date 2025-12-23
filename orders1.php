<?php
    session_start();

    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

    require '../config/db.php';

    // Basic fetch for orders (align with schema buyer_id/order_status)
    $orders = [];
    try {
        $stmt = $con->query("SELECT id AS order_id, buyer_id, total_amount, order_status, created_at FROM orders ORDER BY id DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        try {
            // Alternative common schema
            $stmt = $con->query("SELECT order_id, customer_name, order_date, total, status FROM orders ORDER BY order_id DESC");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e2) {
            $orders = [];
        }
    }
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders</title>
</head>
<body>
    <header>
        <h1>Orders</h1>
        <p>Signed in as: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
    </header>

    <nav>
        <ul>
            <li><a href="./index.php">Overview</a></li>
            <li><a href="./users.php">Users</a></li>
            <li><a href="./categories.php">Categories</a></li>
            <li><a href="./products.php">Products</a></li>
            <li><a href="./orders.php">Orders</a></li>
            <li><a href="./reports.php">Reports</a></li>
            <li><a href="./settings.php">Settings</a></li>
            <li><a href="../logout.php?logout=true">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <h2>Order Management</h2>
            <h3>Search Orders</h3>
            <form action="#" method="GET">
                <label>Order ID:</label>
                <input type="text" name="order_id">
                <br>
                <label>Status:</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <br>
                <button type="submit">Filter</button>
            </form>

            <h3>Recent Orders</h3>
            <table border="1" cellpadding="6" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer/User</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(isset($o['order_id']) ? $o['order_id'] : (isset($o['id']) ? $o['id'] : '')); ?></td>
                                <td><?php echo htmlspecialchars(isset($o['customer_name']) ? $o['customer_name'] : (isset($o['buyer_id']) ? $o['buyer_id'] : '')); ?></td>
                                <td><?php echo htmlspecialchars(isset($o['order_date']) ? $o['order_date'] : (isset($o['created_at']) ? $o['created_at'] : '')); ?></td>
                                <td><?php echo htmlspecialchars(isset($o['total']) ? $o['total'] : (isset($o['total_amount']) ? $o['total_amount'] : '')); ?></td>
                                <td><?php echo htmlspecialchars(isset($o['order_status']) ? $o['order_status'] : (isset($o['status']) ? $o['status'] : '')); ?></td>
                                <td>
                                    <a href="#">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Admin Panel</p>
    </footer>
</body>
</html>


