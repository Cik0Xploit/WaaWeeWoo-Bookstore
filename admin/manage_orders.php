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
$query = "SELECT * FROM orders ORDER BY id ASC";
$querycustomer = "SELECT * FROM order_items ORDER BY id ASC"; 
$result = mysqli_query($conn, $query);
$resultcustomer = mysqli_query($conn, $querycustomer);

$fullname = $_SESSION['fullname'] ?? 'Admin';
// --- END: NEW PAGINATION LOGIC ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="dash.css"> 
    <link rel="stylesheet" href="navbar.css"> 

</head>
<body>

<div class="admin-container">
    <?php include "header.php"; ?>

    <main class="main-content">
        <h1 style="text-align: left;">Ongoing Orders</h1>
        <p style="text-align: left; margin-bottom: 20px;">This is where you manage orders, including viewing and managing order details.</p>

        <div style="width: 100%; text-align: right; margin-bottom: 15px;">
            <a href='add_order.php' class='add-order-button'>➕ Add New Order</a>
        </div>

        <?php
        echo "<table class='inventory-table'>";
        echo "<thead>";
        echo "<tr>
            <th>Order ID</th>
            <th>Status</th>
            <th colspan='2' class='action-header'>Actions</th>
            </tr>";
        echo "</thead>";
        echo "<tbody>";
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)){
                echo"<tr>";
                echo"<td>" . htmlspecialchars($row['id']) . "</td>";
                echo"<td>" . htmlspecialchars($row['status']) . "</td>";
                echo"<td class='action-cell'><a href='delete_book.php?id=" . $row['id'] . "' class='action-link delete' onclick=\"return confirm('Are you sure you want to delete order ID " . $row['id'] . "?');\">Delete Order</a></td>";
                echo"</tr>";
            };
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p style='text-align:center; margin-top: 50px;'>No Orders found in the inventory.</p>";
        }
        ?>

        </main>
</div>

</body>
</html>