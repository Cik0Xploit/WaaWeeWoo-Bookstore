<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'function/connectdb.php';

// Validate Book ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid book ID.");
$book_id = intval($_GET['id']);

// Fetch book details
$query = "SELECT b.*, c.name AS category_name 
          FROM books b
          LEFT JOIN categories c ON b.category_id = c.id
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("Book not found.");
$book = $result->fetch_assoc();
$stmt->close();

// Fetch related books
$related_query = "SELECT id, title, author, cover_image, price
                  FROM books
                  WHERE category_id = ? AND id != ?
                  LIMIT 4";
$related_stmt = $conn->prepare($related_query);
$related_stmt->bind_param("ii", $book['category_id'], $book_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_books = $related_result->fetch_all(MYSQLI_ASSOC);
$related_stmt->close();
$conn->close();

$cover = (!empty($book['cover_image']) && file_exists("images/" . $book['cover_image']))
    ? "images/" . $book['cover_image']
    : "images/bookspic.jpg";
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> | WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/book_detail.css?v=4.0">
</head>
<body>

<div class="container">
    <div class="breadcrumb">
        <a href="index.php">Home</a> / 
        <a href="books.php">Books</a> / 
        <span><?= htmlspecialchars($book['title']) ?></span>
    </div>

    <div class="book-detail">
        <div class="book-images">
            <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="main-image">
        </div>

        <div class="book-info">
            <span class="badge <?= ($book['stock'] > 0) ? 'in-stock' : 'out-of-stock' ?>">
                <?= ($book['stock'] > 0) ? 'In Stock' : 'Out of Stock' ?>
            </span>

            <h2><?= htmlspecialchars($book['title']) ?></h2>
            <p class="author">by <?= htmlspecialchars($book['author'] ?: 'Unknown') ?></p>

            <div class="price-section">
                <span class="price">RM <?= number_format($book['price'], 2) ?></span>
                <?php if (!empty($book['discount'])): ?>
                    <span class="old-price">RM <?= number_format($book['price'] + $book['discount'], 2) ?></span>
                    <span class="discount"><?= round(($book['discount'] / ($book['price'] + $book['discount'])) * 100) ?>% OFF</span>
                <?php endif; ?>
            </div>

            <p class="description">
                <?= nl2br(htmlspecialchars($book['description'] ?: 'No description available.')) ?>
            </p>

            <div class="details">
                <p><strong>Category:</strong> <?= htmlspecialchars($book['category_name'] ?: 'Uncategorized') ?></p>
                <p><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn'] ?: '-') ?></p>
                <p><strong>Stock:</strong> <?= htmlspecialchars($book['stock'] ?: '0') ?></p>
            </div>

            <div class="btn-group">
                <a href="cart_add.php?id=<?= $book['id'] ?>" class="btn btn-primary">🛒 Add to Cart</a>
                <a href="books.php" class="btn btn-outline">⬅ Back</a>
            </div>
        </div>
    </div>

    <div class="recommended">
        <h3>You May Also Like</h3>
        <div class="recommended-grid">
            <?php if (!empty($related_books)): ?>
                <?php foreach ($related_books as $related): ?>
                    <?php
                    $related_cover = (!empty($related['cover_image']) && file_exists("images/" . $related['cover_image']))
                        ? "images/" . $related['cover_image']
                        : "images/bookspic.jpg";
                    ?>
                    <div class="book-card">
                        <a href="book_details.php?id=<?= $related['id'] ?>">
                            <img src="<?= htmlspecialchars($related_cover) ?>" alt="<?= htmlspecialchars($related['title']) ?>">
                            <div class="card-body">
                                <h4><?= htmlspecialchars($related['title']) ?></h4>
                                <p class="author"><?= htmlspecialchars($related['author'] ?: 'Author Name') ?></p>
                                <p class="price">RM <?= number_format($related['price'], 2) ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No related books found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
