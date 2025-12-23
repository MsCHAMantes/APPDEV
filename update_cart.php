<?php
session_start();
require './config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

try {
    /* ================= REMOVE ITEM (FORCE DELETE) ================= */
    if (isset($_POST['remove_id'])) {
        $productId = (int)$_POST['remove_id'];
        $del = $con->prepare("
            DELETE FROM cart 
            WHERE user_id = :uid AND product_id = :pid
        ");
        $del->execute([
            ':uid' => $userId,
            ':pid' => $productId
        ]);

        header('Location: cart.php');
        exit;
    }

    /* ================= ADD TO CART ================= */
    if (isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        $addQty = 1; // default add quantity

        $check = $con->prepare("
            SELECT quantity 
            FROM cart 
            WHERE user_id = :uid AND product_id = :pid
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
                ':qty' => 1
            ]);
        }

        header('Location: cart.php');
        exit;
    }

    /* ================= UPDATE QUANTITIES ================= */
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $pid => $qty) {
            $pid = (int)$pid;
            $qty = max(0, (int)$qty); // ensure non-negative

            if ($qty <= 0) {
                $con->prepare("
                    DELETE FROM cart 
                    WHERE user_id = :uid AND product_id = :pid
                ")->execute([
                    ':uid' => $userId,
                    ':pid' => $pid
                ]);
            } else {
                $con->prepare("
                    UPDATE cart 
                    SET quantity = :qty 
                    WHERE user_id = :uid AND product_id = :pid
                ")->execute([
                    ':qty' => $qty,
                    ':uid' => $userId,
                    ':pid' => $pid
                ]);
            }
        }
    }

} catch (Exception $e) {
    // Optional: log error
    // error_log($e->getMessage());
}

header('Location: cart.php');
exit;
