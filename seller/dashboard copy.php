<?php
    session_start();

    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
        header("Location: ../login.php");
        exit;
    }
    
    require '../config/db.php';

    // Dashboard metrics
    $metrics = [
        'total_users' => 0,
        'total_products' => 0,
        'pending_orders' => 0,
        'revenue_today' => 0.0,
    ];

    try {
        $metrics['total_users'] = (int)$con->query("SELECT COUNT(*) FROM users")->fetchColumn();
    } catch (Exception $e) {}

    try {
        $metrics['total_products'] = (int)$con->query("SELECT COUNT(*) FROM products")->fetchColumn();
    } catch (Exception $e) {}

    try {
        $metrics['pending_orders'] = (int)$con->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {}

    try {
        // Prefer total_amount; fallback to total
        $stmt = $con->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE()");
        $metrics['revenue_today'] = (float)$stmt->fetchColumn();
        if ($metrics['revenue_today'] === 0.0) {
            $stmt = $con->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE()");
            $metrics['revenue_today'] = (float)$stmt->fetchColumn();
        }
    } catch (Exception $e) {
        try {
            $stmt = $con->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE()");
            $metrics['revenue_today'] = (float)$stmt->fetchColumn();
        } catch (Exception $e2) {}
    }
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
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
            <h2>Overview</h2>
            <p>Quick summary of the platform.</p>
            <ul>
                <li>Total Users: <?php echo htmlspecialchars((string)$metrics['total_users']); ?></li>
                <li>Total Products: <?php echo htmlspecialchars((string)$metrics['total_products']); ?></li>
                <li>Pending Orders: <?php echo htmlspecialchars((string)$metrics['pending_orders']); ?></li>
                <li>Revenue (Today): ₱<?php echo htmlspecialchars(number_format((float)$metrics['revenue_today'], 2)); ?></li>
            </ul>
        </section>

        <section>
            <h2>Management Shortcuts</h2>
            <ul>
                <li><a href="./users.php">Manage Users</a></li>
                <li><a href="./products.php">Manage Products</a></li>
                <li><a href="./orders.php">Manage Orders</a></li>
                <li><a href="./reports.php">View Reports</a></li>
            </ul>
        </section>

        <section>
            <h2>Recent Activity</h2>
            <p>No recent activity to show.</p>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Admin Panel</p>
    </footer>
</body>
</html>