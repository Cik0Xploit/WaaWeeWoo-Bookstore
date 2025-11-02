<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include ("../function/connectdb.php");
$query = "SELECT * FROM users ORDER BY id ASC"; 
$result = mysqli_query($conn, $query);

$fullname = $_SESSION['full_name'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books - WaaWeeWoo Bookstore</title>
    
    <link rel="stylesheet" href="dash.css"> 
    <link rel="stylesheet" href="navbar.css"> 
    

</head>
<body>


<div class="admin-page-wrapper">
    <?php include "header.php"; // This likely contains the header and navigation ?>

    <main class="main-content">
        <div class="manage-categories-container">
            <h2>🏷️ User Management</h2>
            <p>Use this panel to view, add, modify, or delete book User.</p>
            
            <?php
            if (mysqli_num_rows($result) > 0) {
                echo "<div class='table-container'>";
                echo "<table class='inventory-table'>";
                echo "<thead>";
                echo "<tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Joined Date</th>
                        </tr>";
                echo "</thead>";
                echo "<tbody>";
                
                while($row = mysqli_fetch_assoc($result)){
                    echo"<tr>";
                    echo"<td>" . htmlspecialchars($row['full_name']) . "</td>";
                    echo"<td>" . htmlspecialchars($row['role']) . "</td>";
                    echo"<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo"<td>" . htmlspecialchars($row['created_at']) . "</td>";
                    echo"<td class='action-cell'>";
                    // Added Update Link
                    echo"<a href='delete_user.php?id=" . $row['id'] . "' class='action-link delete' onclick=\"return confirm('Are you sure you want to delete category ID " . $row['id'] . "?');\" title='Delete User'><i class='fas fa-trash-alt'></i> Delete</a>";
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
            <a href='user_log.php' class='add-book-button'>➕ View User Logs</a>
        </div>
    </main>
</div> 


</body>
</html>