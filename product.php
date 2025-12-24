<?php
session_start();
require './config/db.php';

$categories = [];
$stmt = $con->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


$products = [];
$stmt = $con->query("
    SELECT p.id, p.name, p.price, p.image_path, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

function slugify($text) {
    return strtolower(preg_replace('/\s+/', '-', trim($text)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beauty Product Gallery</title>

<style>
body {
    background-color: #fff8f6;
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 40px;
    color: #333;
}
.container { max-width: 1100px; margin: 0 auto; }
#search-box {
    width: 100%;
    padding: 10px 15px;
    margin-bottom: 30px;
    font-size: 16px;
    border: 1px solid #c08080;
    border-radius: 6px;
}
.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 40px;
}
.filter-btn {
    padding: 10px 18px;
    background: #fff;
    border: 1px solid #c08080;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.2s;
}
.filter-btn:hover { background: #f6dcdc; }
.filter-btn.active { background: #b85c55; color: white; }
.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px 25px;
}
.product-card {
    display: flex;
    flex-direction: column;
    cursor: pointer;
}
.image-box {
    background-color: #f1ede9;
    aspect-ratio: 1 / 1.1;
    display: flex;
    justify-content: center;
    align-items: center;
}
.image-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
.info-row {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}
.title { font-size: 14px; font-weight: 500; margin: 0; }
.rating { font-size: 12px; color: #f0a500; font-weight: bold; }
.sub-info { display: flex; justify-content: space-between; margin-top: 2px; }
.back-dashboard-btn {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 9999;

    text-decoration: none;
    display: inline-block;
  
    transition: transform 0.2s ease-in-out, opacity 0.2s ease;
  }

  .back-dashboard-btn img {
    width: 50px; 
    height: auto;
    display: block;
  }
  .back-dashboard-btn:hover {
    transform: scale(1.05);
    opacity: 0.9;
  }
.sales, .price { font-size: 11px; color: #888; }

@media (max-width: 900px) {
    .product-grid { grid-template-columns: repeat(2, 1fr); }
}

</style>
</head>
<body>
<div class="container">
<a href="buyer/dashboard.php" class="back-dashboard-btn" title="Back to Buyers Dashboard">
  <img src="arrow (1).png" alt="Go back to buyers dashboard">
</a>

<input type="text" id="search-box" placeholder="Search products by name..." onkeyup="searchProducts()">
<div class="filter-buttons">
    <button class="filter-btn active" data-category="all" onclick="filterProducts('all', this)">ALL</button>
    <?php foreach ($categories as $cat): ?>
        <button class="filter-btn"
            data-category="<?= slugify($cat['name']) ?>"
            onclick="filterProducts('<?= slugify($cat['name']) ?>', this)">
            <?= htmlspecialchars($cat['name']) ?>
        </button>
    <?php endforeach; ?>
</div>
<div class="product-grid" id="grid">
<?php if (!$products): ?>
    <p>No products available</p>
<?php endif; ?>

<?php foreach ($products as $p): ?>
<div class="product-card"
     data-category="<?= slugify($p['category_name']) ?>"
     data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
     onclick="window.location.href='product.php?id=<?= $p['id'] ?>'">

<div class="image-box">
    <a href="view_product.php?id=<?= $p['id'] ?>">
        <?php if (!empty($p['image_path'])): ?>
            <img src="./uploads/<?= htmlspecialchars($p['image_path']) ?>" alt="">
        <?php else: ?>
            <img src="./assets/no-image.png" alt="">
        <?php endif; ?>
    </a>
</div>
    <div class="info-row">
        <p class="title"><?= htmlspecialchars($p['name']) ?></p>
        <span class="rating">4.8 ★</span>
    </div>

    <div class="sub-info">
        <p class="sales">500 sale</p>
        <p class="price">₱<?= number_format($p['price'], 2) ?></p>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>

<script>
function filterProducts(category, btn){
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.product-card').forEach(card=>{
        const nameMatch = card.dataset.name.includes(document.getElementById('search-box').value.toLowerCase());
        if((category==='all' || card.dataset.category===category) && nameMatch){
            card.style.display='flex';
        } else {
            card.style.display='none';
        }
    });
}

function searchProducts() {
    const query = document.getElementById('search-box').value.toLowerCase();
    const activeBtn = document.querySelector('.filter-btn.active').dataset.category;

    document.querySelectorAll('.product-card').forEach(card=>{
        const nameMatch = card.dataset.name.includes(query);
        if(activeBtn === 'all' || card.dataset.category === activeBtn) {
            card.style.display = nameMatch ? 'flex' : 'none';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<script>
    window.AI_COM_API = 'http://127.0.0.1:5055/ask';

    <?php if (isset($_SESSION['user_id'])): ?>
    window.AI_COM_CONTEXT = { userId: <?php echo (int)$_SESSION['user_id']; ?> };
    <?php else: ?>
    window.AI_COM_CONTEXT = {};
    <?php endif; ?>
</script>
<script src="ai-chat/assets/ai-chat.js"></script>

</body>
</html>
