<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role']     = 'admin';
        header("Location: admindashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademiaX - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <form action="admin.php" method="post">
            <h1>Admin Login</h1>

            <?php if ($error): ?>
                <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="input-box">
                <input type="email" name="email" placeholder="Admin Email" required>
                <i class="bx bx-user"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="bx bx-lock"></i>
            </div>

            <button type="submit" class="btn">Login as Admin</button>

            <div class="register-link">
                <p>Not an admin? <a href="index.php">Student Login</a></p>
            </div>
        </form>
    </div>
</body>
</html>