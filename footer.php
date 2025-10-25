<?php
// footer.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WaaWeeWoo Bookstore Footer</title>
  <link rel="stylesheet" href="css/footer.css">
</head>
<body>

  <!-- ===== FOOTER SECTION ===== -->
  <footer class="site-footer">
    <div class="footer-container">
      <h3>WaaWeeWoo Bookstore</h3>
      <p>
        At WaaWeeWoo Bookstore, we believe every page holds a new world to explore.
        From timeless classics to modern masterpieces, our curated collection connects readers
        with stories that spark curiosity, inspire thought, and nurture a lifelong love for reading.
      </p>

      <div class="footer-links">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="books.php">Books</a></li>
          <li><a href="aboutUs.php">About Us</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y'); ?> WaaWeeWoo Bookstore. All rights reserved.</p>
    </div>
  </footer>

  <!-- ===== BACK TO TOP BUTTON ===== -->
  <button id="backToTop" title="Go to top">↑</button>

  <script>
    const backToTop = document.getElementById('backToTop');

    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        backToTop.classList.add('show');
      } else {
        backToTop.classList.remove('show');
      }
    });

    backToTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  </script>

</body>
</html>
