<?php
session_start();

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'], $_POST['ajax'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status'];

    $stmt = $con->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $success = $stmt->execute([$newStatus, $orderId]);

    echo json_encode(['success' => $success, 'new_status' => $newStatus]);
    exit;
}

$orders = [];
try {
    $stmt = $con->query("SELECT id AS order_id, buyer_id, total_amount, order_status, created_at FROM orders ORDER BY id DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $orders = [];
}

$statuses = ['pending', 'confirmed', 'shipped', 'delivery', 'cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Dashboard - Orders Management</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --bg-sidebar: #fdf6f4;
    --bg-main: #ffffff;
    --primary-accent: #f8e8e2;
    --text-dark: #4a332d;
    --border-color: #d1b8b0;
    --accent-maroon: #b85c55;
}

* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { display:flex; height:100vh; background:var(--bg-main); color:var(--text-dark); overflow:hidden; }
aside { width:240px; background:var(--bg-sidebar); border-right:1px solid #eee; display:flex; flex-direction:column; padding:20px 0; }
.profile-section { padding:0 20px 20px; }
.profile-circle { width:70px; height:70px; border-radius:50%; background:#e0e0e0; margin-bottom:10px; }
nav { flex:1; padding:0 10px; }
.nav-item {
    display:flex; align-items:center; padding:10px 14px; margin-bottom:8px;
    text-decoration:none; color:var(--text-dark);
    border-radius:10px; border:1px solid var(--text-dark);
    background:#fff; font-size:0.9rem; cursor:pointer;
}
.nav-item i { margin-right:12px; width:18px; }
.nav-item.active { background-color:var(--primary-accent); box-shadow: inset 4px 0 0 #c08080; }
.logout-btn {
    padding:20px; text-decoration:none; color:var(--text-dark);
    font-weight:bold; display:flex; align-items:center;
    border-top:1px solid #ccc; margin-top:auto;
}
main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
header { background:var(--primary-accent); padding:15px 30px; display:flex; align-items:center; gap: 15px; }
.header-banner h1 { font-size: 1.5rem; }
.back-btn { background: var(--accent-maroon); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; display: none; }
.content-body { padding: 30px 40px; }
table { width:100%; border-collapse:collapse; border:1.5px solid #333; background:#fff; }
th, td { padding:10px; border:1px solid #ccc; text-align:center; }
th { background:#c08080; color:#fff; }
select { padding:4px 6px; border-radius:4px; }
.status-updated { font-weight:bold; color:green; }
.status-badge { padding:2px 6px; border-radius:4px; color:#fff; font-size:0.85rem; display:inline-block; }
.status-pending { background:#f0ad4e; }
.status-confirmed { background:#0275d8; }
.status-shipped { background:#5bc0de; }
.status-delivery { background:#5cb85c; }
.status-cancelled { background:#d9534f; }
</style>
</head>
<body>

<aside>
    <div class="profile-section">
        <div class="profile-circle"></div>
        <h2><?= htmlspecialchars($_SESSION['username']) ?></h2>
        <p><?= htmlspecialchars($_SESSION['role']) ?></p>
    </div>

<nav>
    <a href="./dashboard.php" class="nav-item">
        <i class="fa-solid fa-house nav-icon"></i> Overview
    </a>
    <a href="./products.php" class="nav-item">
        <i class="fa-solid fa-box nav-icon"></i> Products
    </a>
    <a href="./orders.php" class="nav-item active">
        <i class="fa-solid fa-cart-shopping nav-icon"></i> Orders
    </a>
</nav>

    <a href="../logout.php?logout=true" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>

<main>
<header class="header-banner">
    <h1 id="page-title">Orders Management</h1>
</header>

<div class="content-body">
<p>Total Orders: <?= count($orders) ?></p>

<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer/User</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($orders)): ?>
            <?php foreach($orders as $o): ?>
                <tr id="order-<?= $o['order_id'] ?>">
                    <td><?= htmlspecialchars($o['order_id']) ?></td>
                    <td><?= htmlspecialchars($o['buyer_id']) ?></td>
                    <td><?= htmlspecialchars($o['created_at']) ?></td>
                    <td>₱<?= number_format($o['total_amount'],2) ?></td>
                    <td class="status-text">
                        <span class="status-badge status-<?= strtolower($o['order_status']) ?>"><?= htmlspecialchars($o['order_status']) ?></span>
                    </td>
                    <td>
                        <select class="status-select" data-id="<?= $o['order_id'] ?>">
                            <?php foreach($statuses as $status): ?>
                                <option value="<?= $status ?>" <?= $o['order_status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="status-updated" style="display:none;">Updated</span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">No orders found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</main>

<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const orderId = this.dataset.id;
        const newStatus = this.value;
        const tr = document.getElementById('order-' + orderId);
        const statusText = tr.querySelector('.status-text');
        const updatedMsg = tr.querySelector('.status-updated');

        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('new_status', newStatus);
        formData.append('ajax', '1');

        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    statusText.innerHTML = `<span class="status-badge status-${data.new_status}">${data.new_status}</span>`;
                    updatedMsg.style.display = 'inline';
                    setTimeout(() => updatedMsg.style.display = 'none', 1500);
                }
            });
    });
});
</script>

</body>
</html>
