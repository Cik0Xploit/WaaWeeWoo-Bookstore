<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get page parameter (default = home)
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Define valid pages
$pages = [
    1 => 'home.php',
    2 => 'books.php',
    3 => 'viewCart.php',
    4 => 'checkout.php'
];

// Determine which page to load
$currentPage = $pages[$page] ?? 'home.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/books.css">
</head>

<body>
    <main>
        <?php include $currentPage; ?>
    </main>
</body>
</html>
