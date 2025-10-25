<?php
include "function/connectdb.php";
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/search.css">
</head>
<body>
    <div class="search-results">
        <?php
        if ($q !== '') {
            // Secure prepared statement
            $stmt = $conn->prepare("SELECT * FROM books WHERE title LIKE ?");
            $searchTerm = "%$q%";
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<h2>Search results for '<b>" . htmlspecialchars($q) . "</b>'</h2>";

            if ($result->num_rows > 0) {
                // ✅ If exactly 1 match → redirect straight to details page
                if ($result->num_rows === 1) {
                    $row = $result->fetch_assoc();
                    header("Location: book_details.php?id=" . urlencode($row['id']));
                    exit;
                }

                // Otherwise, list all results
                while ($row = $result->fetch_assoc()) {
                    echo "
                    <div class='result-item'>
                        <a href='book_details.php?id={$row['id']}'>" . htmlspecialchars($row['title']) . "</a>
                    </div>";
                }
            } else {
                echo "<p class='no-result'>No books found matching '<b>" . htmlspecialchars($q) . "</b>'.</p>";
            }
        } else {
            echo "<p class='no-result'>Please enter a search term.</p>";
        }
        ?>
    </div>
</body>
</html>
