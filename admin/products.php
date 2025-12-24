<?php
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


require '../config/db.php';
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


try {
$products = $con->query("
    SELECT p.id, p.name, p.price, p.stock, p.sold_count, p.status, p.image_path, p.created_at,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products Management</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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

main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden; 
}

.content-body {
    flex: 1;
    padding: 20px 30px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    overflow-y: auto; 
    overflow-x: hidden;
}

header {
    background:var(--primary-accent); padding:15px 30px;
    display:flex; align-items:center; gap:12px;
}

.img-box{
    width:60px;
    height:60px;
    background:#d9d9d9;
    margin:0 auto;
}
.action-container {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 10px;
}

.btn-action {
    padding: 10px 15px;
    border-radius: 4px;
    border: 1px solid #8c5e58;
    background: white;
    color: #8c5e58;
    cursor: pointer;
    font-size: 0.85rem;
    transition: 0.2s;
}

.btn-action:hover {
    background-color: #fdf6f4;
}
table th,
table td {
    text-align: center;
    vertical-align: middle;
}
</style>
</head>

<body>
<aside>
    <div class="profile-section">
        <div class="profile-circle"></div>
        <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <p><?php echo htmlspecialchars($_SESSION['role']);?></p>
    </div>

    <nav>
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i> Overview</a>
        <a href="users.php" class="nav-item"><i class="fa-solid fa-user"></i> Users</a>
        <a href="products.php" class="nav-item active"><i class="fa-solid fa-box"></i> Products</a>
        <a href="orders.php" class="nav-item"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
    </nav>

    <a href="../logout.php?logout=true" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>

<main>
<header>
    <h1 id="pageTitle">Product Management</h1>
</header>

<div id="mainContent" class="content-body" style="padding: 30px 40px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <p style="font-size:0.95rem;color:#555;">All products: <strong><?= count($products) ?></strong></p>
        <button id="manageProductsBtn" style="
            display:flex;
            align-items:center;
            gap:10px;
            background:#fff;
            border:1px solid #c08080;
            padding:10px 18px;
            border-radius:8px;
            cursor:pointer;
            font-weight:500;
        ">
            <i class="fa-solid fa-folder-gear"></i>
            Manage Products
        </button>
    </div>

    <div style="width:100%;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;border:1px solid #444;">
            <thead>
                <tr>
                    <th style="border:1px solid #444;padding:15px;">Image</th>
                    <th style="border:1px solid #444;padding:15px;">Name</th>
                    <th style="border:1px solid #444;padding:15px;">Price</th>
                    <th style="border:1px solid #444;padding:15px;">Category</th>
                    <th style="border:1px solid #444;padding:15px;">Stock</th>
                    <th style="border:1px solid #444;padding:15px;">Sold</th>
                    <th style="border:1px solid #444;padding:15px;">Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products): ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
<td style="border:1px solid #444;padding:15px; text-align:center;">
    <?php if (!empty($p['image_path'])): ?>
        <img src="../uploads/<?= htmlspecialchars($p['image_path']) ?>" 
             alt="<?= htmlspecialchars($p['name']) ?>" 
             style="width:60px; height:60px; object-fit:cover; border-radius:5px; margin:0 auto;">
    <?php else: ?>
        <div class="img-box"></div>
    <?php endif; ?>
</td>

                        <td style="border:1px solid #444;padding:15px;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="border:1px solid #444;padding:15px;">₱<?= number_format($p['price'],2) ?></td>
                        <td style="border:1px solid #444;padding:15px;"><?= htmlspecialchars($p['category_name'] ?? '') ?></td>
                        <td style="border:1px solid #444;padding:15px;"><?= $p['stock'] ?></td>
                        <td style="border:1px solid #444;padding:15px;"><?= $p['sold_count'] ?></td>
                        <td style="border:1px solid #444;padding:15px;"><?= $p['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<script>
const manageBtn = document.getElementById('manageProductsBtn');
const mainContent = document.getElementById('mainContent');
const header = document.querySelector('header');

manageBtn.addEventListener('click', () => {
    if (!document.querySelector('#backArrow')) {
        const backArrow = document.createElement('i');
        backArrow.id = 'backArrow';
        backArrow.className = 'fa-solid fa-arrow-left';
        backArrow.style.cursor = 'pointer';
        backArrow.style.marginRight = '10px';
        backArrow.onclick = () => location.reload();
        header.insertBefore(backArrow, header.firstChild);
    }

    fetch('get_products.php')
        .then(res => res.json())
        .then(products => {
            let pendingProducts = products.filter(p => (p.status ?? 'pending') === 'pending');
            let html = `
                <p style="font-size:0.95rem;color:#555;">All Pending Products: <strong>${pendingProducts.length}</strong></p>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;border:1px solid #444;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #444;padding:15px;">Image</th>
                                <th style="border:1px solid #444;padding:15px;">Name</th>
                                <th style="border:1px solid #444;padding:15px;">Price</th>
                                <th style="border:1px solid #444;padding:15px;">Category</th>
                                <th style="border:1px solid #444;padding:15px;">Status</th>
                                <th style="border:1px solid #444;padding:15px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${pendingProducts.map(p => `
                                <tr data-id="${p.id}">
                                    <td style="border:1px solid #444;padding:15px;">
                                        ${p.image_path ? `<img src="../uploads/${p.image_path}" style="width:60px;height:60px;object-fit:cover;margin:0 auto;border-radius:5px;">` : '<div class="img-box"></div>'}
                                    </td>
                                    <td style="border:1px solid #444;padding:15px;">${p.name}</td>
                                    <td style="border:1px solid #444;padding:15px;">₱${Number(p.price).toFixed(2)}</td>
                                    <td style="border:1px solid #444;padding:15px;">${p.category_name || ''}</td>
                                    <td style="border:1px solid #444;padding:15px;">${(p.status ?? 'Pending').charAt(0).toUpperCase() + (p.status ?? 'pending').slice(1)}</td>
                                    <td class="action-container">
                                        <button class="btn-action" data-id="${p.id}" data-action="reject">Reject</button>
                                        <button class="btn-action" data-id="${p.id}" data-action="approve">Approve</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            mainContent.innerHTML = html;

            mainContent.querySelectorAll('.btn-action').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.dataset.id;
                    const action = this.dataset.action;

                    fetch('update_product_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `product_id=${productId}&action=${action}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success){
                            const row = this.closest('tr');
                            row.remove();
                        } else {
                            alert(data.message || 'Failed to update product status.');
                        }
                    })
                    .catch(err => console.error(err));
                });
            });
        });
});
</script>



</body>
</html>