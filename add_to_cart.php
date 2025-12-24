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
$addQty = 1; 

try {
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
}

header('Location: cart.php');
exit;
