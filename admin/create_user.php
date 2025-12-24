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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id  = trim($_POST['user_id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? '');

    if (!$username || !$email || !$role) {
        $_SESSION['create_user_error'] = "Username, email, and role are required.";
        header("Location: users.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['create_user_error'] = "Invalid email format.";
        header("Location: users.php");
        exit;
    }


    if (!preg_match('/^[a-zA-Z0-9_]{5,20}$/', $username)) {
        $_SESSION['create_user_error'] = "Invalid username format (5-20 chars, letters, numbers, underscore).";
        header("Location: users.php");
        exit;
    }

    try {
        if ($user_id) {
           
            $stmt = $con->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['create_user_error'] = "Username or email already exists for another user.";
                header("Location: users.php");
                exit;
            }

            if ($password) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $con->prepare("UPDATE users SET username = ?, email = ?, password_hash = ?, role = ? WHERE user_id = ?");
                $stmt->execute([$username, $email, $passwordHash, $role, $user_id]);
            } else {
                $stmt = $con->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?");
                $stmt->execute([$username, $email, $role, $user_id]);
            }

            $_SESSION['create_user_success'] = "User '$username' updated successfully.";
        } else {
            
            if (!$password) {
                $_SESSION['create_user_error'] = "Password is required for new users.";
                header("Location: users.php");
                exit;
            }

           
            $stmt = $con->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['create_user_error'] = "Username or email already exists.";
                header("Location: users.php");
                exit;
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $con->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $passwordHash, $role]);

            $_SESSION['create_user_success'] = "User '$username' created successfully.";
        }
    } catch (Exception $e) {
        $_SESSION['create_user_error'] = "Database error: " . $e->getMessage();
    }

    header("Location: users.php");
    exit;
}
?>
