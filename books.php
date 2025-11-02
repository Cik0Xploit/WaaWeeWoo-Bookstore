<?php
session_start();
require_once 'function/connectdb.php';
include_once 'header.php';

// Helper function to build query string with current params
function buildQueryString($params = []) {
    $current = $_GET;
    foreach ($params as $key => $value) {
        $current[$key] = $value;
    }
    return http_build_query($current);
}

// Pagination
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search & Filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$rating = isset($_GET['rating']) ? floatval($_GET['rating']) : 0;
$in_stock = isset($_GET['in_stock']) ? 1 : 0;

// Sort
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';
$orderBy = "b.id ASC";
if ($sort === 'price_asc') $orderBy = "b.price ASC";
elseif ($sort === 'price_desc') $orderBy = "b.price DESC";
elseif ($sort === 'rating_desc') $orderBy = "b.rating DESC"; // assume rating column exists

// Build WHERE clause
$where = [];
if ($search) $where[] = "(b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.isbn LIKE '%$search%')";
if ($category) $where[] = "b.category_id = $category";
if ($rating) $where[] = "b.rating >= $rating";
if ($in_stock) $where[] = "b.stock > 0";

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Fetch books
$bookQuery = "
    SELECT b.*, c.name AS category_name
    FROM books b
    LEFT JOIN categories c ON b.category_id = c.id
    $whereSql
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
";
$bookResult = mysqli_query($conn, $bookQuery);
if (!$bookResult) {
    die("Error fetching books: " . mysqli_error($conn));
}

// Count total books
$countQuery = "SELECT COUNT(*) AS total FROM books b $whereSql";
$totalResult = mysqli_query($conn, $countQuery);
if (!$totalResult) {
    die("Error counting books: " . mysqli_error($conn));
}
$totalBooks = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalBooks / $limit);

// Fetch categories for filters
$catResult = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
if (!$catResult) {
    die("Error fetching categories: " . mysqli_error($conn));
}
?>

<link rel="stylesheet" href="css/books.css">

<section class="books-page container">
    <h1>Browse Our Collection</h1>

    <!-- Search -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search for books, authors, or ISBN..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <div class="books-wrapper">
        <!-- Filters Sidebar -->
        <aside class="filters">
            <h3>Filters</h3>
            <form method="GET">
                <div class="filter-section">
                    <h4>Category</h4>
                    <?php while ($cat = mysqli_fetch_assoc($catResult)): ?>
                        <label>
                            <input type="radio" name="category" value="<?= $cat['id'] ?>" <?= $category==$cat['id']?'checked':'' ?> onchange="this.form.submit()">
                            <?= htmlspecialchars($cat['name']) ?>
                        </label>
                    <?php endwhile; ?>
                </div>

                <div class="filter-section">
                    <h4>Rating</h4>
                    <?php for ($i=4; $i>=1; $i--): ?>
                        <label>
                            <input type="radio" name="rating" value="<?= $i ?>" <?= $rating==$i?'checked':'' ?> onchange="this.form.submit()">
                            <?= $i ?> & up
                        </label>
                    <?php endfor; ?>
                </div>

                <div class="filter-section">
                    <label>
                        <input type="checkbox" name="in_stock" value="1" <?= $in_stock?'checked':'' ?> onchange="this.form.submit()">
                        In Stock Only
                    </label>
                </div>
            </form>
        </aside>

        <!-- Books Grid -->
        <main class="books-content">
            <div class="sort-bar">
                <label>Sort by:</label>
                <select onchange="window.location.href='?' + buildQueryString({sort: this.value})">
                    <option value="featured" <?= $sort=='featured'?'selected':'' ?>>Featured</option>
                    <option value="price_asc" <?= $sort=='price_asc'?'selected':'' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>Price: High to Low</option>
                    <option value="rating_desc" <?= $sort=='rating_desc'?'selected':'' ?>>Rating</option>
                </select>
            </div>

            <div class="books-grid">
                <?php if (mysqli_num_rows($bookResult) > 0): ?>
                    <?php while ($book = mysqli_fetch_assoc($bookResult)): ?>
                        <div class="book-card">
                            <a href="book_details.php?id=<?= $book['id'] ?>">
                                <img src="images/<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="author"><?= htmlspecialchars($book['author']) ?></p>
                            <p class="category"><?= htmlspecialchars($book['category_name']) ?></p>
                            <p class="price">RM<?= number_format($book['price'],2) ?></p>
                            <?php if ($book['stock']>0): ?>
                                <a href="cart_add.php?id=<?= $book['id'] ?>" class="btn">🛒 Add to Cart</a>
                            <?php else: ?>
                                <span class="out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No books found.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page>1): ?>
                    <a href="?<?= buildQueryString(['page' => $page-1]) ?>" class="btn">Previous</a>
                <?php endif; ?>
                <?php for ($i=1; $i<=$totalPages; $i++): ?>
                    <a href="?<?= buildQueryString(['page' => $i]) ?>" class="btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page<$totalPages): ?>
                    <a href="?<?= buildQueryString(['page' => $page+1]) ?>" class="btn">Next</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</section>

<?php include_once 'footer.php'; ?>
