<?php
// Note: This PHP block must be the very first thing in your file, 
// before any HTML output.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}
?>

<nav class="admin-navbar">
    <ul class="nav-links">
        <li>
            <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Overview
            </a>
        </li>
        <li>
            <a href="manage_books.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_books.php' ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i> Books
            </a>
        </li>
        <li>
            <a href="manage_category.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_category.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
        </li>
        <li>
            <a href="manage_user.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_user.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Members
            </a>
        </li>
        <li>
            <a href="manage_orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
        </li>
    </ul>
</nav>
<main>
</main>