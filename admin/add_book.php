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
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/book_add.css">
    
</head>
<body>

<div class="admin-container">
    <?php include "sidebar.php"; ?>

    <main class="main-content">
        <h1>Register New Book 📚</h1>
        <p>Please enter the details for the new book.</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form action="../admin/add_book.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="isbn">ISBN:</label>
                    <input type="text" id="isbn" name="isbn" required>
                </div>
                
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="author">Author:</label>
                    <input type="text" id="author" name="author" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="publication">Publication Year:</label>
                    <input type="number" id="publication" name="publication" min="1000" max="<?= date('Y') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Quantity In Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>

                <div class="form-group">
                    <label for="desc">Description:</label>
                    <input type="text" id="desc" name="description" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (RM):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label for="cover_image">Cover Image:</label>
                    <input type="file" id="cover_image" name="cover_image">
                </div>
                
                <button type="submit" class="submit-btn">Add Book</button>
                <a href="manage_book.php" style="margin-left: 15px; color: #555;">Cancel</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>