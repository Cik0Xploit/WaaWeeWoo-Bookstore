    <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php';

$message = "";
$order = null;
$order_items = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = intval($_GET['id']);

    // Fetch order details
    $order_query = $conn->prepare("
        SELECT o.*, u.full_name, u.email, u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $order_query->bind_param("i", $order_id);
    $order_query->execute();
    $result = $order_query->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();

        // Fetch order items
        $items_query = $conn->prepare("
            SELECT oi.*, b.title, b.price as current_price
            FROM order_items oi
            LEFT JOIN books b ON oi.book_id = b.id
            WHERE oi.order_id = ?
        ");
        $items_query->bind_param("i", $order_id);
        $items_query->execute();
        $items_result = $items_query->get_result();
        $order_items = $items_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $message = "Order not found.";
    }
} else {
    $message = "Invalid order ID.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $shipping_address = trim($_POST['shipping_address']);
    $billing_address = trim($_POST['billing_address']);
    $status = $_POST['status'];

    if (empty($shipping_address) || empty($billing_address)) {
        $message = "Shipping and billing addresses are required.";
    } else {
        // Update order
        $update_query = $conn->prepare("
            UPDATE orders
            SET shipping_address = ?, billing_address = ?, status = ?
            WHERE id = ?
        ");
        $update_query->bind_param("sssi", $shipping_address, $billing_address, $status, $order_id);

        if ($update_query->execute()) {
            $message = "Order updated successfully.";
            // Refresh order data
            header("Location: edit_order.php?id=" . $order_id . "&success=1");
            exit();
        } else {
            $message = "Failed to update order.";
        }
    }
}

// Check for success message
if (isset($_GET['success'])) {
    $message = "Order updated successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="dash.css">
    <link rel="stylesheet" href="navbar.css">
    <style>
        .edit-form { max-width: 800px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .order-items { margin-top: 20px; }
        .order-items table { width: 100%; border-collapse: collapse; }
        .order-items th, .order-items td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .btn-update { background: #8b5cf6; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-update:hover { background: #7c3aed; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include "header.php"; ?>

    <main class="main-content">
        <h1>Edit Order</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($order): ?>
            <div class="edit-form">
                <form method="post">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

                    <div class="form-group">
                        <label>Order ID:</label>
                        <input type="text" value="#<?php echo $order['id']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Customer:</label>
                        <input type="text" value="<?php echo htmlspecialchars($order['full_name'] ?? 'N/A'); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <input type="text" value="<?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" value="<?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Order Date:</label>
                        <input type="text" value="<?php echo htmlspecialchars($order['created_at']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Amount:</label>
                        <input type="text" value="RM <?php echo number_format($order['total'], 2); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status" required>
                            <option value="pending" <?php echo ($order['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo ($order['status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="shipped" <?php echo ($order['status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo ($order['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo ($order['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="shipping_address">Shipping Address:</label>
                        <textarea name="shipping_address" id="shipping_address" rows="4" required><?php echo htmlspecialchars($order['shipping_address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="billing_address">Billing Address:</label>
                        <textarea name="billing_address" id="billing_address" rows="4" required><?php echo htmlspecialchars($order['billing_address']); ?></textarea>
                    </div>

                    <button type="submit" name="update_order" class="btn-update">Update Order</button>
                    <a href="manage_orders.php" style="margin-left: 10px; color: #666;">Cancel</a>
                </form>

                <div class="order-items">
                    <h3>Order Items</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title'] ?? 'Book not found'); ?></td>
                                    <td>RM <?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>RM <?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p>Order not found or invalid order ID.</p>
            <a href="manage_orders.php">Back to Orders</a>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
<?php $conn->close(); ?>
