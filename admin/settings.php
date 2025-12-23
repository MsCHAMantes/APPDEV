<?php
    session_start();

    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Settings</title>
</head>
<body>
    <header>
        <h1>Settings</h1>
        <p>Signed in as: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
    </header>

    <nav>
        <ul>
            <li><a href="./index.php">Overview</a></li>
            <li><a href="./users.php">Users</a></li>
            <li><a href="./categories.php">Categories</a></li>
            <li><a href="./products.php">Products</a></li>
            <li><a href="./orders.php">Orders</a></li>
            <li><a href="./reports.php">Reports</a></li>
            <li><a href="./settings.php">Settings</a></li>
            <li><a href="../logout.php?logout=true">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <h2>Site Settings</h2>
            <form action="#" method="POST">
                <label>Site Name:</label>
                <input type="text" name="site_name" value="My Shop" required>
                <br>
                <label>Contact Email:</label>
                <input type="email" name="contact_email" value="admin@example.com" required>
                <br>
                <label>Default Currency:</label>
                <select name="currency">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="PHP">PHP</option>
                </select>
                <br>
                <button type="submit">Save Settings</button>
            </form>

            <h2>Account Settings</h2>
            <form action="#" method="POST">
                <label>Change Password:</label>
                <input type="password" name="new_password" placeholder="New Password">
                <br>
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" placeholder="Confirm Password">
                <br>
                <button type="submit">Update Password</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Admin Panel</p>
    </footer>
</body>
</html>


