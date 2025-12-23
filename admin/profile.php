<?php
// dashboard.php
session_start();
// OPTIONAL: protect admin page
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: ../login.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

/* SIDEBAR */
aside { width:240px; background:var(--bg-sidebar); border-right:1px solid #eee; display:flex; flex-direction:column; padding:20px 0; }
.profile-section { padding:0 20px 20px; }
.profile-circle { width:70px; height:70px; border-radius:50%; background:#e0e0e0; margin-bottom:10px; }
nav { flex:1; padding:0 10px; }

/* NAV ITEM */
.nav-item {
    display:flex; align-items:center; padding:10px 14px; margin-bottom:8px;
    text-decoration:none; color:var(--text-dark);
    border-radius:10px; border:1px solid var(--text-dark);
    background:#fff; font-size:0.9rem; cursor:pointer;
}
.nav-item i { margin-right:12px; width:18px; }
.nav-item.active { background-color:var(--primary-accent); box-shadow: inset 4px 0 0 #c08080; }

/* LOGOUT */
.logout-btn {
    padding:20px; text-decoration:none; color:var(--text-dark);
    font-weight:bold; display:flex; align-items:center;
    border-top:1px solid #ccc; margin-top:auto;
}

/* MAIN */
main { flex:1; display:flex; flex-direction:column; }
header {
    background:var(--primary-accent); padding:15px 30px;
    display:flex; justify-content:space-between; align-items:center;
}

/* CONTENT */
.content-body { flex:1; padding:20px 30px; display:flex; flex-direction:column; gap:20px; }

/* TABLE STYLING */
.table-container { overflow-x:auto; }
table { width:100%; border-collapse:collapse; font-size:0.85rem; }
th { background:#c08080; color:#fff; padding:6px; }
td { padding:6px; border:1px solid #ccc; text-align:center; }
.img-cell { width:50px; height:50px; background:#e0e0e0; border-radius:5px; margin:auto; }

/* CONTROLS */
.top-controls { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.stats-text { font-weight:bold; }
.filter-btn { padding:6px 12px; border:none; background:#c08080; color:#fff; border-radius:5px; cursor:pointer; }
.filter-btn i { margin-right:6px; }
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
        <a href="products.php" class="nav-item "><i class="fa-solid fa-box"></i> Products</a>
        <a href="orders.php" class="nav-item"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
        <a href="profile.php" class="nav-item active"><i class="fa-solid fa-id-card"></i> Profile</a>
    </nav>

    <a href="../logout.php?logout=true" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>

<main class="main-content"> 
    <header class="header-bar">
        <h1>Admin Profile</h1>
    </header>
</main>

</body>
</html>
