<?php
session_start();
require './config/db.php';

// =============================
// ACTIVITY LOG FUNCTION
// =============================
function logActivity($con, $user_id, $role, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $con->prepare("
        INSERT INTO activity_logs (user_id, role, action, ip_address)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $role, $action, $ip]);
}

$error = ''; // initialize error

if($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if(!filter_var($username, FILTER_VALIDATE_EMAIL) &&
       !preg_match("/^[a-zA-Z0-9_]{5,20}$/", $username)) {
        $error = "Invalid username format!";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $con->prepare("SELECT * FROM users WHERE username = :user OR email = :user LIMIT 1");
        $stmt->execute([":user" => $username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if($data) {

            // support both old 'password' column & new 'password_hash'
            $hash = $data['password_hash'] ?? $data['password'] ?? '';

            if(password_verify($password, $hash)) {

                // SESSION DATA
                $_SESSION['user_id'] = $data['user_id'];
                $_SESSION['username'] = $data['username'];
                $_SESSION['role']     = $data['role'];

                // =============================
                // LOG LOGIN ACTIVITY
                // =============================
                logActivity($con, $_SESSION['user_id'], $_SESSION['role'], 'Logged in');

                // =============================
                //   LOADING ANIMATION SCREEN  
                // =============================
                $roleText = ucfirst($_SESSION['role']); // Admin, Seller, etc.

                echo "
                <html>
                <head>
                    <title>Logging In...</title>
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

                    <h2>You logged in as <strong>$roleText</strong></h2>
                    <div class='loader'></div>

                    <script>
                        setTimeout(() => {
                            window.location.href = './" . $_SESSION['role'] . "/dashboard.php';
                        }, 2000);
                    </script>

                </body>
                </html>
                ";
                exit();

            } else {
                $error = "Invalid password!";
            }

        } else {
            $error = "No user found!";
        }
    }

    // Save error in session and redirect back
    if(!empty($error)){
        $_SESSION['login_error'] = $error;
        header("Location: login.php");
        exit();
    }
}

// Display alert error
if(isset($_SESSION['login_error'])){
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
    echo "<script>alert(".json_encode($error).");</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Poppins",sans-serif;}
body{
            font-family: Arial, sans-serif;
            background: url(bg.png) no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            overflow: hidden;
}
.container{
    width:80%;max-width:1200px;display:flex;align-items:center;justify-content:space-between;
}

/* Product Left */
.image-section img{
    width:435px;
    height: 620px;
    margin-left: 130px;
    margin-top: 40px;
}

.login-box{
    background:#352F2F;
    width:300px;
    padding:35px;
    border-radius:20px;
    color:#fff;
    margin-top: 20px;
    margin-right: 60px;
}
.login-box h2{margin-bottom:20px;font-size:22px;}

label{font-size:14px;margin-top:10px;display:block;}

input{
    width:100%;padding:10px;margin:5px 0 15px;border-radius:6px;
    border:1px solid #ccc;font-size:14px;
}
.password-wrapper{
    position:relative;
}
.remember-row input[type="checkbox"]{
    width:auto;       /* remove full width */
    margin:0;         /* clean alignment */
}

.remember-row span{
    font-size:14px;
    margin-left:4px;  /* spacing between box + text */
}

.remember-row{
    display:flex;
    align-items:center;
    justify-content:flex-start; /* keeps it left side */
    gap:8px;
    margin-top:-5px; /* pulls closer to password input */
}


.login-btn{
    width:100%;background:white;color:black;font-size:15px;padding:10px 0;
    border-radius:6px;margin-top:15px;cursor:pointer;transition:.3s;
}
.login-btn:hover{background:#BF847F; color: white;}

.no-acc{font-size:13px;margin-top:15px;text-align:center;}
.no-acc a{font-weight:bold;color:salmon;}

.recaptcha{
    text-align:center;font-size:11px;color:#cfcfcf;margin-top:18px;
}

.back-link{
    position:absolute;
    bottom:20px;
    right:40px;
    font-size:14px;
    color:#462121;
    text-decoration:none;

}
</style>
</head>

<body>

<div class="container">

    <!-- Product Left -->
    <div class="image-section">
        <img src="loginpic.png">
    </div>

    <!-- LOGIN FORM -->
    <div class="login-box">
        <h2>Login</h2>
        <form action="" method="POST">
            <label>Username / Email</label>
            <input type="text" name="username" placeholder="Insert your email" required>

        <label>Password</label>
        <div class="password-wrapper" style="position:relative;">
    <input type="password" name="password" placeholder="Insert your password" id="password" required>
    <span id="togglePass" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">👁️</span>
        </div>

        <div class="remember-row">
            <input type="checkbox" name="remember">
            <span>Remind me</span>
        </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <p class="no-acc">I don't have an account? <a href="./register.php">Register now.</a></p>

        <p class="recaptcha">Protected by reCAPTCHA • Google Privacy Policy | Terms of Service apply.</p>
    </div>
</div>

<a href="./landingpage.php" class="back-link">Back to home</a>

<script>
document.getElementById("togglePass").addEventListener("click", function(){
    var pass = document.getElementById("password");
    if(pass.type === "password"){
        pass.type = "text";
        this.textContent = "🙈";
    } else {
        pass.type = "password";
        this.textContent = "👁️";
    }
});
</script>

</body>
</html>
