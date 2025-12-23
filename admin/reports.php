<?php
    session_start();

    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

    require '../config/db.php';

    // Basic counts
    $counts = [
        'users' => 0,
        'products' => 0,
        'orders' => 0,
        'sales' => 0.0,
    ];

    try {
        $counts['users'] = (int)$con->query("SELECT COUNT(*) FROM users")->fetchColumn();
    } catch (Exception $e) {}

    try {
        $counts['products'] = (int)$con->query("SELECT COUNT(*) FROM products")->fetchColumn();
    } catch (Exception $e) {}

    try {
        $counts['orders'] = (int)$con->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    } catch (Exception $e) {}

    try {
        // Try common sales total columns
        $counts['sales'] = (float)$con->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status IN ('confirmed','shipped','delivered') OR payment_status = 'paid'")->fetchColumn();
    } catch (Exception $e) {
        try {
            $counts['sales'] = (float)$con->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status IN ('paid','completed','shipped','processing')")->fetchColumn();
        } catch (Exception $e2) {
            $counts['sales'] = 0.0;
        }
    }
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reports</title>
</head>
<body>
    <header>
        <h1>Reports</h1>
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
            <h2>Reports</h2>
            <h3>Filters</h3>
            <form action="#" method="GET">
                <label>From:</label>
                <input type="date" name="from">
                <br>
                <label>To:</label>
                <input type="date" name="to">
                <br>
                <label>Type:</label>
                <select name="type">
                    <option value="sales">Sales</option>
                    <option value="users">Users</option>
                    <option value="products">Products</option>
                </select>
                <br>
                <button type="submit">Generate</button>
            </form>

            <h3>Summary</h3>
            <ul>
                <li>Total Users: <?php echo htmlspecialchars((string)$counts['users']); ?></li>
                <li>Total Products: <?php echo htmlspecialchars((string)$counts['products']); ?></li>
                <li>Total Orders: <?php echo htmlspecialchars((string)$counts['orders']); ?></li>
                <li>Total Sales: <?php echo htmlspecialchars(number_format((float)$counts['sales'], 2)); ?></li>
            </ul>

            <h3>Details</h3>
            <table border="1" cellpadding="6" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Value</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Example Metric</td>
                        <td>0</td>
                        <td>Replace with dynamic data.</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Admin Panel</p>
    </footer>
</body>
</html>


