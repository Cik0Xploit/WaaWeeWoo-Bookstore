<?php
session_start();
include 'function/connectdb.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = $conn->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Fetch cart items
$cart_query = $conn->prepare("
    SELECT b.id, b.title, b.price, c.quantity, (b.price * c.quantity) AS subtotal
    FROM carts c
    JOIN books b ON c.book_id = b.id
    WHERE c.user_id = ?
");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_result = $cart_query->get_result();

// Calculate totals
$subtotal = 0;
$shipping = 5.00; // Fixed shipping cost
while ($item = $cart_result->fetch_assoc()) {
    $subtotal += $item['subtotal'];
}
$total = $subtotal + $shipping;

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    $billing_address = trim($_POST['billing_address']);
    $payment_method = $_POST['payment_method'];

    if (empty($shipping_address) || empty($billing_address)) {
        $error = "Please fill in all required fields.";
    } else {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert order
            $order_query = $conn->prepare("
                INSERT INTO orders (user_id, total, shipping, status, shipping_address, billing_address)
                VALUES (?, ?, ?, 'pending', ?, ?)
            ");
            $order_query->bind_param("iddss", $user_id, $total, $shipping, $shipping_address, $billing_address);
            $order_query->execute();
            $order_id = $conn->insert_id;

            // Insert order items
            $cart_result->data_seek(0); // Reset result pointer
            while ($item = $cart_result->fetch_assoc()) {
                $item_query = $conn->prepare("
                    INSERT INTO order_items (order_id, book_id, price, quantity, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $item_query->bind_param("iidid", $order_id, $item['id'], $item['price'], $item['quantity'], $item['subtotal']);
                $item_query->execute();
            }

            // Clear cart
            $clear_cart = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
            $clear_cart->bind_param("i", $user_id);
            $clear_cart->execute();

            // Commit transaction
            $conn->commit();

            // Redirect to success page
            header("Location: checkout.php?success=1&order_id=" . $order_id);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Checkout failed. Please try again.";
        }
    }
}

// Check for success message
$success = isset($_GET['success']) && $_GET['success'] == 1;
$order_id = $_GET['order_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/cart.css">
    <style>
        .checkout-form { max-width: 800px; margin: 0 auto; }
        .checkout-form h2 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;
        }
        .checkout-summary { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .checkout-summary h3 { margin-top: 0; }
        .summary-item { display: flex; justify-content: space-between; margin: 10px 0; }
        .total { font-weight: bold; font-size: 1.2em; color: #8b5cf6; }
        .btn-checkout { background: #8b5cf6; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-checkout:hover { background: #7c3aed; }
        .success-message { text-align: center; padding: 50px 20px; }
        .success-message h2 { color: #22c55e; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="cart-container">
    <?php if ($success): ?>
        <div class="success-message">
            <h2>✅ Order Placed Successfully!</h2>
            <p>Thank you for your purchase. Your order #<?php echo $order_id; ?> has been placed and is being processed.</p>
            <p>You will receive an email confirmation shortly.</p>
            <a href="books.php" class="checkout-btn" style="display: inline-block; margin-top: 20px;">Continue Shopping</a>
        </div>
    <?php else: ?>
        <h1>Checkout</h1>

        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="checkout-summary">
            <h3>Order Summary</h3>
            <?php
            $cart_result->data_seek(0);
            while ($item = $cart_result->fetch_assoc()):
            ?>
                <div class="summary-item">
                    <span><?php echo htmlspecialchars($item['title']); ?> (x<?php echo $item['quantity']; ?>)</span>
                    <span>RM <?php echo number_format($item['subtotal'], 2); ?></span>
                </div>
            <?php endwhile; ?>
            <div class="summary-item">
                <span>Subtotal:</span>
                <span>RM <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-item">
                <span>Shipping:</span>
                <span>RM <?php echo number_format($shipping, 2); ?></span>
            </div>
            <div class="summary-item total">
                <span>Total:</span>
                <span>RM <?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <form method="post" class="checkout-form">
            <h2>Shipping Information</h2>

            <div class="form-group">
                <label for="shipping_address">Shipping Address *</label>
                <textarea name="shipping_address" id="shipping_address" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="billing_address">Billing Address *</label>
                <textarea name="billing_address" id="billing_address" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="cod">Cash on Delivery</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="bank">Bank Transfer</option>
                </select>
            </div>

            <button type="submit" class="btn-checkout">Place Order</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
