<?php
// Session and Security checks must be the very first thing.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

// PHP variables for the header
$fullname = $_SESSION['fullname'] ?? 'Admin User';
$admin_email = $_SESSION['email'] ?? 'admin@waaweewoo.com';
// Simple function to generate initials
$initials = strtoupper(
    substr($fullname, 0, 1) . 
    (strpos($fullname, ' ') !== false ? substr(strstr($fullname, ' '), 1, 1) : '')
) ?? 'AD';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        
        <div class="logo-area">
            <div class="logo-icon">M</div> 
            <div class="text">
                <div class="title">WaaWeeWoo Admin</div>
                <div class="subtitle">Management Dashboard</div>
            </div>
        </div>
        
        <div class="user-area">
            <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
            
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($fullname) ?></div>
                <div class="email"><?= htmlspecialchars($admin_email) ?></div>
            </div>
            
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>
    
    <?php include "infobar.php"; ?>