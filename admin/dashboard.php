<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

$fullname = $_SESSION['fullname'] ?? 'Admin';
include("../function/connectdb.php");

// Get statistics
$books_query = "SELECT COUNT(id) AS total FROM books";
$users_query = "SELECT COUNT(id) AS total FROM users";
$orders_query = "SELECT COUNT(id) AS total FROM orders";
$revenue_query = "SELECT SUM(total) AS total FROM orders WHERE status = 'delivered'";

$books_result = mysqli_query($conn, $books_query);
$users_result = mysqli_query($conn, $users_query);
$orders_result = mysqli_query($conn, $orders_query);
$revenue_result = mysqli_query($conn, $revenue_query);

$total_books = mysqli_fetch_assoc($books_result)['total'] ?? 0;
$total_users = mysqli_fetch_assoc($users_result)['total'] ?? 0;
$total_orders = mysqli_fetch_assoc($orders_result)['total'] ?? 0;
$total_revenue = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;

// Get recent orders
$recent_orders_query = "
    SELECT o.id, o.total, o.status, o.created_at, u.full_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);

// Get low stock books
$low_stock_query = "
    SELECT title, stock
    FROM books
    WHERE stock < 15
    ORDER BY stock ASC
    LIMIT 5
";
$low_stock_result = mysqli_query($conn, $low_stock_query);

include "header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <h1>📊 Admin Dashboard</h1>
    <p>Welcome back, <?php echo htmlspecialchars($fullname); ?>! Here's what's happening with your bookstore.</p>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>📚 Total Books</h3>
            <div class="value"><?php echo number_format($total_books); ?></div>
            <div class="change">+12% from last month</div>
        </div>
        <div class="stat-card">
            <h3>👥 Total Users</h3>
            <div class="value"><?php echo number_format($total_users); ?></div>
            <div class="change">+8% from last month</div>
        </div>
        <div class="stat-card">
            <h3>🛍️ Total Orders</h3>
            <div class="value"><?php echo number_format($total_orders); ?></div>
            <div class="change">+15% from last month</div>
        </div>
        <div class="stat-card">
            <h3>💰 Total Revenue</h3>
            <div class="value">RM <?php echo number_format($total_revenue, 2); ?></div>
            <div class="change">+23% from last month</div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Orders -->
        <div class="content-card">
            <h3>📦 Recent Orders</h3>
            <div class="recent-orders">
                <?php if (mysqli_num_rows($recent_orders_result) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <h4>Order #<?php echo htmlspecialchars($order['id']); ?></h4>
                                <p><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?> • <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="order-amount">
                                <div class="price">RM <?php echo number_format($order['total'], 2); ?></div>
                                <div class="status status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;">No orders yet</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="content-card">
            <h3>⚠️ Low Stock Alert</h3>
            <div class="low-stock">
                <?php if (mysqli_num_rows($low_stock_result) > 0): ?>
                    <?php while ($book = mysqli_fetch_assoc($low_stock_result)): ?>
                        <div class="stock-item">
                            <div class="stock-info">
                                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                <p>Needs restocking</p>
                            </div>
                            <div class="stock-count">
                                <div class="count"><?php echo htmlspecialchars($book['stock']); ?> left</div>
                                <button class="restock-btn" onclick="alert('Restock functionality coming soon!')">Restock</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #22c55e; padding: 20px;">✅ All books are well-stocked!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>


</div>

<?php
// Free results
mysqli_free_result($books_result);
mysqli_free_result($users_result);
mysqli_free_result($orders_result);
mysqli_free_result($revenue_result);
mysqli_free_result($recent_orders_result);
mysqli_free_result($low_stock_result);
?>

</body>
</html>
