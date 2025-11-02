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
                    <h3>Order History</h3>
                </div>
                <div class="card-content">
                    <?php
                    $orders_query = $conn->prepare("
                        SELECT o.id, o.order_date, o.total_amount, o.status,
                               GROUP_CONCAT(b.title SEPARATOR ', ') AS books
                        FROM orders o
                        LEFT JOIN order_items oi ON o.id = oi.order_id
                        LEFT JOIN books b ON oi.book_id = b.id
                        WHERE o.user_id = ?
                        GROUP BY o.id
                        ORDER BY o.order_date DESC
                    ");
                    $orders_query->bind_param("i", $user_id);
                    $orders_query->execute();
                    $orders_result = $orders_query->get_result();

                    if ($orders_result->num_rows > 0): ?>
                        <div class="orders-list">
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <div class="order-info">
                                            <h4>Order #<?php echo $order['id']; ?></h4>
                                            <p class="order-date"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
                                        </div>
                                        <span class="status <?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="order-details">
                                        <p><?php echo htmlspecialchars($order['books']); ?></p>
                                        <p><strong>Total:</strong> RM <?php echo number_format($order['total_amount'], 2); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-orders">
                            <p>You haven’t placed any orders yet.</p>
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
