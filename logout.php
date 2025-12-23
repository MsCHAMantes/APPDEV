<?php
session_start();
require './config/db.php'; // Make sure this points to your PDO connection

if (isset($_GET['logout']) && $_GET['logout'] === 'true') {

    $user_id = $_SESSION['user_id'] ?? null;
    $role    = $_SESSION['role'] ?? null;
    $username = $_SESSION['username'] ?? "User";

    // ===== LOG LOGOUT ACTIVITY =====
    if ($user_id && $role) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $stmt = $con->prepare("
            INSERT INTO activity_logs (user_id, role, action, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $role, 'Logged out', $ip]);
    }
    // ===============================

    $_SESSION = [];
    session_destroy();

    echo "
    <html>
    <head>
        <title>Logging Out...</title>
        <style>
            body {
                background: #FEF4E6;
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                flex-direction: column;
            }

            .loader {
                border: 6px solid #ddd;
                border-top: 6px solid #9e6b9e;
                border-radius: 50%;
                width: 60px;
                height: 60px;
                animation: spin 1s linear infinite;
                margin-bottom: 20px;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            h2 {
                color: #444;
                font-size: 35px;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <h2>Logging out... See you next time!</h2>
        <div class='loader'></div>
        

        <script>
            setTimeout(() => {
                window.location.href = './login.php';
            }, 2000);
        </script>

    </body>
    </html>
    ";
    exit();
}

header('Location: ./login.php');
exit();
?>
