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
    $status     = mysqli_real_escape_string($conn, $_POST['status']);

    if (empty($title) || empty($author) || empty($isbn) || empty($price) || empty($stock)) {
        $error = "All fields are required.";
    } else {
        // Handle file upload for cover image
        $cover_image_update = '';
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $file_type = $_FILES['cover_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $file_name = $_FILES['cover_image']['name'];
                $file_tmp = $_FILES['cover_image']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = uniqid() . '.' . $file_ext;
                $upload_path = '../images/' . $new_file_name;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $cover_image_update = ", cover_image = '$new_file_name'";
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Only JPG and PNG files are allowed.";
            }
        }

        if (empty($error)) {
            // Execute the update query
            $update_query = "UPDATE books SET
                             title = '$title',
                             author = '$author',
                             isbn = '$isbn',
                             price = '$price',
                             stock = '$stock',
                             category_id = '$category_id',
                             status = '$status'
                             $cover_image_update
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
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/navbar.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include "header.php"; ?>

    <main class="main-content">
        <div class="dashboard-container">
            <h1><i class="fas fa-edit"></i> Update Book: <?= htmlspecialchars($book['title'] ?? 'N/A') ?></h1>
            <p>Use the form below to modify the details of this book.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if (isset($book)): ?>
                <div class="card">
                    <div class="card-body">
                        <form action="update_book.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                                <option value="<?= htmlspecialchars($cat['id']) ?>"
                                                    <?= $cat['id'] == $book['category_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price (RM) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($book['price']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Quantity In Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($book['stock']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active" <?= ($book['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= ($book['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="cover_image" class="form-label">Book Cover Image</label>
                                <input type="file" class="form-control" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png">
                                <div class="form-text">Only JPG and PNG files are allowed. Leave empty to keep current image.</div>
                                <?php if (!empty($book['cover_image'])): ?>
                                    <div class="mt-2">
                                        <img src="../images/<?= htmlspecialchars($book['cover_image']) ?>" alt="Current Cover" class="img-thumbnail" style="max-width: 100px; max-height: 150px;">
                                        <p class="mb-0 mt-1 text-muted">Current image: <?= htmlspecialchars($book['cover_image']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="update_book" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Book Details
                                </button>
                                <a href="manage_books.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
