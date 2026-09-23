<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $username, $password);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Registration successful! Welcome to Shema Architecture Portal. Please Login.";
        header("Location: login.php");
        exit;
    } else {
        $error_msg = "Error: Username or Email is already registered!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Us | Shema Architecture Studio</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #f1f5f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #1e293b; padding: 40px; border-radius: 12px; border: 1px solid #334155; width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #38bdf8; margin: 0 0 5px 0; }
        .brand { font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 2px; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #475569; border-radius: 6px; background: #0f172a; color: white; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #0284c7; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 15px; }
        .btn:hover { background: #0369a1; }
        a { color: #38bdf8; text-decoration: none; font-size: 14px; display: inline-block; margin-top: 15px; }
        .err { color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: left; }
    </style>
</head>
<body>
<div class="box">
    <h2>Create Account</h2>
    <div class="brand">Shema Shingela Daudi Architecture</div>
    <?php if(isset($error_msg)) echo "<div class='err'>⚠️ $error_msg</div>"; ?>
    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="username" placeholder="Username" required autocomplete="off">
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Register Account</button>
    </form>
    <a href="login.php">Already registered? Log In Portal</a>
</div>
</body>
</html>
