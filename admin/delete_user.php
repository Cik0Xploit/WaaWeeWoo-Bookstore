<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php'; // DB connection

// 1. Check if the user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect to the user management page with an error
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Invalid user ID provided for deletion.";
    header("Location: manage_members.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);

// IMPORTANT: Prevent accidental deletion of the admin account used for login
if ($user_id == $_SESSION['id']) { 
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "You cannot delete your own active administrator account.";
    header("Location: manage_members.php");
    exit();
}

// 2. Query to delete the user from the 'users' table
$delete_query = "DELETE FROM users WHERE id = '$user_id' LIMIT 1";

if (mysqli_query($conn, $delete_query)) {
    // Check if any row was actually affected/deleted
    if (mysqli_affected_rows($conn) > 0) {
        $_SESSION['status'] = 'success'; 
        $_SESSION['message'] = "User ID **{$user_id}** successfully deleted from the system.";
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "Error: User ID **{$user_id}** not found or already deleted.";
    }
} else {
    // Handle database error
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Database error: " . mysqli_error($conn);
}

mysqli_close($conn);

// 3. Redirect back to the user management page
header("Location: manage_user.php");
exit();
?>