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
    $status         = mysqli_real_escape_string($conn, $_POST['status']);
    $cover_image    = ''; // Placeholder for file name

    // Simple validation (can be expanded)
    if (empty($title) || empty($isbn) || empty($category_id) || empty($price)) {
        $error = "Title, ISBN, Category, and Price are required fields.";
    } else {
        // Handle file upload
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
                    $cover_image = $new_file_name;
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Only JPG and PNG files are allowed.";
            }
        }

        if (empty($error)) {
            if (empty($cover_image)) {
                $cover_image = 'bookspic.jpg'; // Default image
            }

            $query = "INSERT INTO books (isbn, title, author, category_id, stock, description, price, cover_image, status)
                      VALUES ('$isbn', '$title', '$author', '$category_id', '$stock', '$desc', '$price', '$cover_image', '$status')";

            if (mysqli_query($conn, $query)) {
                $_SESSION['message'] = "New Book '{$title}' successfully added!";
                header("Location: manage_books.php"); // Redirect to inventory after success
                exit();
            } else {
                $error = "Error adding book: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book - WaaWeeWoo Bookstore</title>
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
            <h1><i class="fas fa-plus"></i> Add New Book</h1>
            <p>Use the form below to add a new book to the inventory.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Book Details</h5>
                    <a href="manage_books.php" class="btn-close" aria-label="Close"></a>
                </div>
                <div class="card-body">
                    <form action="add_book.php" method="POST" enctype="multipart/form-data">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Book Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="Enter book title" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="author" name="author" placeholder="Enter author name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="isbn" name="isbn" placeholder="Enter ISBN" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select category</option>
                                        <?php mysqli_data_seek($categories_result, 0); // Reset pointer for use ?>
                                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (RM) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" value="0.00" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="0" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cover_image" class="form-label">Book Cover Image</label>
                            <input type="file" class="form-control" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png">
                            <div class="form-text">Only JPG and PNG files are allowed. Leave empty to use default image.</div>
                        </div>

                        <div class="mb-3">
                            <label for="desc" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="desc" name="description" rows="5" placeholder="Enter book description" required></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Book
                            </button>
                            <a href="manage_books.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
