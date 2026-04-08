<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['name'];
        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademiaX</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <form action ="index.php" method="post">
            <h1>AcademiaX</h1>
            <div class="input-box">
             <input type="Email" name="email" placeholder="Email" required>
             <i class="bxl bx-clerk"></i>    
            </div>
             <div class="input-box">
             <input type="password" name="password" placeholder="Password" required>
             <i class="bx bx-lock"></i>
             </div>
             <div class ="remember-forgot">
                <label><input type="checkbox"> Remember me</label>
                <a href="#">Forgot Password?</a>
             </div>
             <div class="input-box button">
                <button type="submit" class="btn">Login</button>
                
                </div>
             
             <div class ="register-link">
                
                    <p>Don't have an account? <a href="register.php">Register</a></p>
             </div>
             <div class ="admin-link">
                
                    <p>Switch to admin? <a href="admin.php">Admin</a></p>
             </div>

                    
                


            </div>
        </form>
    </div>
</body>
</html>