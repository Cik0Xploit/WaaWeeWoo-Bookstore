<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php';
$fullname = $_SESSION['fullname'] ?? 'Admin';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = mysqli_real_escape_string($conn, $_POST['name']);
    
    if (empty($category)) {
        $error = "Category is a required fields.";
    } else {
        $query = "INSERT INTO categories (name) VALUES ('$category')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = "New Category '{$category}' successfully added!";
            header("Location: manage_category.php"); // Redirect to inventory after success
            exit();
        } else {
            $error = "Error adding Category: " . mysqli_error($conn);
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

<div class="dashboard-container">
    <h1>Add New Category</h1>
    <p>Use the form below to add a new category to the system.</p>

    <div class="content-card">
        <div class="form-card large-form">
            <div class="card-header">
                <h2>Add New Category</h2>
                <a href="manage_category.php" class="close-icon">&times;</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form action="add_category.php" method="POST">
                <div class="form-group">
                    <label for="name">Category Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter category name" required>
                </div>

                <div class="form-actions">
                    <a href="manage_category.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
