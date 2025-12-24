<?php
require './config/db.php';

if($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = 'buyer';

 
    if(!preg_match("/^[a-zA-Z0-9_]{5,20}$/", $username)) {
        echo "<script>alert('Username must be 5-20 characters, letters/numbers/underscore only.');</script>";
    }
 
    else if(!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
        echo "<script>alert('Invalid email format.');</script>";
    }
 
    else if(strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.');</script>";
    }
    else {

     
        $check = $con->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        $check->execute([$username]);

        if($check->rowCount() > 0) {
            echo "<script>alert('Username already taken.');</script>";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $con->prepare(
                "INSERT INTO users (username, email, password_hash, role)
                 VALUES (:username, :email, :password, :role)"
            );

            $save = $stmt->execute([
                ":username" => $username,
                ":email"    => $email,
                ":password" => $hashedPassword,
                ":role"     => $role
            ]);

            if($save) {
                echo "<script>
                        alert('Registration successful!');
                        window.location.href = './login.php';
                      </script>";
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Poppins",sans-serif;}
body{
    background: url(rbg.png) no-repeat center center fixed;
    background-size: cover;
    overflow: hidden;
}

.container{
    width:80%;
    max-width:1200px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
 
.image-section img{
    width:435px;
    height: 620px;
    margin-left: 130px;
    margin-top: -10px;
}
 
.register-box{
    background:#352F2F;
    width:300px;
    padding:35px;
    border-radius:20px;
    color:#fff;
    margin-top: 35px;
    margin-right: 60px;
}
.register-box h2{
    margin-bottom:20px;
    font-size:22px;
}

label{font-size:14px;margin-top:10px;display:block;}

input{
    width:100%;
    padding:10px;
    margin:5px 0 15px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}

.register-btn{
    width:100%;
    background:white;
    color:black;
    font-size:15px;
    padding:10px 0;
    border-radius:6px;
    margin-top:10px;
    cursor:pointer;
    transition:.3s;
}
.register-btn:hover{
    background:#BF847F;
    color:white;
}

.have-acc{
    font-size:13px;
    margin-top:15px;
    text-align:center;
}
.have-acc a{
    font-weight:bold;
    color:salmon;
}

.back-link{
    position:absolute;
    bottom:20px;
    right:40px;
    font-size:14px;
    color:#FDD5D5;
    text-decoration:none;
}
</style>
</head>
<body>

<div class="container">
    <div class="image-section">
        <img src="registerbg.png">
    </div>

    <div class="register-box">
        <h2>Create Account</h2>

        <form action="" method="POST">

            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required>

            <label>Email</label>
            <input type="text" name="email" placeholder="Enter email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>

            <button type="submit" class="register-btn">Register</button>
        </form>

        <p class="have-acc">Already have an account? <a href="./login.php">Login here</a></p>

    </div>

</div>

<a href="./landingpage.php" class="back-link">Back to home</a>

</body>
</html>
