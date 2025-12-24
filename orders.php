<?php
session_start();
require './config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$orders = [];
try {
$stmt = $con->prepare("
    SELECT 
        o.id,
        o.total_amount,
        o.order_status,
        o.payment_status,
        o.created_at,
        p.name AS product_name,
        p.image_path
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    WHERE o.buyer_id = :uid AND o.order_status != 'Cancelled'
    ORDER BY o.id DESC
");
$stmt->execute([':uid' => $userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders</title>
<style>

:root {
  --border-color: #d8c3c3;
  --text-main: #333;
  --text-light: #777;
  --accent-red: #a85d5d;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #FBF6F5;
  display: flex;
  justify-content: center;
  padding: 40px;
  position: relative;
}

.order-container {
  background: white;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  max-width: 900px;
  width: 100%;
}

.order-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.order-card {
  width: 100%;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  padding: 15px;
  display: flex;
  gap: 15px;
  box-sizing: border-box;
}

.product-image {
  width: 140px;
  height: 160px;
  background-color: #d9d9d9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #555;
  text-align: center;
  font-size: 0.9rem;
}
.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}


.order-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.details-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.order-id label {
  display: block;
  font-size: 0.7rem;
  color: var(--accent-red);
  margin-bottom: 2px;
}

.order-id span {
  font-size: 0.75rem;
  font-weight: bold;
}

.status-badge {
  font-size: 0.7rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 4px;
}

.product-info p {
  margin: 4px 0;
  font-size: 0.85rem;
  color: var(--text-main);
}

.details-footer {
  font-size: 0.65rem;
  color: var(--text-light);
  line-height: 1.2;
}

.back-dashboard-btn {
    position: fixed;   
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
}

.back-dashboard-btn:hover {
  transform: scale(1.05);
  opacity: 0.9;
}

@media (max-width: 768px) {
  .order-grid {
    grid-template-columns: 1fr;
  }
}
.cancel-btn {
    margin-top: 10px;
    padding: 6px 12px;
    background-color: #a85d5d;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: 0.2s;
}
.cancel-btn:hover {
    background-color: #d14f4f;
}


</style>
</head>
<body>

<a href="buyer/dashboard.php" class="back-dashboard-btn" title="Back to Buyers Dashboard">
  <img src="arrow (1).png" alt="Go back to buyers dashboard">
</a>

<div class="order-container">
  <div class="order-grid">
    <?php if ($orders): ?>
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="product-image">
    <?php if (!empty($order['image_path'])): ?>
        <img src="./uploads/<?= htmlspecialchars($order['image_path']) ?>" 
             alt="<?= htmlspecialchars($order['product_name']) ?>">
    <?php else: ?>
        <img src="./assets/no-image.png" alt="No image">
    <?php endif; ?>
</div>

          <div class="order-details">
            <div class="details-top">
              <div class="order-id">
                <label>Order ID</label>
                <span>#ORD-<?= htmlspecialchars($order['id']) ?></span>
              </div>
              <div class="status-badge">
                <span class="clock-icon">🕒</span> <?= htmlspecialchars($order['order_status']) ?>
              </div>
            </div>
             
            <div class="product-info">
             <p><strong>Name:</strong> <?= htmlspecialchars($order['product_name']) ?></p>
<p><strong>Total Amount:</strong> ₱<?= number_format((float)$order['total_amount'], 2) ?></p>
<p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>

<?php if ($order['order_status'] !== 'Cancelled'): ?>
    <button class="cancel-btn" data-id="<?= $order['id'] ?>">Cancel Order</button>
<?php else: ?>
    <span style="color:red; font-weight:bold;">Order Cancelled</span>
<?php endif; ?>



            </div>

            <div class="details-footer">
              <p><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
document.querySelectorAll('.cancel-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const orderId = this.dataset.id;
        const card = this.closest('.order-card'); // get the card div

        if (confirm("Are you sure you want to cancel this order?")) {
            fetch('cancel_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'order_id=' + orderId
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    card.remove();
                    alert('Order cancelled successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                alert('Error: ' + err);
            });
        }
    });
});
</script>



</body>
</html>
