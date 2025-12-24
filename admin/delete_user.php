<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_POST['user_id'] ?? '';

if ($user_id) {
    $stmt = $con->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->execute([$user_id]);
    $_SESSION['create_user_success'] = "User deleted successfully.";
}

header("Location: users.php");
exit;
?>
