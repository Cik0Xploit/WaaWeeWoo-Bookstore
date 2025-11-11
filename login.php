<?php
session_start();
require_once 'function/connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "⚠ Please enter both email and password.";
    } else {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (md5($password) === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                $log_stmt = $conn->prepare("INSERT INTO user_logins (user_id) VALUES (?)");
                $log_stmt->bind_param("i", $user['id']);
                $log_stmt->execute();
                $log_stmt->close();

                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: home.php");
                }
                exit;
            } else {
                $error = "❌ Invalid password.";
            }
        } else {
            $error = "❌ No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <img src="images/logo.png" alt="WaaWeeWoo Bookstore Logo" class="logo">
    <div class="login-container">
        <h2>Welcome Back</h2>

        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="btn">Login</button>

            <p class="register-link">Don't have an account? <a href="signup.php">Register here</a>.</p>
        </form>
    </div>
</body>
</html>