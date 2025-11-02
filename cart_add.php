<?php
require_once 'function/connectdb.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = intval($_GET['book_id'] ?? 0);

if ($book_id > 0) {
    // Check if book exists and is in stock
    $book_check = $conn->prepare("SELECT stock FROM books WHERE id = ?");
    $book_check->bind_param("i", $book_id);
    $book_check->execute();
    $book_result = $book_check->get_result();

    if ($book_result->num_rows === 0) {
        echo "Error: Book not found";
        exit();
    }

    $book = $book_result->fetch_assoc();
    if ($book['stock'] <= 0) {
        echo "Error: Book out of stock";
        exit();
    }

    // Check if already in cart
    $check = $conn->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND book_id = ?");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update quantity
        $cart = $result->fetch_assoc();
        $new_qty = $cart['quantity'] + 1;
        $update = $conn->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $new_qty, $cart['id']);
        $update->execute();
    } else {
        // Insert new record
        $insert = $conn->prepare("INSERT INTO carts (user_id, book_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $user_id, $book_id);
        $insert->execute();
    }

    echo "Success: Item added to cart";
    exit();
} else {
    echo "Error: Invalid book ID";
    exit();
}
