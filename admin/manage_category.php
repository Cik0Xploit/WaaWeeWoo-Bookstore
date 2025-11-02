<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}
include ("../function/connectdb.php");

// Fetch all categories
$query = "SELECT * FROM categories ORDER BY id ASC"; 
$result = mysqli_query($conn, $query);

$fullname = $_SESSION['fullname'] ?? 'Admin';
?>

<div class="admin-page-wrapper">
    <?php include "header.php"; // This likely contains the header and navigation ?>

    <main class="main-content">
        <div class="manage-categories-container">
            <h2>🏷️ Category Management</h2>
            <p>Use this panel to view, add, modify, or delete book categories.</p>

            <div class="top-controls">
                <a href='add_category.php' class='add-book-button'>➕ Add New Category</a>
            </div>
            
            <?php
            if (mysqli_num_rows($result) > 0) {
                echo "<div class='table-container'>";
                echo "<table class='inventory-table'>";
                echo "<thead>";
                echo "<tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>";
                echo "</thead>";
                echo "<tbody>";
                
                while($row = mysqli_fetch_assoc($result)){
                    echo"<tr>";
                    echo"<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo"<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo"<td class='action-cell'>";
                    // Added Update Link
                    echo"<a href='delete_category.php?id=" . $row['id'] . "' class='action-link delete' onclick=\"return confirm('Are you sure you want to delete category ID " . $row['id'] . "?');\" title='Delete Category'><i class='fas fa-trash-alt'></i> Delete</a>";
                    echo"</td>";
                    echo"</tr>";
                };
                
                echo "</tbody>";
                echo "</table>";
                echo "</div>"; // Close table-container
            } else {
                echo "<p class='no-records'>No categories found.</p>";
            }
            ?>
        </div>
    </main>
</div> 

</body>
</html>