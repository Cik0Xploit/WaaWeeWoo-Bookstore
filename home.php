<?php
include 'function/authenticate.php';
include 'header.php';
?>

<link rel="stylesheet" href="css/home.css">

<!-- ===== HERO SECTION ===== -->
<section class="hero">
  <div class="hero-text-box">
    <small>New Arrivals Every Week</small>
    <h1>Discover Your Next Favorite Book</h1>
    <p>Explore thousands of titles across every genre — from bestsellers to timeless classics. Find your next great read today.</p>
    <div class="hero-buttons">
      <a href="books.php">Browse Collection</a>
      <a href="bestsellers.php">View Bestsellers</a>
    </div>
  </div>
</section>

<!-- ===== CATEGORY SECTION ===== -->
<section class="categories container">
  <h2>Browse by Category</h2>
  <div class="category-grid">
    <?php
    $query = "SELECT * FROM categories ORDER BY id ASC";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $icon = '📚';
            switch ($row['name']) {
                case 'Self-help / Business': $icon = '💼'; break;
                case 'Biography / Science': $icon = '🔬'; break;
                case 'Programming / Technology': $icon = '💻'; break;
                case 'Light Novel': $icon = '📖'; break;
                case 'Thriller' : $icon = '🕵️‍♂️'; break;
            }

            echo '<div class="category-card">';
            echo '<div class="category-icon">'.$icon.'</div>';
            echo '<h3>'.htmlspecialchars($row['name']).'</h3>';
            echo '<p>'.htmlspecialchars($row['description'] ?? 'No description available.').'</p>';
            echo '</div>';
        }
    } else {
        echo '<p>No categories found.</p>';
    }
    ?>
  </div>
</section>

<!-- ===== FEATURED BOOKS SECTION ===== -->
<section class="featured-books container">
    <div class="featured-header">
    <div>
        <h2>Featured Books</h2>
        <p>Hand-picked selections from our top curators</p>
    </div>
    <a href="books.php">View All →</a>
  </div>    
  <div class="book-grid">
    <?php
    $bookQuery = "SELECT * FROM books WHERE id BETWEEN 1 AND 4 ORDER BY id ASC";
    $bookResult = $conn->query($bookQuery);

    if ($bookResult->num_rows > 0) {
        while ($book = $bookResult->fetch_assoc()) {
            // Get category name
            $catQuery = "SELECT name FROM categories WHERE id = " . intval($book['category_id']);
            $catResult = $conn->query($catQuery);
            $categoryName = $catResult->num_rows > 0 ? $catResult->fetch_assoc()['name'] : 'Uncategorized';

            echo '<a href="book_detailS.php?id='.$book['id'].'" class="book-link">';
            echo '<div class="book-card">';
            echo '<div class="book-image">';
            echo '<img src="images/'.$book['cover_image'].'" alt="'.htmlspecialchars($book['title']).'">';
            echo '<span class="book-tag">'.htmlspecialchars($categoryName).'</span>';
            echo '</div>';
            echo '<div class="book-info">';
            echo '<h3>'.htmlspecialchars($book['title']).'</h3>';
            echo '<p>by '.htmlspecialchars($book['author']).'</p>';
            echo '<div class="book-price">$'.number_format($book['price'], 2).'</div>';
            echo '</div>';
            echo '</div>';
            echo '</a>';
        }
    } else {
        echo '<p>No featured books available.</p>';
    }
    ?>
  </div>
</section>

<?php
include 'footer.php';
?>
