<?php
session_start();
require_once 'function/connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $agree = isset($_POST['agree']);

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "⚠️ All fields except address and phone are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "⚠️ Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "⚠️ Passwords do not match.";
    } elseif (!$agree) {
        $error = "⚠️ You must agree to the Terms of Service and Privacy Policy.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "⚠️ Email already registered.";
        } else {
            // Hash password and insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "
                INSERT INTO users (full_name, email, password, role, phone, address)
                VALUES (?, ?, ?, 'member', ?, ?)
            ";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $address);

            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "✅ Registration successful! Please log in.";
                header("Location: login.php");
                exit;
            } else {
                $error = "❌ Database error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <img src="images/logo.png" alt="WaaWeeWoo Bookstore Logo" class="logo">
    <div class="register-container">
        <h2>Create an Account</h2>

        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Full Name</label>
            <input type="text" name="full_name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="+60XXXXXXXXX">

            <label>Address</label>
            <textarea name="address" rows="3" placeholder="Your full address..."></textarea>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>

            <div class="terms">
                <input type="checkbox" name="agree" id="agree">
                <label for="agree">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
            </div>

            <button type="submit" class="btn">Register</button>

            <p class="login-link">Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>
