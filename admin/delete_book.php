<?php
// admin/delete_book.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php'; // DB connection

// 1. Check if the book ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_book.php?error=Invalid book ID provided.");
    exit();
}

$book_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Query to delete the book
// NOTE: In a real system, you might want to first delete the image file from the server
$delete_query = "DELETE FROM books WHERE id = '$book_id' LIMIT 1";

if (mysqli_query($conn, $delete_query)) {
    // Check if any row was actually affected/deleted
    if (mysqli_affected_rows($conn) > 0) {
        $_SESSION['message'] = "Book ID {$book_id} successfully deleted.";
    } else {
        $_SESSION['message'] = "Error: Book ID {$book_id} not found or already deleted.";
    }
} else {
    // Handle database error
    $_SESSION['message'] = "Database error: " . mysqli_error($conn);
}

mysqli_close($conn);

// 3. Redirect back to the book inventory page
header("Location: manage_books.php");
exit();
?>