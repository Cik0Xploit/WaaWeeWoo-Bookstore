<?php
session_start();
include 'function/connectdb.php'; // Make sure this file sets $conn

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $book_id = $_POST['book_id'];
    $quantity = $_POST['quantity'];

    $update = $conn->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND book_id = ?");
    $update->bind_param("iii", $quantity, $user_id, $book_id);
    $update->execute();
}

// Handle item removal
if (isset($_GET['remove'])) {
    $book_id = $_GET['remove'];

    $delete = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND book_id = ?");
    $delete->bind_param("ii", $user_id, $book_id);
    $delete->execute();
}

// Fetch cart items
$query = $conn->prepare("
    SELECT b.id, b.title, b.price, c.quantity, (b.price * c.quantity) AS total
    FROM carts c
    JOIN books b ON c.book_id = b.id
    WHERE c.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$total_price = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="cart-container">
    <h1>Your Shopping Cart</h1>

    <?php if ($result->num_rows > 0): ?>
        <table class="cart-table">
            <tr>
                <th>Book Title</th>
                <th>Price (RM)</th>
                <th>Quantity</th>
                <th>Total (RM)</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): 
                $total_price += $row['total'];
            ?>
            <tr>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= number_format($row['price'], 2); ?></td>
                <td>
                    <form method="post" class="update-form">
                        <input type="hidden" name="book_id" value="<?= $row['id']; ?>">
                        <input type="number" name="quantity" value="<?= $row['quantity']; ?>" min="1">
                        <button type="submit" name="update_cart">Update</button>
                    </form>
                </td>
                <td><?= number_format($row['total'], 2); ?></td>
                <td><a href="?remove=<?= $row['id']; ?>" class="remove-btn">Remove</a></td>
            </tr>
            <?php endwhile; ?>

            <tr class="cart-total">
                <td colspan="3" style="text-align:right; font-weight:600;">Grand Total:</td>
                <td colspan="2"><strong>RM <?= number_format($total_price, 2); ?></strong></td>
            </tr>
        </table>

        <div class="cart-actions">
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <p class="empty-cart">Your cart is empty. <a href="books.php">Continue Shopping</a></p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
