<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$full_name = $_SESSION['full_name'] ?? 'Guest';
$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/header.css">
</head>
<body>

<header class="site-header">
    <div class="nav-container">
        <!-- Logo & Welcome -->
        <div class="logo-section">
            <a href="index.php" class="brand">
                <span class="brand-name">WaaWeeWoo Bookstore</span>
            </a>
            <span class="welcome-text">Welcome, <?= htmlspecialchars($full_name) ?></span>
        </div>

        <!-- Mobile Toggle -->
        <button id="mobileMenuBtn" class="mobile-toggle" aria-label="Toggle Menu">
            ☰
        </button>

        <!-- Navigation -->
        <nav class="nav-links" id="navMenu">
            <a href="index.php">Home</a>
            <a href="books.php">All Books</a>
            <a href="bestsellers.php">Bestsellers</a>
            <a href="contact.php">Team Members</a>
            <a href="aboutUs.php">About Us</a>

            <!-- Search -->
            <form action="search.php" method="get" class="search-form">
                <input type="text" name="q" placeholder="Search books...">
                <button type="submit">🔍</button>
            </form>

            <!-- User Menu -->
            <div class="dropdown">
                <button type="button" class="dropdown-btn">☰</button>
                <div class="dropdown-menu right">
                    <a href="profile.php">👤 Profile</a>
                    <hr>
                    <?php if ($loggedIn): ?>
                        <a href="cart.php">🛒 Cart</a>
                        <a href="logout.php" class="logout">🚪 Logout</a>
                    <?php else: ?>
                        <a href="login.php">🔐 Login</a>
                        <a href="signup.php">📝 Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>

    <!-- Mobile Nav -->
    <div class="mobile-nav" id="mobileNav">
        <a href="index.php">Home</a>
        <a href="books.php">All Books</a>
        <a href="categories.php">Categories</a>
        <a href="contact.php">Contact</a>
        <a href="aboutUs.php">About Us</a>
        <?php if ($loggedIn): ?>
            <a href="profile.php">👤 Profile</a>
            <a href="cart.php">🛒 Cart</a>
            <a href="logout.php" class="logout">🚪 Logout</a>
        <?php else: ?>
            <a href="login.php">🔐 Login</a>
            <a href="register.php">📝 Register</a>
        <?php endif; ?>
    </div>
</header>

<script>
// Toggle mobile menu
document.getElementById("mobileMenuBtn").addEventListener("click", () => {
    document.getElementById("mobileNav").classList.toggle("active");
});

// Dropdowns
document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", e => {
        e.stopPropagation();
        const menu = btn.nextElementSibling;
        document.querySelectorAll(".dropdown-menu").forEach(m => {
            if (m !== menu) m.classList.remove("show");
        });
        menu.classList.toggle("show");
    });
});

document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach(m => m.classList.remove("show"));
});
</script>

</body>
</html>
