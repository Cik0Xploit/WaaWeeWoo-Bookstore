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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .tracking-timeline {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .tracking-timeline h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
            padding-left: 1rem;
        }

        .timeline-item.completed .timeline-marker {
            background-color: #198754;
        }

        .timeline-item.pending .timeline-marker {
            background-color: #6c757d;
        }

        .timeline-marker {
            position: absolute;
            left: -2.5rem;
            top: 0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
        }

        .timeline-content h6 {
            margin: 0 0 0.25rem 0;
            font-size: 0.9rem;
            font-weight: 600;
            color: #495057;
        }

        .timeline-content small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .tracking-info {
            margin-top: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .tracking-info h5 {
            color: #495057;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .tracking-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
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

            <!-- Order Tracking Section -->
            <div class="order-tracking-section" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">📦 Order Tracking</h3>
                <div class="tracking-timeline">
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-marker">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Order Placed</h6>
                                <small class="text-muted"><?php echo date('M d, Y H:i'); ?></small>
                            </div>
                        </div>
                        <div class="timeline-item pending">
                            <div class="timeline-marker">
                                <i class="far fa-circle text-muted"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Payment Confirmed</h6>
                                <small class="text-muted">Waiting for payment processing</small>
                            </div>
                        </div>
                        <div class="timeline-item pending">
                            <div class="timeline-marker">
                                <i class="far fa-circle text-muted"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Shipped</h6>
                                <small class="text-muted">Order will be shipped soon</small>
                            </div>
                        </div>
                        <div class="timeline-item pending">
                            <div class="timeline-marker">
                                <i class="far fa-circle text-muted"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Delivered</h6>
                                <small class="text-muted">Estimated delivery: 3-5 business days</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tracking-info" style="margin-top: 20px; padding: 15px; background: white; border-radius: 6px;">
                    <h5 style="color: #495057; margin-bottom: 10px;">Track Your Order</h5>
                    <p style="margin: 0; color: #6c757d; font-size: 0.9rem;">
                        You can track your order status anytime by visiting your <a href="profile.php" style="color: #007bff; text-decoration: none;">Profile</a> page under the "Order History" tab.
                    </p>
                </div>
            </div>

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
