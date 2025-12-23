<?php
session_start();
require './config/db.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id === 0) {
    die("Product not found.");
}

// Fetch product info
$stmt = $con->prepare("
    SELECT p.id, p.name, p.price, p.image_path, p.description, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status='active'
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

// Example placeholders for sold count and rating
$sold_count = 500;
$rating = 4.8;
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500&family=Playfair+Display:ital@0;1&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #fdf6f5;
            --border-color: #c49a94;
            --text-dark: #2d2d2d;
            --text-muted: #5e4b48;
        }

        body {
            margin: 0;
            padding: 40px;
            background-color: var(--bg-color);
            font-family: 'Outfit', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;

        }

        .container {
            padding-top: 45px;
            max-width: 1000px;
            width: 100%;
            display: flex;
            gap: 40px;
            position: relative;
        }

        /* Cancel Button */
        .cancel-btn {
            position: absolute;
            top: -20px;
            left: -60px;
            padding: 10px 30px;
            border: 1px solid var(--border-color);
            border-radius: 50px;
            background: white;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 14px;
        }

        /* Left Side: Product Image & Platform */
        .product-visuals {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
        }

        .product-image {
            width: 280px;
            height: 420px;

            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: -20px;
            z-index: 2;
        }

        .product-image img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .platform {
            width: 320px;
            height: 60px;
            border-radius: 50% / 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;

        }

        /* Right Side: Product Info */
        .product-info {
            flex: 1.2;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-card {
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            background: transparent;
            margin-bottom: 30px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 400;
            margin: 0 0 20px 0;
            color: var(--text-dark);
            line-height: 1.1;
        }

        .description {
            font-size: 15px;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        .stats {
            display: flex;
            gap: 15px;
            align-items: center;
            font-size: 14px;
            color: var(--text-muted);
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Buttons */
        .action-area {
            display: flex;
            gap: 20px;
        }

        .btn {
            flex: 1;
            padding: 15px 25px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-dark);
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background: #fffafa;
            transform: translateY(-1px);
        }

        .btn-buy {
            background: transparent;
        }

    </style>
</head>
<body>

    <div class="container">
        <a href="product.php" class="cancel-btn">Cancel</a>

        <div class="product-visuals">
            <div class="product-image">
                <img src="<?= !empty($product['image_path']) ? './uploads/' . htmlspecialchars($product['image_path']) : './assets/no-image.png' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="platform">
                <img src="platform.png" alt="">
            </div>
        </div>

        <div class="product-info">
            <div class="info-card">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <p class="description">
                    <?= htmlspecialchars($product['description']) ?>
                </p>

                <div class="stats">
                    <span><?= $sold_count ?> sold</span>
                    <div class="rating">
                        <?= $rating ?> <span style="color: #FFD700;">★</span>
                    </div>
                </div>
            </div>



<div class="action-area">
<form action="add_to_cart.php" method="POST">
    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
    <button type="submit" class="btn">🛒 Add to Cart</button>
</form>



    
    <button class="btn btn-buy" onclick="window.location.href='checkout.php?product_id=<?= $product['id'] ?>'">
        Buy For: ₱<?= number_format($product['price'], 2) ?>
    </button>
</div>

        </div>
    </div>

    <script>
function addToCart(productId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'add_to_cart.php';

    const pid = document.createElement('input');
    pid.type = 'hidden';
    pid.name = 'product_id';
    pid.value = productId;

    const qty = document.createElement('input');
    qty.type = 'hidden';
    qty.name = 'quantity';
    qty.value = 1;

    form.appendChild(pid);
    form.appendChild(qty);
    document.body.appendChild(form);
    form.submit();
}
</script>


</body>
</html>
