<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$payment_method = $user['payment_method'] ?? ''; 


if (isset($_POST['update_profile'])) {
    $stmt = $conn->prepare("
        UPDATE users SET 
        first_name=?, last_name=?, email=?, phone=?, address=?, account_status='FULLY VERIFIED'
        WHERE user_id=?
    ");
    $stmt->bind_param(
        "sssssi",
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $user_id
    );
    $stmt->execute();
    $user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
}

if (isset($_POST['update_profile_pic'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['name'] != '') {
        $dir = "uploads/profiles/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $file = $dir . time() . "_" . basename($_FILES['profile_pic']['name']);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file);
        $conn->query("UPDATE users SET profile_pic='$file' WHERE user_id=$user_id");
        $user['profile_pic'] = $file;
    }
}


if (isset($_POST['update_payment'])) {
    $method = $_POST['payment_method'] === 'COD' ? 'COD' : 'GCash';
    $stmt = $conn->prepare("UPDATE users SET payment_method=? WHERE user_id=?");
    $stmt->bind_param("si", $method, $user_id);
    $stmt->execute();
    $payment_method = $method;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Profile Dashboard</title>
<style>
:root {
    --bg-color: #f7ebe8;
    --card-bg: #ffffff;
    --btn-pink: #cc8386;
    --btn-gray: #8e8e8e;
    --text-dark: #333333;
    --text-gray: #666666;
    --status-orange: #d48275;
    --border-light: #eeeeee;
}
body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    background-color: var(--bg-color);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
        position: relative;   
    overflow: hidden;
}
.container {
    background: var(--card-bg);
    width: 780px;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    box-sizing: border-box;
    z-index: 2;
}
/* HEADER */
.profile-header {
    display: flex;
    align-items: center;
    padding-bottom: 15px;
}
.avatar-circle {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    border: 1px solid #dcdcdc;
    position: relative;
    background-size: cover;
    background-position: center;
}
.avatar-add {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    background: #fff;
    border: 1px solid #dcdcdc;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--btn-pink);
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
}
.header-info { margin-left: 15px; }
.username-row { font-size: 12px; color: var(--text-gray); }
.username-val { font-size: 22px; color: var(--text-dark); font-weight: 500; margin-left: 8px; }
.account-status-top { font-size: 10px; color: var(--text-gray); margin-top: 5px; }
.main-divider-horizontal { border: none; height: 2px; background-color: #000; margin-bottom: 20px; }
.view-grid {
    display: grid;
    grid-template-columns: 1fr 2px 1fr;
    gap: 20px;
}
.vertical-line { background-color: #000; }
h3 { font-size: 12px; color: var(--text-gray); text-transform: uppercase; margin-bottom: 10px; margin-top: 0; }
.section-box { border: 1px solid var(--border-light); border-radius: 10px; padding: 15px; margin-bottom: 15px; }
.data-row { display: flex; font-size: 13px; margin-bottom: 8px; }
.label { width: 100px; color: var(--text-gray); }
.value { color: var(--text-dark); font-weight: 500; }
.status-text { color: var(--status-orange); font-weight: bold; font-size: 12px; text-transform: uppercase;}
.view-container { width: 100%; text-align: center; }
.view-container h2 {
    font-size: 16px;
    color: var(--text-gray);
    letter-spacing: 2px;
    margin-bottom: 20px;
    font-weight: bold;
    text-transform: uppercase;
}
.edit-box input, .payment-option {
    width: 100%;
    padding: 12px;
    margin-bottom: 10px;
    border: 1px solid #eee;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
    box-sizing: border-box;
    display: block;
    text-align: center;
    text-decoration: none;
    background: #fff;
}
.payment-option:hover { background-color: #f9f9f9; }
.btn { width: 100%; border: none; border-radius: 8px; padding: 12px; font-size: 13px; cursor: pointer; margin-top: 8px; color: white; display: flex; align-items: center; justify-content: center; gap: 8px; box-sizing: border-box; }
.btn-pink { background-color: var(--btn-pink); }
.btn-gray { background-color: var(--btn-gray); }

.hidden { display: none; }
.password-form-container {
    max-width: 400px;
    margin: 0 auto;
    text-align: left;
}
.password-label {
    font-size: 13px;
    color: #333;
    margin-bottom: 5px;
    display: block;
}
.password-input-styled {
    width: 100%;
    padding: 12px;
    background: var(--input-fill);
    border: none;
    border-radius: 8px;
    margin-bottom: 15px;
    box-sizing: border-box;
}
.history-scroll {
    max-height: 400px; 
    overflow-y: auto;
    padding-right: 10px; 
}
.history-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); 
    gap: 15px;
    text-align: left;
}

.order-card {
    background: var(--input-fill, #fffaf9); 
    padding: 15px;
    border-radius: 10px;
    font-size: 11px;
    line-height: 1.4;
    color: #333;
    position: relative;
    box-shadow: 0 1px 8px rgba(0,0,0,0.05);
    margin-bottom: 10px;
}
.history-scroll::-webkit-scrollbar {
    width: 8px;
}

.history-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 4px;
}

.history-scroll::-webkit-scrollbar-track {
    background-color: transparent;
}

.order-status {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 10px;
    font-weight: bold;
    color: #8e6a6a;
}
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
.image1 {
    position: absolute;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -50%);

    width: 1250px;
    height: auto;
    object-fit: contain;

    z-index: 1;     
    pointer-events: none; 
}

</style>
</head>
<body>

<a href="buyer/dashboard.php" class="back-dashboard-btn" title="Back to Buyers Dashboard">
  <img src="arrow (1).png" alt="Go back to buyers dashboard">
</a>

<img src="profilebg.png" class="image1">

<div class="container">
    <div class="profile-header">
        <div class="avatar-circle" style="background-image: url('<?= htmlspecialchars($user['profile_pic'] ?: 'default-avatar.png') ?>');">
            <form method="post" enctype="multipart/form-data">
                <label class="avatar-add">
                    +
                    <input type="file" name="profile_pic" style="display:none;" onchange="this.form.submit()">
                    <input type="hidden" name="update_profile_pic" value="1">
                </label>
            </form>
        </div>
        <div class="header-info">
            <div class="username-row">Username: <span class="username-val"><?= htmlspecialchars($user['username']) ?></span></div>
            <div class="account-status-top">Account Status: <?= htmlspecialchars($user['account_status']) ?></div>
        </div>
    </div>

    <hr class="main-divider-horizontal">
    <div id="dashboardView" class="view-grid">
        <div class="left-col">
            <h3>Account Information</h3>
            <div class="section-box">
                <div class="data-row"><span class="label">Name</span><span class="value"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span></div>
                <div class="data-row"><span class="label">Email</span><span class="value"><?= htmlspecialchars($user['email']) ?></span></div>
                <div class="data-row"><span class="label">Password</span><span class="value">******</span></div>
                <div class="data-row"><span class="label">Status</span><span class="status-text"><?= htmlspecialchars($user['account_status']) ?></span></div>
                <button class="btn btn-pink" onclick="showView('editView')">✎ Edit Account Information</button>
            </div>

            <h3>Payment Methods Available</h3>
            <div class="section-box" style="text-align:center;">
                <div class="btn btn-pink">
                    Cash On Delivery
                </div>
                <div class="btn btn-pink">
                    Gcash
                </div>
                
            </div>
        </div>

        <div class="vertical-line"></div>

        <div class="right-col">
            <h3>Activity History</h3>
            <div class="section-box">
                <button class="btn btn-pink" onclick="showView('historyView')">📋 View All Orders</button>
            </div>
            
            <h3>Security</h3>
            <div class="section-box">
                <div style="font-size:13px; line-height:1.5;">📅 Joined: March 12, 2024<br>🔄 Last Update: March 30, 2024</div>
                <button class="btn btn-pink" onclick="showView('securityView')">🔒 Change Password</button>
            </div>
        </div>
    </div>
    <div id="editView" class="view-container hidden">
        <h2>Account Information</h2>
        <div class="section-box edit-box">
            <form id="editForm" method="post">
                <input name="first_name" placeholder="First Name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                <input name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                <input name="email" type="email" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
                <input name="phone" placeholder="Phone Number" value="<?= htmlspecialchars($user['phone']) ?>" required>
                <input name="address" placeholder="Address" value="<?= htmlspecialchars($user['address']) ?>" required>

                <button type="submit" name="update_profile" class="btn btn-pink">💾 Save Changes</button>
                <button type="button" class="btn btn-gray" onclick="showView('dashboardView')">Cancel</button>
            </form>
        </div>
    </div>
    <div id="paymentView" class="view-container hidden">
        <h2>Payment Method</h2>
        <div class="section-box edit-box" style="text-align:center;">
            <form method="post">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="COD" <?= $payment_method === 'COD' ? 'checked' : '' ?>>
                    Cash on Delivery
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="GCash" <?= $payment_method === 'GCash' ? 'checked' : '' ?>>
                    GCash
                </label>

                <button type="submit" name="update_payment" class="btn btn-pink" style="margin-top: 20px;">Save Payment Method</button>
                <button type="button" class="btn btn-gray" onclick="showView('dashboardView')" style="margin-top: 10px;">Back to Dashboard</button>
            </form>
        </div>
    </div>

<div id="historyView" class="view-container hidden">
    <h2>View Order History</h2>
        <div class="history-scroll">

    <div class="history-grid">
        <?php
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        require './config/db.php';

        if (!isset($_SESSION['user_id'])) {
            echo "<p>Please log in to view your order history.</p>";
        } else {
            $userId = (int)$_SESSION['user_id'];

            try {
                $stmt = $con->prepare("SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC");
                $stmt->execute([$userId]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($orders) {
                    foreach ($orders as $order) {
                        $stmtItems = $con->prepare("
                            SELECT oi.quantity, oi.total_price, p.name AS product_name
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = ?
                        ");
                        $stmtItems->execute([$order['id']]);
                        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                        $totalItems = array_sum(array_column($items, 'quantity'));

                        $statusColor = match(strtolower($order['order_status'])) {
                            'delivered' => 'green',
                            'pending' => 'orange',
                            'cancelled' => 'red',
                            default => 'gray',
                        };

                        echo '<div class="order-card">';
                        echo '<span class="order-status" style="color:' . $statusColor . ';">' . htmlspecialchars($order['order_status']) . '</span><br>';
                        echo '<span style="color:#cc8386; font-weight:bold;">Order ID</span><br>#ORD-' . htmlspecialchars($order['id']) . '<br><br>';

                        foreach ($items as $item) {
                            echo htmlspecialchars($item['product_name']) . ' × ' . htmlspecialchars($item['quantity']) . '<br>';
                        }

                        echo '<hr style="border:0; border-top:1px solid #c5b2af; margin:10px 0;">';
                        echo '<div style="display:flex; justify-content:space-between;">';
                        echo '<span>' . date('M d, Y', strtotime($order['created_at'])) . '<br>' . $totalItems . ' items</span>';
                        echo '<span style="text-align:right;">Total<br><strong>₱' . number_format((float)$order['total_amount'], 2) . '</strong></span>';
                        echo '</div></div>';
                    }
                } else {
                    echo "<p>No orders found.</p>";
                }
            } catch (PDOException $e) {
                echo "<p>Error fetching orders: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </div>
    </div>
    <button class="btn btn-gray" onclick="showView('dashboardView')" style="width:200px; margin: 30px auto;">Back</button>
</div>

<div id="securityView" class="view-container hidden">
    <h2>Change Password</h2>
    <div class="password-form-container">
        <form method="POST">
            <label class="password-label">Old Password:</label>
            <input type="password" name="old_password" class="password-input-styled" required>
            
            <label class="password-label">New Password:</label>
            <input type="password" name="new_password" class="password-input-styled" required>
            
            <label class="password-label">Confirm New Password:</label>
            <input type="password" name="confirm_password" class="password-input-styled" required>
            
            <button type="submit" name="change_password" class="btn" style="background: var(--input-fill); color: var(--btn-pink); font-weight: bold;">Save</button>
            <button type="button" class="btn btn-gray" onclick="showView('dashboardView')">Cancel</button>
        </form>

        <?php
        if (session_status() == PHP_SESSION_NONE) session_start();
        require './config/db.php';

        if (isset($_POST['change_password'])) {
            $userId = (int)$_SESSION['user_id'];
            $oldPassword = $_POST['old_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($newPassword !== $confirmPassword) {
                echo "<p style='color:red;'>New password and confirmation do not match.</p>";
            } else {
                try {
                    $stmt = $con->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row && password_verify($oldPassword, $row['password_hash'])) {
                        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmtUpdate = $con->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
                        $stmtUpdate->execute([$newHash, $userId]);
                        echo "<p style='color:green;'>Password updated successfully.</p>";
                    } else {
                        echo "<p style='color:red;'>Old password is incorrect.</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>Error updating password: " . $e->getMessage() . "</p>";
                }
            }
        }
        ?>
    </div>
</div>




</div>

<script>
function showView(viewId) {
    const views = ['dashboardView', 'editView', 'paymentView', 'historyView', 'securityView'];
    views.forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById(viewId).classList.remove('hidden');
}
const editForm = document.getElementById('editForm');
editForm.addEventListener('submit', function(e) {
    const inputs = editForm.querySelectorAll('input');
    for (let input of inputs) {
        if (input.value.trim() === '') {
            e.preventDefault();
            alert('All fields must be filled before saving!');
            return false;
        }
    }
});
</script>

</body>
</html>
