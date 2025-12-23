<?php
session_start();

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

// Dashboard metrics
$metrics = [
    'total_orders' => 0,
    'total_products' => 0,
    'pending_orders' => 0,
    'revenue_today' => 0.0,
];

// Total products for this seller
try {
    $stmt = $con->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $metrics['total_products'] = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

// Total orders for this seller (only approved)
try {
    $stmt = $con->prepare("
        SELECT COUNT(DISTINCT o.id)
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ? AND NOT o.order_status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $metrics['total_orders'] = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

// Pending orders
try {
    $stmt = $con->prepare("
        SELECT COUNT(DISTINCT o.id)
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ? AND o.order_status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $metrics['pending_orders'] = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

// Revenue today (all orders today for this seller)
try {
    $stmt = $con->prepare("
        SELECT COALESCE(SUM(o.total_amount),0)
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ? AND DATE(o.created_at) = CURDATE()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $metrics['revenue_today'] = (float)$stmt->fetchColumn();
} catch (Exception $e) {
    $metrics['revenue_today'] = 0.0;
}

// Fetch today's orders for this seller with buyer username
$recentOrders = [];
try {
    $stmt = $con->prepare("
        SELECT DISTINCT o.id AS order_id, o.buyer_id, u.username, o.total_amount, o.order_status, o.created_at
        FROM orders o
        INNER JOIN users u ON o.buyer_id = u.id
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ? AND DATE(o.created_at) = CURDATE()
        ORDER BY o.id DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentOrders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    --bg-sidebar: #fdf6f4;
    --bg-main: #ffffff;
    --primary-accent: #f8e8e2;
    --text-dark: #4a332d;
    --border-color: #d1b8b0;
}

* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { display:flex; min-height:100vh; background:var(--bg-main); color:var(--text-dark); overflow:hidden; }

aside { width:240px; background:var(--bg-sidebar); border-right:1px solid #eee; display:flex; flex-direction:column; padding:20px 0; }
.profile-section { padding:0 20px 20px; }
.profile-circle { width:70px; height:70px; border-radius:50%; background:#e0e0e0; margin-bottom:10px; }
nav { flex:1; padding:0 10px; }
.nav-item { display:flex; align-items:center; padding:10px 14px; margin-bottom:8px; text-decoration:none; color:var(--text-dark); border-radius:10px; border:1px solid var(--text-dark); background:#fff; font-size:0.9rem; cursor:pointer; }
.nav-item i { margin-right:12px; width:18px; }
.nav-item.active { background-color:var(--primary-accent); box-shadow: inset 4px 0 0 #c08080; }
.logout-btn { padding:20px; text-decoration:none; color:var(--text-dark); font-weight:bold; display:flex; align-items:center; border-top:1px solid #ccc; margin-top:auto; }

main { flex:1; display:flex; flex-direction:column; overflow:auto; }
header { background:var(--primary-accent); padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }

.content-body { flex:1; padding:20px 30px; display:flex; flex-direction:column; gap:20px; }
.grid-container { margin:10px; display:grid; grid-template-columns:2fr 1fr; grid-gap:20px; }
.card { background: #f1f0f0; border-radius:15px; padding:20px; }
.card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.stats-grid { display:grid; grid-template-columns:1fr 1fr; grid-gap:15px; }
.stat-item { background:#F9F9F9; padding:15px; border-radius:10px; }
.stat-item span { font-size:0.8rem; color:#888; display:block; }
.stat-item h3 { font-size:1.5rem; margin-top:5px; }
.status-list { list-style:none; padding-left:0; }
.status-list li { display:flex; justify-content:space-between; padding:10px 0; font-size:0.9rem; }
.status-dot { width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:10px; }
.table-section { margin-top:25px; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid var(--border-color); padding:10px; text-align:center; font-size:0.85rem; }
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
    <a class="nav-item active"><i class="fa-solid fa-house nav-icon"></i> Overview</a>
    <a href="./products.php" class="nav-item"><i class="fa-solid fa-box nav-icon"></i> Products</a>
    <a href="./orders.php" class="nav-item"><i class="fa-solid fa-cart-shopping nav-icon"></i> Orders</a>
</nav>

<a href="../logout.php?logout=true" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>

<main class="main-content">
    <header>
        <h1>Sales Overview</h1>
    </header>

    <div class="grid-container">
        <section class="card">
            <div class="card-header"><h2>Sales Summary</h2></div>
            <div class="stats-grid">
                <div class="stat-item"><span>Total Products</span><h3><?php echo $metrics['total_products']; ?></h3></div>
                <div class="stat-item"><span>Total Orders</span><h3><?php echo $metrics['total_orders']; ?></h3></div>
                <div class="stat-item"><span>Pending Orders</span><h3><?php echo $metrics['pending_orders']; ?></h3></div>
                <div class="stat-item"><span>Revenue Today</span><h3>₱<?php echo number_format($metrics['revenue_today'],2); ?></h3></div>
            </div>
        </section>

        <section class="card">
            <h2>Order Status</h2>
            <ul class="status-list">
                <li><span><span class="status-dot" style="background:#AFAFAF;"></span>Pending</span> <strong><?php echo $metrics['pending_orders']; ?></strong></li>
            </ul>
        </section>
    </div>

<section class="card table-section"> 
    <h2>Today's Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($recentOrders)): ?>
                <?php foreach($recentOrders as $o): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($o['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($o['username']); ?></td>
                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($o['created_at']))); ?></td>
                        <td>₱<?php echo number_format((float)$o['total_amount'],2); ?></td>
                        <td><?php echo htmlspecialchars($o['order_status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No orders today.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

</main>

</body>
</html>
