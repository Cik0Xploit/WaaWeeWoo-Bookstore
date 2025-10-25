<?php
// admin/update_book.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php'; // DB connection

$fullname = $_SESSION['fullname'] ?? 'Admin';
$error = '';
$success = '';

// --- 1. GET (Display Form) Logic ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $book_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Fetch book details
    $fetch_query = "SELECT * FROM books WHERE id = '$book_id' LIMIT 1";
    $result = mysqli_query($conn, $fetch_query);
    
    if (mysqli_num_rows($result) === 0) {
        $error = "Error: Book not found.";
    } else {
        $book = mysqli_fetch_assoc($result);
    }
    
    // Fetch categories for the dropdown menu
    $categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
    $categories_result = mysqli_query($conn, $categories_query);

} else if (isset($_POST['update_book'])) {
    
    // --- 2. POST (Form Submission) Logic ---
    
    // Basic form validation and sanitization
    $book_id    = mysqli_real_escape_string($conn, $_POST['id']);
    $title      = mysqli_real_escape_string($conn, $_POST['title']);
    $author     = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn       = mysqli_real_escape_string($conn, $_POST['isbn']);
    $price      = mysqli_real_escape_string($conn, $_POST['price']);
    $stock      = mysqli_real_escape_string($conn, $_POST['stock']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    // NOTE: You would handle image upload logic here if you want to allow changing the cover image
    
    if (empty($title) || empty($author) || empty($isbn) || empty($price) || empty($stock)) {
        $error = "All fields are required.";
    } else {
        // Execute the update query
        $update_query = "UPDATE books SET 
                         title = '$title',
                         author = '$author',
                         isbn = '$isbn',
                         price = '$price',
                         stock = '$stock',
                         category_id = '$category_id'
                         WHERE id = '$book_id'";
                         
        if (mysqli_query($conn, $update_query)) {
            $success = "Book '{$title}' successfully updated!";
            // Re-fetch the updated data to refresh the form
            $fetch_query = "SELECT * FROM books WHERE id = '$book_id' LIMIT 1";
            $book = mysqli_fetch_assoc(mysqli_query($conn, $fetch_query));
        } else {
            $error = "Update failed: " . mysqli_error($conn);
        }
    }
    
    // Re-fetch categories for the dropdown menu in case of error
    $categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
    $categories_result = mysqli_query($conn, $categories_query);

} else {
    header("Location: manage_book.php?error=No book ID specified for update.");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Book - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/book_update.css">
</head>
<body>

<div class="admin-container">
    <?php include "sidebar.php"; ?>

    <main class="main-content">
        <h1>Update Book: <?= htmlspecialchars($book['title'] ?? 'N/A') ?></h1>
        <p>Use the form below to modify the details of this book.</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($book)): ?>
            <div class="form-card">
                <form action="update_book.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
                    
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" required>
                            <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>" 
                                    <?= $cat['id'] == $book['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (RM)</label>
                        <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($book['price']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Quantity In Stock</label>
                        <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($book['stock']) ?>" required>
                    </div>
                    
                    <button type="submit" name="update_book" class="submit-btn">Update Book Details</button>
                    <a href="/MiniProject2025/admin/manage_book.php" style="margin-left: 15px; color: #555;">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>