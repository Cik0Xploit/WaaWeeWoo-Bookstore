<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}


// Note: The session/security/variable initialization should be in header.php, 
// but since the original code had it here, we will include the necessary files first.

// Include header.php (which should contain session_start() and security checks)
include "header.php"; 

// Since header.php already handles the security, we can proceed with DB logic.
include ("../function/connectdb.php");

// --- START: PAGINATION LOGIC ---
$limit = 10; // Number of records to show per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit; // Starting record for the query

// 1. Get total records
$count_query = "SELECT COUNT(id) AS total FROM books";
$count_result = mysqli_query($conn, $count_query);
$total_books = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_books / $limit);

// Ensure the requested page is valid
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $start = ($page - 1) * $limit;
}

// 2. Fetch data for current page with LIMIT/OFFSET
$query = "SELECT b.*, c.name AS category_name 
          FROM books b 
          JOIN categories c ON b.category_id = c.id 
          ORDER BY b.id ASC 
          LIMIT $start, $limit"; // Apply LIMIT and OFFSET
$result = mysqli_query($conn, $query);
// --- END: PAGINATION LOGIC ---
?>

<main class="main-content"> <h1 style="text-align: left;">Book Inventory</h1>
        <p style="text-align: left; margin-bottom: 20px;">This is where you manage the entire collection of books, including adding, updating, and deleting entries.</p>

        <div style="width: 100%; text-align: right; margin-bottom: 15px;">
            <a href='add_book.php' class='add-book-button'>➕ Add New Book</a>
        </div>
        
        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table class='inventory-table'>";
            echo "<thead>";
            echo "<tr>
                        <th>Book ID</th>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Image</th>
                        <th colspan='2' class='action-header'>Actions</th>
                    </tr>";
            echo "</thead>";
            echo "<tbody>";
            
            while($row = mysqli_fetch_assoc($result)){
                echo"<tr>";
                echo"<td>" . htmlspecialchars($row['id']) . "</td>";
                echo"<td>" . htmlspecialchars($row['isbn']) . "</td>";
                echo"<td>" . htmlspecialchars($row['title']) . "</td>";
                echo"<td>" . htmlspecialchars($row['author']) . "</td>";
                echo"<td>" . htmlspecialchars($row['category_name']) . "</td>";
                echo"<td>RM " . htmlspecialchars($row['price']) . "</td>";
                echo"<td>" . htmlspecialchars($row['stock']) . "</td>";
                echo"<td class='image-cell'><img src='../images/" . htmlspecialchars($row['cover_image']) . "' alt='Book Cover' width='80' height='110'></td>";
                echo"<td class='action-cell'><a href='update_book.php?id=" . $row['id'] . "' class='action-link update'>Update Book</a></td>";
                echo"<td class='action-cell'><a href='delete_book.php?id=" . $row['id'] . "' class='action-link delete' onclick=\"return confirm('Are you sure you want to delete book ID " . $row['id'] . "?');\">Delete Book</a></td>";
                echo"</tr>";
            };
            
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p style='text-align:center; margin-top: 50px;'>No books found in the inventory.</p>";
        }
        ?>

        <?php if ($total_pages > 1): ?>
            <div style="text-align:center; margin-top:20px;">
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
        <?php endif; ?>
    </main>
    
</body>
</html>