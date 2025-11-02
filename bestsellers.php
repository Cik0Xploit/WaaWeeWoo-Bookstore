<?php
session_start();
require_once 'function/connectdb.php';
include 'header.php';

// Fetch bestseller books (assuming there's a bestseller flag or we can sort by popularity/sales)
$query = "SELECT b.id, b.title, b.author, b.price, b.cover_image, b.description, c.name as category_name
          FROM books b
          LEFT JOIN categories c ON b.category_id = c.id
          WHERE b.id IN (1,2,3,4,5,6)
          ORDER BY b.id ASC
          LIMIT 12";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<link rel="stylesheet" href="css/bestsellers.css">

<!-- ===== HERO SECTION ===== -->
<section class="hero">
  <div class="hero-text-box">
    <h1>Our Bestsellers</h1>
    <p>Discover the most popular and highly-rated books loved by our readers. These timeless favorites continue to captivate hearts and minds.</p>
  </div>
</section>

<!-- ===== BESTSELLERS SECTION ===== -->
<section class="bestsellers-section">
  <div class="container">
    <h2>Top-Rated Books</h2>
    <div class="books-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($book = mysqli_fetch_assoc($result)): ?>
                <div class="book-card">
                    <div class="book-image">
                        <img src="images/<?= htmlspecialchars($book['cover_image'] ?: 'default.jpg') ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                        <div class="book-badge">Bestseller</div>
                    </div>
                    <div class="book-info">
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="author">by <?= htmlspecialchars($book['author']) ?></p>
                        <p class="category"><?= htmlspecialchars($book['category_name'] ?: 'General') ?></p>
                        <p class="description"><?= htmlspecialchars(substr($book['description'], 0, 100) . (strlen($book['description']) > 100 ? '...' : '')) ?></p>
                        <div class="book-footer">
                            <span class="price">RM <?= number_format($book['price'], 2) ?></span>
                            <a href="book_details.php?id=<?= $book['id'] ?>" class="btn-view">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-books">
                <p>No bestsellers available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
