<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include ("../function/connectdb.php");

// 2. Fetch data for current page with LIMIT/OFFSET
$query = "SELECT * FROM categories ORDER BY id ASC"; 
$result = mysqli_query($conn, $query);

$fullname = $_SESSION['fullname'] ?? 'Admin';
// --- END: NEW PAGINATION LOGIC ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/inventory.css">

</head>
<body>

<div class="admin-container">
    <?php include "sidebar.php"; ?>

    <main class="main-content">
        <h1 style="text-align: left;">Categories Viewer</h1>
        <p style="text-align: left; margin-bottom: 20px;">This is where you manage the entire collection of books, including adding, updating, and deleting entries.</p>

        <div style="width: 100%; text-align: right; margin-bottom: 15px;">
            <a href='add_category.php' class='add-book-button'>➕ Add New Category</a>
        </div>
        
        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table class='inventory-table'>";
            echo "<thead>";
            echo "<tr>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th colspan='2' class='action-header'>Actions</th>
                </tr>";
            echo "</thead>";
            echo "<tbody>";
            
            while($row = mysqli_fetch_assoc($result)){
                echo"<tr>";
                echo"<td>" . htmlspecialchars($row['id']) . "</td>";
                echo"<td>" . htmlspecialchars($row['name']) . "</td>";
                echo"<td class='action-cell'><a href='delete_book.php?id=" . $row['id'] . "' class='action-link delete' onclick=\"return confirm('Are you sure you want to delete book ID " . $row['id'] . "?');\">Delete Category</a></td>";
                echo"</tr>";
            };
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p style='text-align:center; margin-top: 50px;'>No books found in the inventory.</p>";
        }
        ?>

        </main>
</div>

</body>
</html>