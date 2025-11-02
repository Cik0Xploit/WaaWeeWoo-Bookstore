<?php
// admin/add_book.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php'; // Updated path to connectdb.php
$fullname = $_SESSION['fullname'] ?? 'Admin';
$error = '';
$success = '';

// Fetch categories for the dropdown menu
$categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Standardize column names based on inventory: stock, category_id, price
    $isbn           = mysqli_real_escape_string($conn, $_POST['isbn']);
    $title          = mysqli_real_escape_string($conn, $_POST['title']);
    $author         = mysqli_real_escape_string($conn, $_POST['author']);
    $category_id    = mysqli_real_escape_string($conn, $_POST['category_id']);
    $stock          = mysqli_real_escape_string($conn, $_POST['stock']);
    $desc          = mysqli_real_escape_string($conn, $_POST['description']);
    $price          = mysqli_real_escape_string($conn, $_POST['price']);
    $cover_image    = ''; // Placeholder for file name
    
    // Simple validation (can be expanded)
    if (empty($title) || empty($isbn) || empty($category_id) || empty($price)) {
        $error = "Title, ISBN, Category, and Price are required fields.";
    } else {
        $cover_image = 'default.jpg'; 
        
        $query = "INSERT INTO books (isbn, title, author, category_id, stock, description, price, cover_image)
                  VALUES ('$isbn', '$title', '$author', '$category_id', '$stock', '$desc', '$price', '$cover_image')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = "New Book '{$title}' successfully added!";
            header("Location: manage_book.php"); // Redirect to inventory after success
            exit();
        } else {
            $error = "Error adding book: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="dash.css"> 
    <link rel="stylesheet" href="navbar.css"> 
    <link rel="stylesheet" href="add_book.css"> 

    
</head>
<body>
<div class="admin-container">
    <?php include "header.php"; ?>

    <main class="main-content">
        <div class="form-card large-form"> 
            <div class="card-header">
                <h2>Add New Book</h2>
                <a href="manage_books.php" class="close-icon">&times;</a>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form action="add_book.php" method="POST" enctype="multipart/form-data" class="book-form-grid">
                
                <div class="form-group span-col-1">
                    <label for="title">Book Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter book title" required>
                </div>
                <div class="form-group span-col-1">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" placeholder="Enter author name" required>
                </div>

                <div class="form-group span-col-1">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" placeholder="Enter ISBN" required>
                </div>
                <div class="form-group span-col-1">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category</option>
                        <?php mysqli_data_seek($categories_result, 0); // Reset pointer for use ?>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group span-col-1">
                    <label for="price">Price (RM)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" value="0.00" required>
                </div>
                <div class="form-group span-col-1">
                    <label for="stock">Stock Quantity</label>
                    <input type="number" id="stock" name="stock" min="0" value="0" required>
                </div>

                <div class="form-group span-col-2">
                    <label for="desc">Description</label>
                    <textarea id="desc" name="description" rows="5" placeholder="Enter book description" required></textarea>
                </div>
                
                <div class="form-actions span-col-2">
                    <a href="manage_books.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Add Book</button>
                </div>
                
            </form>
        </div>
    </main>
</div>

</body>
</html>