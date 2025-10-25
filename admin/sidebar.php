<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}
?>

<aside class="sidebar">
    <h2>📚 WaaWeeWoo Admin</h2>
    <ul>
        <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a></li>
        <li><a href="manage_books.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_books.php' ? 'active' : '' ?>">📖 Manage Books</a></li>
        <li><a href="manage_category.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_category.php' ? 'active' : '' ?>">📖 Manage Category</a></li>
        <li><a href="manage_user.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>">👥 Manage Users</a></li>
        <li><a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">🛒 Orders</a></li>
        <li><a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">📊 Reports</a></li>
        <li><a href="user_log.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_log.php' ? 'active' : '' ?>">🕓 User Logs</a></li>
    </ul>
    <a href="logout.php" class="logout">🚪 Logout</a>
</aside>
