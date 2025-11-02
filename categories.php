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
<link rel="stylesheet" href="css/categories.css">

<!-- ===== HERO SECTION ===== -->
<section class="hero">
  <div class="hero-text-box">
    <h1>Explore Categories</h1>
    <p>Discover books across various genres and topics. Find your next favorite read in our carefully curated categories.</p>
  </div>
</section>

<!-- ===== CATEGORIES SECTION ===== -->
<section class="categories container">
  <h2>Browse by Category</h2>
  <div class="category-grid">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <a href="books.php?category=<?= $row['id'] ?>" class="category-link">
                <div class="category-card">
                    <div class="category-icon">
                        <?php
                        // Assign icons based on category name
                        $icon = '📚';
                        switch (strtolower($row['name'])) {
                            case 'fiction': $icon = '📖'; break;
                            case 'self-help / business': $icon = '💼'; break;
                            case 'biography / science': $icon = '🔬'; break;
                            case 'programming / technology': $icon = '💻'; break;
                            case 'light novel': $icon = '📗'; break;
                        }
                        echo $icon;
                        ?>
                    </div>
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= htmlspecialchars($row['description'] ?: 'Explore a wide range of books in this category.') ?></p>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No categories found.</p>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>
