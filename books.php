<?php
session_start();
require_once 'function/connectdb.php';

// Pagination setup
$limit = 12; // 12 books per page (4x3 grid)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch books with categories
$query = "
    SELECT b.id, b.title, b.author, b.price, b.cover_image, c.name AS category_name
    FROM books b
    LEFT JOIN categories c ON b.category_id = c.id
    ORDER BY b.id ASC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Error fetching books: " . mysqli_error($conn));
}

// Count total books for pagination
$count_query = "SELECT COUNT(*) AS total FROM books";
$count_result = mysqli_query($conn, $count_query);
if (!$count_result) {
    die("Error counting books: " . mysqli_error($conn));
}
$total_books = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_books / $limit);

include_once 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Books - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/books.css">
</head>
<body>

<?php if (isset($_GET['added'])): ?>
    <div class="toast">✅ <?= htmlspecialchars($_GET['added']) ?> added to cart!</div>
<?php endif; ?>

<section class="books-section">
    <h2 class="page-title">📚 Available Books</h2>

    <div class="books-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="book-card">
                    <a href="book_details.php?id=<?= $row['id'] ?>">
                        <img src="images/<?= htmlspecialchars($row['cover_image']) ?>" 
                             alt="<?= htmlspecialchars($row['title']) ?>">
                    </a>
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p class="author"><?= htmlspecialchars($row['author'] ?: 'Unknown Author') ?></p>
                    <p class="category"><?= htmlspecialchars($row['category_name'] ?: 'Uncategorized') ?></p>
                    <p class="price">RM <?= number_format($row['price'], 2) ?></p>
                    <a href="cart_add.php?id=<?= $row['id'] ?>" class="btn">🛒 Add to Cart</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-books">No books found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn">Next</a>
        <?php endif; ?>
    </div>
</section>

<?php include_once 'footer.php'; ?>
</body>
</html>
