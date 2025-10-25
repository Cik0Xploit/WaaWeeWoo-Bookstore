<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'function/connectdb.php';

// Validate Book ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid book ID.");
}

$book_id = intval($_GET['id']);

// Fetch book details securely
$query = "
    SELECT b.*, c.name AS category_name 
    FROM books b
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.id = ?
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("DB Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Book not found.");
}

$book = $result->fetch_assoc();
$stmt->close();
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/book_detail.css">
</head>
<body>

<div class="book-details-container">
    <h2 class="book-title">📖 <?= htmlspecialchars($book['title']) ?></h2>

    <div class="book-details">
        <!-- Book Cover -->
        <div class="book-cover">
            <?php
            $cover = !empty($book['cover_image']) && file_exists("images/" . $book['cover_image'])
                ? $book['cover_image']
                : 'default.png';
            ?>
            <img src="images/<?= htmlspecialchars($cover) ?>" 
                 alt="<?= htmlspecialchars($book['title']) ?> Cover">
        </div>

        <!-- Book Information -->
        <div class="book-info">
            <p><strong>Author:</strong> <?= htmlspecialchars($book['author'] ?: 'Unknown') ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($book['category_name'] ?: 'Uncategorized') ?></p>
            <p><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn'] ?: '-') ?></p>
            <p><strong>Stock:</strong> <?= htmlspecialchars($book['stock'] ?: '0') ?> available</p>
            <p><strong>Price:</strong> 
                <span class="price">RM <?= number_format($book['price'], 2) ?></span>
            </p>
            <p><strong>Description:</strong></p>
            <p class="book-desc"><?= nl2br(htmlspecialchars($book['description'] ?: 'No description available.')) ?></p>

            <div class="book-actions">
                <a href="cart_add.php?id=<?= $book['id'] ?>" class="btn-primary">🛒 Add to Cart</a>
                <a href="books.php" class="btn-secondary">⬅ Back to Books</a>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>
<?php include 'footer.php'; ?>
