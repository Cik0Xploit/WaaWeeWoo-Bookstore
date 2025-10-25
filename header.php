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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-50 text-gray-900">

<!-- ===== HEADER ===== -->
<header class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        
        <!-- Left: Logo & Welcome -->
        <div class="flex items-center space-x-3">
            <a href="index.php" class="flex items-center space-x-2">
                <div class="bg-purple-600 p-2 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 0a2 2 0 00-2 2v14a2 2 0 002 2V4zm0 0a2 2 0 012 2v14a2 2 0 01-2 2V4z" />
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-900">WaaWeeWoo Bookstore</span>
            </a>
            <span class="hidden sm:inline text-gray-600 text-sm">Welcome, <?= htmlspecialchars($full_name) ?></span>
        </div>

        <!-- Mobile Menu Button -->
        <button class="md:hidden p-2 border rounded text-gray-700 hover:bg-gray-100 focus:outline-none" id="mobileMenuBtn">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Nav Links -->
        <nav id="navMenu" class="hidden md:flex items-center space-x-6">
            <a href="index.php" class="text-gray-700 hover:text-purple-600 font-medium">Home</a>

            <!-- Books Dropdown -->
            <div class="relative">
                <button type="button" class="dropdown-toggle text-gray-700 hover:text-purple-600 font-medium flex items-center">
                    Books <span class="ml-1">▾</span>
                </button>
                <div class="dropdown-menu hidden absolute bg-white shadow-md border border-gray-100 rounded-md mt-2 py-2 w-40 z-10">
                    <a href="books.php" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">All Books</a>
                    <a href="categories.php" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Categories</a>
                </div>
            </div>

            <a href="contact.php" class="text-gray-700 hover:text-purple-600 font-medium">Contact</a>
            <a href="aboutUs.php" class="text-gray-700 hover:text-purple-600 font-medium">About Us</a>

            <!-- Search Form -->
            <form action="search.php" method="get" class="relative">
                <input type="text" name="q" placeholder="Search books..." 
                       class="border border-gray-300 rounded-md pl-9 pr-3 py-1.5 text-sm focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-2 top-2.5 w-4 h-4 text-gray-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
            </form>

            <!-- User Dropdown -->
            <div class="relative">
                <button type="button" class="dropdown-toggle text-gray-700 hover:text-purple-600 font-medium flex items-center">
                    Menu <span class="ml-1">▾</span>
                </button>
                <div class="dropdown-menu hidden absolute bg-white shadow-md border border-gray-100 rounded-md mt-2 py-2 w-40 right-0 z-10">
                    <a href="profile.php" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">👤 Profile</a>
                    <hr class="border-gray-200 my-1">
                    <?php if ($loggedIn): ?>
                        <a href="cart.php" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">🛒 Cart</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 font-medium">🚪 Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">🔐 Login</a>
                        <a href="register.php" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">📝 Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>

    <!-- Mobile Nav (Dropdown for Small Screens) -->
    <div id="mobileNav" class="hidden md:hidden bg-white border-t border-gray-200 px-4 py-3 space-y-2">
        <a href="index.php" class="block text-gray-700 hover:text-purple-600">Home</a>
        <a href="books.php" class="block text-gray-700 hover:text-purple-600">All Books</a>
        <a href="categories.php" class="block text-gray-700 hover:text-purple-600">Categories</a>
        <a href="contact.php" class="block text-gray-700 hover:text-purple-600">Contact</a>
        <a href="aboutUs.php" class="block text-gray-700 hover:text-purple-600">About Us</a>
        <?php if ($loggedIn): ?>
            <a href="profile.php" class="block text-gray-700 hover:text-purple-600">👤 Profile</a>
            <a href="cart.php" class="block text-gray-700 hover:text-purple-600">🛒 Cart</a>
            <a href="logout.php" class="block text-red-600 hover:text-purple-600">🚪 Logout</a>
        <?php else: ?>
            <a href="login.php" class="block text-gray-700 hover:text-purple-600">🔐 Login</a>
            <a href="register.php" class="block text-gray-700 hover:text-purple-600">📝 Register</a>
        <?php endif; ?>
    </div>
</header>

<script>
// Toggle mobile menu
document.getElementById("mobileMenuBtn").addEventListener("click", () => {
    document.getElementById("mobileNav").classList.toggle("hidden");
});

// Handle dropdown toggles
const dropdownToggles = document.querySelectorAll(".dropdown-toggle");
dropdownToggles.forEach(toggle => {
    toggle.addEventListener("click", (e) => {
        e.stopPropagation();
        const menu = toggle.nextElementSibling;
        document.querySelectorAll(".dropdown-menu").forEach(m => {
            if (m !== menu) m.classList.add("hidden");
        });
        menu.classList.toggle("hidden");
    });
});

// Close dropdowns when clicking outside
document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach(m => m.classList.add("hidden"));
});
</script>

</body>
</html>
