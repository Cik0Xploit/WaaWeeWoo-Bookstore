<?php
session_start();
require_once 'function/connectdb.php';
include 'header.php';

// Fetch all categories
$query = "SELECT id, name, description FROM categories ORDER BY name ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/categories.css">
</head>
<body>

<h2 class="page-title">📚 Book Categories</h2>

<div class="category-container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="category-card">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p><?= htmlspecialchars($row['description'] ?: 'No description available.') ?></p>
                <a href="books.php?category=<?= $row['id'] ?>" class="btn-view">View Books</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-cat">No categories found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
