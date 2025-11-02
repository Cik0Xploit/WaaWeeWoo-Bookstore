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

// Fetch reviews for this book
$reviews_query = "SELECT r.*, u.full_name
                  FROM reviews r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.book_id = ?
                  ORDER BY r.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $book_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);
$reviews_stmt->close();

// Calculate average rating
$avg_rating = 0;
$total_reviews = count($reviews);
if ($total_reviews > 0) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $total_rating / $total_reviews;
}

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
                <?php if ($book['stock'] > 0): ?>
                    <a href="cart_add.php?book_id=<?= $book['id'] ?>" class="btn btn-primary" onclick="addToCart(<?= $book['id'] ?>, '<?= htmlspecialchars(addslashes($book['title'])) ?>'); return false;">🛒 Add to Cart</a>
                <?php else: ?>
                    <span class="btn btn-disabled">Out of Stock</span>
                <?php endif; ?>
                <a href="books.php" class="btn btn-outline">⬅ Back</a>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <h3>Customer Reviews</h3>

        <?php if ($total_reviews > 0): ?>
            <div class="rating-summary">
                <div class="avg-rating">
                    <span class="rating-number"><?= number_format($avg_rating, 1) ?></span>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= round($avg_rating) ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="review-count">(<?= $total_reviews ?> review<?= $total_reviews > 1 ? 's' : '' ?>)</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="reviews-list">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <strong><?= htmlspecialchars($review['full_name'] ?: 'Anonymous') ?></strong>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-date">
                                <?= date('M d, Y', strtotime($review['created_at'])) ?>
                            </div>
                        </div>
                        <div class="review-comment">
                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-reviews">No reviews yet. Be the first to review this book!</p>
            <?php endif; ?>
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

<script>
function addToCart(bookId, bookTitle) {
    // Check if user is logged in
    <?php if (!isset($_SESSION['user_id'])): ?>
        alert('Please login first to add items to cart.');
        window.location.href = 'login.php';
        return;
    <?php endif; ?>

    // Create AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'cart_add.php?book_id=' + bookId, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                var response = xhr.responseText;
                if (response.startsWith('Success:')) {
                    // Success - show alert
                    alert('"' + bookTitle + '" has been added to your cart!');
                    // Optional: Update cart count in header if you have one
                    // updateCartCount();
                } else if (response.startsWith('Error:')) {
                    // Error - show specific error message
                    alert(response.replace('Error: ', ''));
                } else {
                    alert('Error adding item to cart. Please try again.');
                }
            } else {
                // HTTP error
                alert('Error adding item to cart. Please try again.');
            }
        }
    };
    xhr.send();
}
</script>
</body>
</html>
