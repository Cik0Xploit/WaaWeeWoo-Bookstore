<?php
session_start();
include 'function/connectdb.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $book_id = intval($_POST['book_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $message = 'Invalid rating.';
    } elseif (empty($comment)) {
        $message = 'Comment is required.';
    } else {
        // Check if review already exists
        $check_review = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ?");
        $check_review->bind_param("ii", $user_id, $book_id);
        $check_review->execute();
        $review_result = $check_review->get_result();

        if ($review_result->num_rows > 0) {
            $message = 'You have already reviewed this book.';
        } else {
            // Insert review
            $insert_review = $conn->prepare("INSERT INTO reviews (user_id, book_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
            $insert_review->bind_param("iiis", $user_id, $book_id, $rating, $comment);
            if ($insert_review->execute()) {
                $message = 'Review submitted successfully!';
            } else {
                $message = 'Failed to submit review. Please try again.';
            }
        }
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($full_name) || empty($email)) {
        $message = 'Full name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } else {
        // Check if email already used by another user
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        $result_check = $check_email->get_result();

        if ($result_check && $result_check->num_rows > 0) {
            $message = 'Email is already in use.';
        } else {
            $update = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $update->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
            if ($update->execute()) {
                $message = 'Profile updated successfully!';
                $_SESSION['full_name'] = $full_name;
            } else {
                $message = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Fetch user info
$query = $conn->prepare("SELECT full_name, email, phone, address, created_at FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/profile.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tracking-timeline {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .tracking-timeline h5 {
            margin-bottom: 1rem;
            color: #495057;
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

        .order-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .order-info h4 {
            margin: 0 0 0.25rem 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .order-date {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
        }

        .mt-3 {
            margin-top: 1rem !important;
        }

        .fs-6 {
            font-size: 0.875rem !important;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-muted {
            color: #6c757d !important;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">
    <!-- Profile Header -->
    <div class="card">
        <div class="card-content">
            <div class="profile-header">
                <div class="avatar">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tabs-list">
            <button class="tabs-trigger active" data-tab="info">Profile Info</button>
            <button class="tabs-trigger" data-tab="orders">Order History</button>
            <button class="tabs-trigger" data-tab="security">Security</button>
        </div>

        <!-- Profile Info Tab -->
        <div class="tab-content active" id="info">
            <div class="card">
                <div class="card-header">
                    <h3>Account Information</h3>
                    <button type="button" id="editBtn" class="btn btn-secondary">Edit Profile</button>
                </div>
                <?php if ($message): ?>
                    <div class="message <?php echo (strpos($message, 'success') !== false) ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <div class="card-content">
                    <form id="profileForm" method="POST">
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-actions" style="display: none;">
                            <button type="submit" name="update_profile" class="btn">Save Changes</button>
                            <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Orders Tab -->
        <div class="tab-content" id="orders">
            <div class="card">
                <div class="card-header">
                    <h3>Order History & Tracking</h3>
                </div>
                <div class="card-content">
                    <?php
                    $orders_query = $conn->prepare("
                        SELECT o.id, o.created_at as order_date, o.total, o.status, o.shipping_address,
                               GROUP_CONCAT(b.title SEPARATOR ', ') AS books
                        FROM orders o
                        LEFT JOIN order_items oi ON o.id = oi.order_id
                        LEFT JOIN books b ON oi.book_id = b.id
                        WHERE o.user_id = ?
                        GROUP BY o.id
                        ORDER BY o.created_at DESC
                    ");
                    $orders_query->bind_param("i", $user_id);
                    $orders_query->execute();
                    $orders_result = $orders_query->get_result();

                    if ($orders_result->num_rows > 0): ?>
                        <div class="orders-list">
                            <?php while ($order = $orders_result->fetch_assoc()):
                                $status_badge = match(strtolower($order['status'])) {
                                    'pending' => 'badge bg-warning text-dark',
                                    'paid' => 'badge bg-info',
                                    'shipped' => 'badge bg-primary',
                                    'delivered' => 'badge bg-success',
                                    'cancelled' => 'badge bg-danger',
                                    default => 'badge bg-secondary'
                                };
                            ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <div class="order-info">
                                            <h4>Order #<?php echo $order['id']; ?></h4>
                                            <p class="order-date"><?php echo date('M d, Y \a\t H:i', strtotime($order['order_date'])); ?></p>
                                        </div>
                                        <span class="<?php echo $status_badge; ?> fs-6">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="order-details">
                                        <p><strong>Books:</strong> <?php echo htmlspecialchars($order['books']); ?></p>
                                        <p><strong>Total:</strong> RM <?php echo number_format($order['total'], 2); ?></p>
                                        <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>

                                        <!-- Order Tracking Timeline -->
                                        <div class="tracking-timeline mt-3">
                                            <h5>Order Tracking</h5>
                                            <div class="timeline">
                                                <?php
                                                $tracking_steps = [
                                                    'pending' => ['step' => 1, 'label' => 'Order Placed', 'date' => $order['order_date'], 'completed' => true],
                                                    'paid' => ['step' => 2, 'label' => 'Payment Confirmed', 'date' => null, 'completed' => in_array(strtolower($order['status']), ['paid', 'shipped', 'delivered'])],
                                                    'shipped' => ['step' => 3, 'label' => 'Shipped', 'date' => null, 'completed' => in_array(strtolower($order['status']), ['shipped', 'delivered'])],
                                                    'delivered' => ['step' => 4, 'label' => 'Delivered', 'date' => null, 'completed' => strtolower($order['status']) === 'delivered']
                                                ];

                                                foreach ($tracking_steps as $step_key => $step):
                                                ?>
                                                    <div class="timeline-item <?php echo $step['completed'] ? 'completed' : 'pending'; ?>">
                                                        <div class="timeline-marker">
                                                            <?php if ($step['completed']): ?>
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-circle text-muted"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="timeline-content">
                                                            <h6><?php echo $step['label']; ?></h6>
                                                            <?php if ($step['completed'] && $step['date']): ?>
                                                                <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($step['date'])); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <?php if (strtolower($order['status']) === 'delivered'): ?>
                                            <div class="review-section mt-3">
                                                <h5>Review Books</h5>
                                                <?php
                                                // Fetch individual books for this order
                                                $books_query = $conn->prepare("
                                                    SELECT b.id, b.title
                                                    FROM order_items oi
                                                    JOIN books b ON oi.book_id = b.id
                                                    WHERE oi.order_id = ?
                                                ");
                                                $books_query->bind_param("i", $order['id']);
                                                $books_query->execute();
                                                $books_result = $books_query->get_result();
                                                while ($book = $books_result->fetch_assoc()):
                                                    // Check if user already reviewed this book
                                                    $review_check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ?");
                                                    $review_check->bind_param("ii", $user_id, $book['id']);
                                                    $review_check->execute();
                                                    $has_reviewed = $review_check->get_result()->num_rows > 0;
                                                ?>
                                                    <?php if (!$has_reviewed): ?>
                                                        <div class="review-form" style="margin-top: 10px;">
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                                <label><?php echo htmlspecialchars($book['title']); ?>:</label>
                                                                <select name="rating" required style="margin: 0 5px;">
                                                                    <option value="">Rate</option>
                                                                    <option value="5">5 ★</option>
                                                                    <option value="4">4 ★</option>
                                                                    <option value="3">3 ★</option>
                                                                    <option value="2">2 ★</option>
                                                                    <option value="1">1 ★</option>
                                                                </select>
                                                                <input type="text" name="comment" placeholder="Write a review..." required style="width: 200px; margin: 0 5px;">
                                                                <button type="submit" name="submit_review" class="btn btn-small">Submit Review</button>
                                                            </form>
                                                        </div>
                                                    <?php else: ?>
                                                        <p style="color: green; font-size: 0.9em;">You have already reviewed "<?php echo htmlspecialchars($book['title']); ?>"</p>
                                                    <?php endif; ?>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-orders">
                            <p>You haven't placed any orders yet.</p>
                            <a href="books.php" class="btn">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Security Tab -->
        <div class="tab-content" id="security">
            <div class="card">
                <div class="card-header">
                    <h3>Change Password</h3>
                </div>
                <div class="card-content">
                    <form method="POST" action="update_password.php" class="grid grid-2">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <div style="display: flex; justify-content: flex-end; align-items: end;">
                            <button class="btn" type="submit">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
// Initialize tabs on page load
document.addEventListener("DOMContentLoaded", () => {
    // ===== Tabs Switcher =====
    const tabButtons = document.querySelectorAll(".tabs-trigger");
    const tabContents = document.querySelectorAll(".tab-content");

    // Set initial display state
    tabContents.forEach((tab) => {
        if (tab.classList.contains("active")) {
            tab.style.display = "block";
        } else {
            tab.style.display = "none";
        }
    });

    tabButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            // Deactivate all tabs
            tabButtons.forEach((b) => b.classList.remove("active"));
            tabContents.forEach((tab) => {
                tab.classList.remove("active");
                tab.style.display = "none";
            });

            // Activate selected tab
            btn.classList.add("active");
            const targetTab = document.getElementById(btn.dataset.tab);
            if (targetTab) {
                targetTab.classList.add("active");
                targetTab.style.display = "block";
            }
        });
    });

    // ===== Edit Profile Functionality =====
    const editBtn = document.getElementById("editBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const formActions = document.querySelector(".form-actions");
    const inputs = document.querySelectorAll("#profileForm input");

    if (editBtn && cancelBtn && formActions && inputs.length) {
        let originalValues = {};

        editBtn.addEventListener("click", () => {
            // Store original values
            inputs.forEach((input) => {
                originalValues[input.name] = input.value;
                input.removeAttribute("readonly");
                input.style.backgroundColor = "#fff";
            });
            formActions.style.display = "flex";
            editBtn.style.display = "none";
        });

        cancelBtn.addEventListener("click", () => {
            // Restore original values
            inputs.forEach((input) => {
                input.value = originalValues[input.name];
                input.setAttribute("readonly", true);
                input.style.backgroundColor = "#f7fafc";
            });
            formActions.style.display = "none";
            editBtn.style.display = "inline-block";
        });
    }
});
</script>

</body>
</html>
