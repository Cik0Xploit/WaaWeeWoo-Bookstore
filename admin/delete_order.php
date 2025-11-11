<?php
// admin/delete_order.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php'; // DB connection

// 2. ID Validation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect back to the orders page with an error
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Invalid order ID provided for deletion.";
    header("Location: manage_orders.php"); // Assuming your orders page is manage_orders.php
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. Database Deletion
// WARNING: When deleting an order from the 'orders' table, you must also delete 
// all corresponding entries from the 'order_items' table to maintain database integrity.
// This is typically done automatically using Foreign Key constraints with ON DELETE CASCADE,
// but for maximum safety in simple scripts, you can run two delete queries or rely on CASCADE.

// Assuming a Foreign Key CASCADE exists, we only need to delete the main order record.
$delete_query = "DELETE FROM orders WHERE id = '$order_id' LIMIT 1";

if (mysqli_query($conn, $delete_query)) {
    // Check if any row was actually affected/deleted
    if (mysqli_affected_rows($conn) > 0) {
        $_SESSION['status'] = 'success'; 
        $_SESSION['message'] = "Order #{$order_id} successfully deleted.";
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "Error: Order #{$order_id} not found or already deleted.";
    }
} else {
    // Handle database error
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = "Database error: " . mysqli_error($conn);
}

mysqli_close($conn);

// 4. Redirect back to the orders list page
header("Location: manage_orders.php"); // Redirect to the order management page
exit();
?>