<?php
session_start();
require './config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

if (!isset($_POST['product_id'])) {
    header('Location: product.php');
    exit;
}

$productId = (int)$_POST['product_id'];
$addQty = 1; // ✅ DEFAULT QUANTITY = 1

try {
    // Check if product already exists in cart
    $check = $con->prepare("
        SELECT quantity 
        FROM cart 
        WHERE user_id = :uid AND product_id = :pid
        LIMIT 1
    ");
    $check->execute([
        ':uid' => $userId,
        ':pid' => $productId
    ]);

    if ($row = $check->fetch(PDO::FETCH_ASSOC)) {
        // ✅ PRODUCT EXISTS → ADD TO EXISTING QUANTITY
        $newQty = $row['quantity'] + $addQty;

        $update = $con->prepare("
            UPDATE cart 
            SET quantity = :qty 
            WHERE user_id = :uid AND product_id = :pid
        ");
        $update->execute([
            ':qty' => $newQty,
            ':uid' => $userId,
            ':pid' => $productId
        ]);
    } else {
        // ✅ PRODUCT NOT IN CART → INSERT WITH QUANTITY = 1
        $insert = $con->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (:uid, :pid, :qty)
        ");
        $insert->execute([
            ':uid' => $userId,
            ':pid' => $productId,
            ':qty' => $addQty
        ]);
    }

} catch (Exception $e) {
    // optional: log error
}

header('Location: cart.php');
exit;
