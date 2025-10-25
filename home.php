<?php include "header.php"; ?>
<link rel="stylesheet" href="css/home.css">

<main class="homepage">

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to <span>WaaWeeWoo Bookstore</span></h1>
            <p>Your one-stop destination for great reads and endless imagination.</p>
            <a href="books.php" class="btn-primary">Browse Books</a>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="featured">
        <h2>✨ Featured Categories</h2>
        <div class="category-grid">
            <div class="category-card">
                <img src="images/fiction.jpg" alt="Fiction">
                <h3>Fiction</h3>
            </div>
            <div class="category-card">
                <img src="images/nonfiction.jpg" alt="Non-Fiction">
                <h3>Non-Fiction</h3>
            </div>
            <div class="category-card">
                <img src="images/scifi.jpg" alt="Science Fiction">
                <h3>Sci-Fi</h3>
            </div>
            <div class="category-card">
                <img src="images/children.jpg" alt="Children’s Books">
                <h3>Children</h3>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <h2>📖 About WaaWeeWoo</h2>
        <p>
            At WaaWeeWoo Bookstore, we believe every book holds a story waiting to change your world.
            Whether you’re chasing fantasy realms, business insights, or children’s adventures —
            we bring the joy of reading closer to you.
        </p>
        <a href="aboutUs.php" class="btn-secondary">Learn More</a>
    </section>


</main>

<?php include "footer.php"; ?>
