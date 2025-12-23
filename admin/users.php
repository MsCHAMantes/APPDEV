<?php
session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

/* =======================
   FETCH USERS
======================= */
$users = [];
try {
    $stmt = $con->query("SELECT user_id, username, email, role FROM users ORDER BY user_id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root { --bg-sidebar: #fdf6f4; --primary-accent: #f8e8e2; --text-dark: #4a332d; }
/* RESET */
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { display:flex; height:100vh; background:#fff; color:var(--text-dark); overflow:hidden; }
/* SIDEBAR */
aside { width:240px; background:var(--bg-sidebar); border-right:1px solid #eee; display:flex; flex-direction:column; padding:20px 0; }
/* PROFILE */
.profile-section { padding:0 20px 20px; }
.profile-circle { width:70px; height:70px; border-radius:50%; background:#e0e0e0; margin-bottom:10px; }
/* NAV */
nav { flex:1; padding:0 10px; }
.nav-item { display:flex; align-items:center; padding:10px 14px; margin-bottom:8px; text-decoration:none; color:var(--text-dark); border-radius:10px; border:1px solid var(--text-dark); background:#fff; font-size:.9rem; cursor:pointer; }
.nav-item i { margin-right:12px; width:18px; }
.nav-item.active { background-color:var(--primary-accent); box-shadow: inset 4px 0 0 #c08080; }
/* LOGOUT */
.logout-btn { padding:20px; text-decoration:none; color:var(--text-dark); font-weight:bold; display:flex; align-items:center; border-top:1px solid #ccc; margin-top:auto; }
/* MAIN */
main{flex:1;display:flex;flex-direction:column;}
header{ background:var(--primary-accent); padding:15px 30px; display:flex; align-items:center; gap:10px; }
/* CONTENT */
.content-body{ flex:1; padding:20px 30px; display:flex; flex-direction:column; gap:20px; }
/* USER LIST */
.user-header{ display:flex; justify-content:space-between; align-items:center; }
.user-header button{ padding:8px 14px; border:1px solid #c08080; background:#fff; border-radius:8px; cursor:pointer; }
.user-table{ width:100%; border-collapse:collapse; border:1px solid var(--border-color); }
.user-table th{ background:var(--primary-accent); padding:10px; text-align:left; }
.user-table td{ padding:10px; border-top:1px solid black; font-size:.9rem; }
.user-info{ display:flex; align-items:center; gap:10px; }
.avatar{ width:40px;height:40px; border-radius:50%; background:#ddd; }
.action-btn{ padding:5px 10px; border:1px solid #c08080; background:#fff; border-radius:6px; font-size:.8rem; cursor:pointer; }
/* CREATE USER */
.input{ width:100%; padding:13px 10px; border:1px solid #c08080; border-radius:8px; }
/* FORM ROW */
.form-row{ display:flex; align-items:center; gap:12px; }
.form-row label{ width:120px; font-size:.9rem; font-weight:500; }
.form-row .input{ flex:1; }
table th, table td { border:1px solid #5a4742ff; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<aside>
    <div class="profile-section">
        <div class="profile-circle"></div>
        <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <p><?php echo htmlspecialchars($_SESSION['role']);?></p>
    </div>

    <nav>
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i> Overview</a>
        <a class="nav-item active"><i class="fa-solid fa-user"></i> Users</a>
        <a href="products.php" class="nav-item"><i class="fa-solid fa-box"></i> Products</a>
        <a href="orders.php" class="nav-item"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
    </nav>

    <a href="../logout.php?logout=true" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>

<!-- MAIN -->
<main>
<header>
    <h1 id="pageTitle">User Management</h1>
</header>

<div class="content-body">

<?php
if(isset($_SESSION['create_user_error'])) {
    echo "<p style='color:red;margin-bottom:10px;'>".$_SESSION['create_user_error']."</p>";
    unset($_SESSION['create_user_error']);
}
if(isset($_SESSION['create_user_success'])) {
    echo "<p style='color:green;margin-bottom:10px;'>".$_SESSION['create_user_success']."</p>";
    unset($_SESSION['create_user_success']);
}
?>

<!-- USER LIST -->
<div id="userListView">
<div class="user-header">
    <p>All users: <strong><?php echo count($users); ?></strong></p>
    <button onclick="showCreateUser()"><i class="fa-solid fa-plus"></i> Add User</button>
</div>

<table class="user-table">
<thead>
<tr>
    <th style="text-align:center;">User</th>
    <th style="text-align:center;">Role</th>
    <th style="text-align:center;">Action</th>
</tr>
</thead>
<tbody>
<?php if($users): foreach($users as $u): ?>
<tr>
<td>
    <div class="user-info">
        <div class="avatar"></div>
        <div>
            <strong><?php echo htmlspecialchars($u['username']); ?></strong><br>
            <?php echo htmlspecialchars($u['email']); ?>
        </div>
    </div>
</td>
<td style="text-align:center;"><?php echo htmlspecialchars($u['role']); ?></td>
<td style="text-align:center; vertical-align:middle;">
    <button class="action-btn edit-btn"
        data-user_id="<?= $u['user_id'] ?>"
        data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>"
        data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
        data-role="<?= htmlspecialchars($u['role'], ENT_QUOTES) ?>">
        Edit
    </button>
    <form method="POST" action="delete_user.php" style="display:inline;">
        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
        <button type="submit" class="action-btn" onclick="return confirm('Delete this user?')">Delete</button>
    </form>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="3">No users found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<!-- CREATE / EDIT USER FORM -->
<div id="createUserView" style="display:none;max-width:400px;">
<i class="fa-solid fa-arrow-left" style="cursor:pointer" onclick="showUserList()"></i>

<form method="POST" action="create_user.php" style="margin-top:20px;display:flex;flex-direction:column;gap:15px;">
    <input type="hidden" name="user_id" id="user_id">

    <div class="form-row">
        <label>Username</label>
        <input type="text" class="input" name="username" id="username" required>
    </div>

    <div class="form-row">
        <label>Email</label>
        <input type="email" class="input" name="email" id="email" required>
    </div>

    <div class="form-row">
        <label>Password</label>
        <input type="password" class="input" name="password" id="password">
    </div>

    <div class="form-row">
        <label>Role</label>
        <select name="role" class="input" id="role">
            <option>Admin</option>
            <option>Seller</option>
            <option>Buyer</option>
        </select>
    </div>

    <button type="submit" class="action-btn" style="width:150px;margin-left:132px;">
        Create / Update User
    </button>
</form>
</div>

</div>
</main>

<script>
function showCreateUser(){
    userListView.style.display='none';
    createUserView.style.display='block';
    pageTitle.innerText='Create User';
    // Clear form
    document.getElementById('user_id').value = '';
    document.getElementById('username').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('role').value = 'Admin';
}

function showUserList(){
    createUserView.style.display='none';
    userListView.style.display='block';
    pageTitle.innerText='User Management';
}

// Edit user pre-fill
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        showCreateUser();
        document.getElementById('user_id').value = btn.dataset.user_id;
        document.getElementById('username').value = btn.dataset.username;
        document.getElementById('email').value = btn.dataset.email;
        document.getElementById('role').value = btn.dataset.role;
        document.getElementById('password').required = false; // optional for edit
    });
});
</script>

</body>
</html>
