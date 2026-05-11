<?php
// login.php - Login Page using password_verify() + Sessions + Cookies

session_start();
require_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Auto-fill from "Remember Me" cookie
$remembered_email = '';
if (isset($_COOKIE['remember_email'])) {
    $remembered_email = htmlspecialchars($_COOKIE['remember_email']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);

    // Input validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $pdo = getConnection();

        // Fetch user by email — Prepared Statement
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // password_verify() for secure comparison
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Remember Me cookie — stores email for 30 days
            if ($remember) {
                setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            } else {
                setcookie('remember_email', '', time() - 3600, '/');
            }

            // Last login cookie
            setcookie('last_login', date('Y-m-d H:i:s'), time() + (365 * 24 * 60 * 60), '/', '', false, true);

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Expense Tracker</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-container">
    <div class="auth-brand">
        <span class="brand-icon">💰</span>
        <h1>Expense Tracker</h1>
        <p>Track your finances with ease</p>
    </div>
    <div class="auth-card">
        <h2>Welcome Back</h2>

        <?php if (!empty($_COOKIE['last_login'])): ?>
            <div class="alert alert-info">
                Last login: <?= htmlspecialchars($_COOKIE['last_login']) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= $remembered_email ?>"
                       placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Your password" required>
            </div>
            <div class="form-check">
                <input type="checkbox" id="remember_me" name="remember_me"
                       <?= $remembered_email ? 'checked' : '' ?>>
                <label for="remember_me">Remember Me (30 days)</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</div>
</body>
</html>
