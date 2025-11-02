<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

$fullname = $_SESSION['fullname'] ?? 'Admin';
include ("../function/connectdb.php");
$count_query = "SELECT COUNT(id) AS total FROM books";
$user_query = "SELECT COUNT(id) AS total FROM users";
$count_result = mysqli_query($conn, $count_query);

$total_books = 0;
if ($count_result) { 
    $book_data = mysqli_fetch_assoc($count_result);
    $total_books = $book_data['total'];
    mysqli_free_result($count_result); 
}

$user_result = mysqli_query($conn, $user_query); 
$registered_users = 0;
if ($user_result) {
    $user_data = mysqli_fetch_assoc($user_result);
    $total_users = $user_data['total'];
    mysqli_free_result($user_result);
}
include "header.php";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dash.css"> 
    <link rel="stylesheet" href="navbar.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard-grid">
            <div class="card">
                <h3>📚 Total Books</h3>
                <p><?= htmlspecialchars($total_books)?></p>
            </div>
            <div class="card">
                <h3>👥 Registered Users</h3>
                <p><?= htmlspecialchars($total_users)?></p>
            </div>
            <div class="card">
                <h3>🛍 Orders Today</h3>
                <p>9</p>
            </div>
            <div class="card">
                <h3>💰 Revenue</h3>
                <p>RM 1,230</p>
            </div>
        </div>
    
</body>
</html>
