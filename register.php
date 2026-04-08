<?php
session_start();
require 'db.php';

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $student_id = trim($_POST['student_id']);
    $department = trim($_POST['department']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email or student_id already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
    $check->execute([$email, $student_id]);

    if ($check->fetch()) {
        $error = "Email or Student ID already registered!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, student_id, department, password, role) VALUES (?, ?, ?, ?, ?, 'student')");
        $stmt->execute([$name, $email, $student_id, $department, $password]);
        $success = "Registered! You can now login.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - AcademiaX</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <form action="register.php" method="post">
            <h1>Register</h1>

            <?php if ($error): ?>
                <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p style="color:lightgreen; text-align:center;"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="name" placeholder="User Name" required>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-box">
                <input type="text" name="student_id" placeholder="Student ID" required>
            </div>
            <div class="input-box">
                <input type="text" name="department" placeholder="Department" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn">Register</button>
            <div class="register-link">
                <p>Already have an account? <a href="index.php">Login</a></p>
            </div>
        </form>
    </div>
</body>
</html>