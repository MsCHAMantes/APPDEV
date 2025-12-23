<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

/* ======================
   DASHBOARD METRICS
====================== */
$metrics = [
    'total_users'    => 0,
    'total_orders'   => 0,
    'total_products' => 0,
    'total_revenue'  => 0.0
];

// Total Users
try {
    $metrics['total_users'] = (int)$con->query(
        "SELECT COUNT(*) FROM users"
    )->fetchColumn();
} catch (Exception $e) {}

// Total Orders
try {
    $metrics['total_orders'] = (int)$con->query(
        "SELECT COUNT(*) FROM orders"
    )->fetchColumn();
} catch (Exception $e) {}

// Total Products
try {
    $metrics['total_products'] = (int)$con->query(
        "SELECT COUNT(*) FROM products"
    )->fetchColumn();
} catch (Exception $e) {}

// Total Revenue (Completed Orders)
try {
    $metrics['total_revenue'] = (float)$con->query(
        "SELECT COALESCE(SUM(total_price),0) 
         FROM orders 
         WHERE status='completed'"
    )->fetchColumn();
} catch (Exception $e) {}

/* ======================
   RECENT ORDERS
====================== */
$recentOrders = [];
try {
    $stmt = $con->query(
        "SELECT id, user_id, total_price, status, created_at
         FROM orders
         ORDER BY id DESC
         LIMIT 5"
    );
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

/* ======================
   ACTIVITY LOGS
====================== */
$activityLogs = [];
try {
    $stmt = $con->query(
        "SELECT al.*, u.username 
         FROM activity_logs al
         LEFT JOIN users u ON al.user_id = u.user_id
         ORDER BY al.created_at DESC
         LIMIT 10"
    );
    $activityLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $activityLogs = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    --bg-sidebar: #fdf6f4;
    --bg-main: #ffffff;
    --primary-accent: #f8e8e2;
    --text-dark: #4a332d;
    --border-color: #d1b8b0;
}
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
body {
    display:flex;
    height:100vh;
    background:var(--bg-main);
    color:var(--text-dark);
    overflow:hidden;
}

/* SIDEBAR */
aside {
    width:240px;
    background:var(--bg-sidebar);
    border-right:1px solid #eee;
    display:flex;
    flex-direction:column;
    padding:20px 0;
}
.profile-section {
    padding:0 20px 20px;
}
.profile-circle {
    width:70px;
    height:70px;
    border-radius:50%;
    background:#e0e0e0;
    margin-bottom:10px;
}
nav {
    flex:1;
    padding:0 10px;
}
.nav-item {
    display:flex;
    align-items:center;
    padding:10px 14px;
    margin-bottom:8px;
    text-decoration:none;
    color:var(--text-dark);
    border-radius:10px;
    border:1px solid var(--text-dark);
    background:#fff;
    font-size:0.9rem;
}
.nav-item i {
    margin-right:12px;
    width:18px;
}
.nav-item.active {
    background-color:var(--primary-accent);
    box-shadow: inset 4px 0 0 #c08080;
}
.logout-btn {
    padding:20px;
    text-decoration:none;
    color:var(--text-dark);
    font-weight:bold;
    display:flex;
    align-items:center;
    border-top:1px solid #ccc;
    margin-top:auto;
}

/* MAIN */
main {
    flex:1;
    display:flex;
    flex-direction:column;
}
header {
    background:var(--primary-accent);
    padding:15px 30px;
}
.content-body {
    flex:1;
    padding:20px 30px;
}
.stats-grid {
    display:grid;
    grid-template-columns: repeat(4,1fr);
    gap:15px;
    margin-bottom:25px;
}
.stat-card {
    background:#f1f0f0ff;
    padding:20px;
    border-radius:15px;
}
.stat-card h4 {
    font-size:0.85rem;
    color:#777;
}
.stat-card .value {
    font-size:1.6rem;
    margin-top:8px;
}
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    border:1px solid var(--border-color);
    padding:10px;
    font-size:0.85rem;
    text-align:center;
}
.table-wrapper {
    max-height: 300px; /* adjust height as needed */
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: 10px;
}

.table-wrapper table {
    width: 100%;
    border-collapse: collapse;
}

.table-wrapper th, .table-wrapper td {
    border: 1px solid var(--border-color);
    padding: 8px;
    text-align: center;
    font-size: 0.85rem;
}

</style>
</head>

<body>

<aside>
    <div class="profile-section">
        <div class="profile-circle"></div>
        <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <p><?php echo htmlspecialchars($_SESSION['role']); ?></p>
    </div>

    <nav>
        <a class="nav-item active">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <a href="./users.php" class="nav-item">
            <i class="fa-solid fa-users"></i> Users
        </a>
        <a href="./products.php" class="nav-item">
            <i class="fa-solid fa-box"></i> Products
        </a>
        <a href="./orders.php" class="nav-item">
            <i class="fa-solid fa-cart-shopping"></i> Orders
        </a>
    </nav>

    <a href="../logout.php?logout=true" class="logout-btn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</aside>

<main>
    <header>
        <h1>Admin Overview</h1>
    </header>

    <div class="content-body">

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Users</h4>
                <div class="value"><?php echo $metrics['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h4>Total Orders</h4>
                <div class="value"><?php echo $metrics['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h4>Total Products</h4>
                <div class="value"><?php echo $metrics['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <h4>Total Revenue</h4>
                <div class="value">₱<?php echo number_format($metrics['total_revenue'],2); ?></div>
            </div>
        </div>

<div class="stat-card">
    <h3>Activity Logs</h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($activityLogs)): ?>
                <?php foreach ($activityLogs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                        <td><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($log['role']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No activity yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



    </div>
</main>

</body>
</html>
