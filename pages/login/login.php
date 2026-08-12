<?php

session_start();

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT id, username, password
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$username]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header("Location: ../dashboard/dashboard.php");
        exit;

    } else {

        echo "<script>alert('Invalid username or password.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Login Form -->
    <form class="login-form">

        <!-- Header -->
        <div class="login-header">

            <div class="logo-wrapper">
                <img src="Bagabag Logo.jpg" alt="Organization Logo" style="border: 1px solid gray;">
            </div>

            <div class="header-text">
                <h3>BASCA-RIS</h3>
                <p>Barangay Association of Senior Citizens Affairs Records Information System</p>
            </div>
            <div class="logo-wrapper">
                <img src="LOGOMWSWD.jpg" alt="MFSCAP Logo" style="border: 1px solid gray;">
            </div>

        </div>

        <hr>

        <!-- Username -->
        <div class="form-group">
            <label for="loginUsername">Username</label>

            <div class="input-wrapper">
                <i class="bi bi-person"></i>

                <input type="text" id="loginUsername" placeholder="Enter your username" required>
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="loginPassword">Password</label>

            <div class="input-wrapper">
                <i class="bi bi-lock"></i>

                <input type="password" id="loginPassword" placeholder="Enter your password" required>

                <button type="button" class="password-toggle" onclick="togglePassword()">

                    <i class="bi bi-eye" id="eyeIcon"></i>

                </button>
            </div>
        </div>

        <!-- Login -->
        <a href="../dashboard/dashboard.php" class="login-btn" onclick="loginUser(event)">
            <i class="bi bi-box-arrow-in-right"></i>
            Sign In
        </a>

        <!-- Footer -->
        <p class="login-footer">
            Senior Citizen Information System
        </p>

    </form>

    <script src="login.js"></script>
</body>

</html>