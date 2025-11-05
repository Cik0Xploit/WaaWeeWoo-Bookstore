<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php';

if (isset($_GET['id']) && isset($_GET['status']) && is_numeric($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);

    // Validate status
    $allowed_statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        $_SESSION['message'] = "Invalid status.";
        header("Location: manage_orders.php");
        exit();
    }

    // Update order status
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Order #$order_id status updated to " . ucfirst($new_status) . ".";
    } else {
        $_SESSION['message'] = "Failed to update order status.";
    }

    $stmt->close();
} else {
    $_SESSION['message'] = "Invalid request.";
}

header("Location: manage_orders.php");
exit();
?>
