<?php
require_once 'connectdb.php'; // connect to database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    // Validate user credentials
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password using MD5 hash
        if (md5($_POST['password']) === $user['password']) {
            // Save session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
        } else {
            // Invalid password
            header("Location: ../login.php?error=Invalid email or password");
            exit;
        }

        // 🔹 Log user login
        $log_stmt = $conn->prepare("INSERT INTO user_logins (user_id) VALUES (?)");
        $log_stmt->bind_param("i", $user['id']);
        $log_stmt->execute();
        $log_stmt->close();

        // 🔹 Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../home.php");
        }
        exit;
    } else {
        // Invalid credentials
        header("Location: ../login.php?error=Invalid email or password");
        exit;
    }
}
?>
