<?php
// admin/delete_category.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php'; // DB connection

// 1. Check if the category ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_category.php?error=Invalid category ID provided.");
    exit();
}

$category_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Check if the category is being used by any books
$check_query = "SELECT COUNT(*) as book_count FROM books WHERE category_id = '$category_id'";
$check_result = mysqli_query($conn, $check_query);
$check_row = mysqli_fetch_assoc($check_result);

if ($check_row['book_count'] > 0) {
    $_SESSION['message'] = "Cannot delete category. It is currently assigned to {$check_row['book_count']} book(s). Please reassign or remove the books first.";
    header("Location: manage_category.php");
    exit();
}

// 3. Query to delete the category
$delete_query = "DELETE FROM categories WHERE id = '$category_id' LIMIT 1";

if (mysqli_query($conn, $delete_query)) {
    // Check if any row was actually affected/deleted
    if (mysqli_affected_rows($conn) > 0) {
        $_SESSION['message'] = "Category ID {$category_id} successfully deleted.";
    } else {
        $_SESSION['message'] = "Error: Category ID {$category_id} not found or already deleted.";
    }
} else {
    // Handle database error
    $_SESSION['message'] = "Database error: " . mysqli_error($conn);
}

mysqli_close($conn);

// 4. Redirect back to the category management page
header("Location: manage_category.php");
exit();
?>
