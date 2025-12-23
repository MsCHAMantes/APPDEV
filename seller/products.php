<?php 
session_start();
require_once "../config/db.php";

// AUTO-DETECT database connection
if (isset($conn)) {
    $db = $conn; // MySQLi
} elseif (isset($con)) {
    $db = $con; // PDO
} else {
    die("Database connection variable not found. Check db.php");
}

// Get logged-in seller ID
$seller_id = $_SESSION['user_id'] ?? 1;

/* ================= FETCH METRICS ================= */
try {
    if ($db instanceof PDO) {
        $totalProducts = $db->query("SELECT COUNT(*) AS total FROM products WHERE seller_id=$seller_id")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $pendingProducts = $db->query("SELECT COUNT(*) AS total FROM products WHERE seller_id=$seller_id AND status='pending'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $declinedProducts = $db->query("SELECT COUNT(*) AS total FROM products WHERE seller_id=$seller_id AND status='declined'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $products = $db->query("SELECT * FROM products WHERE seller_id=$seller_id ORDER BY created_at DESC");
    } else { // MySQLi
        $totalProducts = $db->query("SELECT COUNT(*) AS total FROM products WHERE seller_id=$seller_id")->fetch_assoc()['total'] ?? 0;
        $pendingProducts = $db->query("SELECT COUNT(*) AS total FROM products WHERE seller_id=$seller_id AND status='pending'")->fetch_assoc()['total'] ?? 0;
        $declinedProducts = $db->query("SELECT COUNT(*) AS total FROM products WHERE seller_id=$seller_id AND status='declined'")->fetch_assoc()['total'] ?? 0;
        $products = $db->query("SELECT * FROM products WHERE seller_id=$seller_id ORDER BY created_at DESC");
    }
} catch (Exception $e) {
    die("Error fetching products: " . $e->getMessage());
}

/* ================= CREATE / UPDATE PRODUCT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = $_POST['edit_id'] ?? null;
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $stock_limit = $_POST['stock_limit'];
    $category_id = $_POST['category'] ?? null;

    // Fetch category description automatically
    $categoryDescription = '';
    if ($category_id) {
        if ($db instanceof PDO) {
            $stmtCat = $db->prepare("SELECT description FROM categories WHERE id = :id");
            $stmtCat->bindValue(':id', $category_id, PDO::PARAM_INT);
            $stmtCat->execute();
            $categoryDescription = $stmtCat->fetchColumn() ?? '';
        } else {
            $stmtCat = $db->prepare("SELECT description FROM categories WHERE id = ?");
            $stmtCat->bind_param("i", $category_id);
            $stmtCat->execute();
            $stmtCat->bind_result($categoryDescription);
            $stmtCat->fetch();
        }
    }

    // Handle image upload
    $imageName = $_POST['existing_image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $imageName);
    }

    try {
        if ($db instanceof PDO) {
            if ($edit_id) {
                $stmt = $db->prepare("
                    UPDATE products 
                    SET name=:name, price=:price, stock=:stock, stock_limit=:stock_limit, image_path=:image, description=:description, category_id=:category_id
                    WHERE id=:id AND seller_id=:seller_id
                ");
                $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO products (seller_id, name, price, stock, stock_limit, image_path, description, status, category_id) 
                    VALUES (:seller_id, :name, :price, :stock, :stock_limit, :image, :description, 'pending', :category_id)
                ");
            }
            $stmt->bindValue(':seller_id', $seller_id, PDO::PARAM_INT);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':price', $price);
            $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindValue(':stock_limit', $stock_limit, PDO::PARAM_INT);
            $stmt->bindValue(':image', $imageName);
            $stmt->bindValue(':description', $categoryDescription); // use category description
            $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->execute();
        } else { // MySQLi
            if ($edit_id) {
                $stmt = $db->prepare("
                    UPDATE products 
                    SET name=?, price=?, stock=?, stock_limit=?, image_path=?, description=?, category_id=? 
                    WHERE id=? AND seller_id=?
                ");
                $stmt->bind_param("sdiiisiii", $name, $price, $stock, $stock_limit, $imageName, $categoryDescription, $category_id, $edit_id, $seller_id);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO products (seller_id, name, price, stock, stock_limit, image_path, description, status, category_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $stmt->bind_param("isdiiisi", $seller_id, $name, $price, $stock, $stock_limit, $imageName, $categoryDescription, $category_id);
            }
            $stmt->execute();
        }
        header("Location: products.php");
        exit;
    } catch (Exception $e) {
        die("Error saving product: " . $e->getMessage());
    }
}

/* ================= TOGGLE AVAILABILITY ================= */
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $toggle_id = intval($_GET['id']);
    $current_status = $_GET['toggle'] === 'available' ? 'available' : 'unavailable';
    try {
        if ($db instanceof PDO) {
            $stmt = $db->prepare("UPDATE products SET status=:status WHERE id=:id AND seller_id=:seller_id");
            $stmt->bindValue(':status', $current_status);
            $stmt->bindValue(':id', $toggle_id, PDO::PARAM_INT);
            $stmt->bindValue(':seller_id', $seller_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("UPDATE products SET status=? WHERE id=? AND seller_id=?");
            $stmt->bind_param("sii", $current_status, $toggle_id, $seller_id);
            $stmt->execute();
        }
        header("Location: products.php");
        exit;
    } catch (Exception $e) {
        die("Error toggling availability: " . $e->getMessage());
    }
}

/* ================= DELETE PRODUCT ================= */
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        if ($db instanceof PDO) {
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id AND seller_id = :seller_id");
            $stmt->bindValue(':id', $delete_id, PDO::PARAM_INT);
            $stmt->bindValue(':seller_id', $seller_id, PDO::PARAM_INT);
            $stmt->execute();
        } else { 
            $stmt = $db->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ii", $delete_id, $seller_id);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: products.php");
        exit;
    } catch (Exception $e) {
        die("Error deleting product: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Dashboard - Products Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Your existing CSS remains unchanged */
:root {
    --bg-sidebar: #fdf6f4;
    --bg-main: #ffffff;
    --primary-accent: #f8e8e2;
    --text-dark: #4a332d;
    --border-color: #d1b8b0;
    --accent-maroon: #b85c55; /* Add this line */
}


/* Global */
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
main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
header { background:var(--primary-accent); padding:15px 30px; display:flex; align-items:center; gap: 15px; }
.header-banner h1 { font-size: 1.5rem; }
.back-btn { background: var(--accent-maroon); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; display: none; }
.content-body { padding: 30px 40px; }
.metrics-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-bottom: 40px; }
.metric-card { border: 1.5px solid #333; padding: 20px; text-align: center; border-radius: 4px; }
.metric-card .value { font-size: 3rem; color: #333; }
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.btn { background: #fff; border: 1px solid #777; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; font-size: 0.85rem; }
table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; }
th, td { border: 1px solid #333; padding: 12px; text-align: center; }
.status-dot { width: 14px; height: 14px; border-radius: 50%; display: inline-block; }
#create-product-view { display: none; }
.form-layout { display: flex; gap: 50px; }
.form-fields { flex: 1; }
.form-group { display: flex; align-items: center; margin-bottom: 20px; gap: 15px; }
.form-group label { width: 120px; font-weight: 600; }
.form-group input, .form-group textarea { flex: 1; padding: 10px; border: 1px solid var(--accent-maroon); border-radius: 4px; }
.image-placeholder { width: 100%; aspect-ratio: 1/1; border: 2.5px solid var(--accent-maroon); border-radius: 8px; display: flex; align-items: center; justify-content: center; position: relative; cursor: pointer; }
.image-placeholder i { font-size: 100px; color: var(--accent-maroon); }
.btn-submit { background: var(--accent-maroon); color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
/* Ensure buttons inside table cells are aligned and spaced nicely */
/* Style buttons inside table cells */
td .btn {
    display: inline-flex;      /* Inline next to each other */
    align-items: center;       /* Vertically center text */
    justify-content: center;   /* Center content horizontally */
    margin: 2px 4px;           /* Space between buttons */
    padding: 6px 12px;         /* Adjust padding */
    font-size: 0.8rem;         /* Smaller font size */
    white-space: nowrap;       /* Prevent text wrap */
}

td .btn:disabled {
    background-color: #ccc;    /* Gray out disabled button */
    color: #666;
    cursor: not-allowed;
}

td .btn + .btn {
    margin-left: 6px;          /* Extra space if multiple buttons exist */
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
    <a href="./dashboard.php" class="nav-item ">
        <i class="fa-solid fa-house nav-icon"></i> Overview
    </a>

    <a href="./products.php" class="nav-item active">
        <i class="fa-solid fa-box nav-icon"></i> Products
    </a>

    <a href="./orders.php" class="nav-item">
        <i class="fa-solid fa-cart-shopping nav-icon"></i> Orders
    </a>
</nav>

    <a href="../logout.php?logout=true" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>

<main id="main-content">
<header class="header-banner">
    <button class="back-btn" id="back-to-list" onclick="toggleView('list')">
        <i class="fa-solid fa-arrow-left"></i>
    </button>
    <h1 id="page-title">Product Management</h1>
</header>

<div class="content-body">
<div id="product-list-view">
    <div class="metrics-container">
        <div class="metric-card"><h4>Total</h4><div class="value"><?= $totalProducts ?></div></div>
        <div class="metric-card"><h4>Pending</h4><div class="value"><?= $pendingProducts ?></div></div>
        <div class="metric-card"><h4>Declined</h4><div class="value"><?= $declinedProducts ?></div></div>
    </div>

    <div class="toolbar">
        <p>All products: <?= $totalProducts ?></p>
        <button class="btn" onclick="toggleView('form')"><i class="fa-solid fa-plus"></i> Add Products</button>
    </div>

<table>
    <thead>
        <tr>
            <th>Alert</th>
            <th>Image</th>
            <th>Item Name</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($products as $row): ?>
        <tr>
            <td>
                <span class="status-dot" style="background:<?= $row['stock'] <= $row['stock_limit'] ? 'red':'green' ?>;"></span>
            </td>
            <td>
                <?php if(!empty($row['image_path'])): ?>
    <img src="../uploads/<?= $row['image_path'] ?>" width="40" height="40" style="object-fit:cover;">
<?php else: ?>
    <i class="fa-solid fa-box"></i>
<?php endif; ?>

            </td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= $row['stock'] ?></td>
            <td>₱<?= number_format($row['price'], 2) ?></td>
            <td>
                <!-- Edit Button -->
                <button class="btn" onclick="editProduct(
    <?= $row['id'] ?>, 
    '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', 
    <?= $row['price'] ?>, 
    <?= $row['stock'] ?>, 
    <?= $row['stock_limit'] ?>, 
    '<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>', 
    '<?= $row['image_path'] ?>'
)">Edit</button>


                <!-- Pending / Available Toggle -->
                <?php if($row['status'] === 'pending'): ?>
                    <button class="btn" disabled>Pending Approval</button>
                <?php else: ?>
                    <a href="?toggle=<?= $row['status']=='available'?'unavailable':'available' ?>&id=<?= $row['id'] ?>" class="btn">
                        <?= $row['status']=='available'?'Set Unavailable':'Set Available' ?>
                    </a>
                <?php endif; ?>

                <!-- Delete Button -->
                <a href="?delete_id=<?= $row['id'] ?>" class="btn" onclick="return confirm('Are you sure you want to delete this product?')">
                    Delete
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</div>

<div id="create-product-view">
<form method="POST" enctype="multipart/form-data" class="form-layout">
<div class="form-fields">
    <div class="form-group"><label>Name:</label><input name="name" required></div>
    <div class="form-group"><label>Price:</label><input type="number" step="0.01" name="price" required></div>
    <div class="form-group"><label>Stock:</label><input type="number" name="stock" required></div>
    <div class="form-group"><label>Limit:</label><input type="number" name="stock_limit" required></div>
    <div class="form-group">
    <label>Category:</label>
<select name="category" required>
    <option value="" disabled selected>Select Category</option>
    <option value="1">Lipstick</option>
    <option value="2">Foundation</option>
    <option value="3">Browser</option>
    <option value="4">Eyes Products</option>
    <option value="5">Blush</option>
</select>

</div>

    <div class="form-group"><label>Description:</label><textarea name="description" rows="4"></textarea></div>
</div>

<div style="width: 300px; text-align: center;">
    <div class="image-placeholder">
        <i class="fa-solid fa-image"></i>
        <input type="file" name="image" style="opacity:0;position:absolute;inset:0;cursor:pointer;">
    </div>
    <p style="margin: 10px 0;">Insert Image Here</p>
    <button type="submit" class="btn-submit">Create Product</button>
</div>
</form>
</div>
</div>
</main>

<script>
function toggleView(view){
    const list=document.getElementById('product-list-view');
    const form=document.getElementById('create-product-view');
    const title=document.getElementById('page-title');
    const back=document.getElementById('back-to-list');

    if(view==='form'){
        list.style.display='none';
        form.style.display='block';
        title.innerText='Create New Product';
        back.style.display='inline-block';
    }else{
        list.style.display='block';
        form.style.display='none';
        title.innerText='Product Management';
        back.style.display='none';
    }
}

function editProduct(id, name, price, stock, stock_limit, description, image) {
    toggleView('form');
    document.querySelector('#create-product-view input[name="name"]').value = name;
    document.querySelector('#create-product-view input[name="price"]').value = price;
    document.querySelector('#create-product-view input[name="stock"]').value = stock;
    document.querySelector('#create-product-view input[name="stock_limit"]').value = stock_limit;
    document.querySelector('#create-product-view textarea[name="description"]').value = description;

    // store product id in hidden input for update
    let hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'edit_id';
    hidden.value = id;
    document.querySelector('#create-product-view form').appendChild(hidden);

    // store existing image name
    let hiddenImage = document.createElement('input');
    hiddenImage.type = 'hidden';
    hiddenImage.name = 'existing_image';
    hiddenImage.value = image;
    document.querySelector('#create-product-view form').appendChild(hiddenImage);

    document.getElementById('page-title').innerText = "Edit Product";
    document.getElementById('back-to-list').style.display = 'inline-block';
}
</script>
</body>
</html>
