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
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/category_add.css">
</head>
<body>

<div class="admin-container">
    <?php include "sidebar.php"; ?>

    <main class="main-content">
        <h1>Register New Category 📚</h1>
        <p>Please enter the new Category.</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form action="../admin/add_category.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <input type="text" id="category" name="name" required>
                </div>

                <button type="submit" class="submit-btn">Add Category</button>
                <a href="manage_category.php" style="margin-left: 15px; color: #555;">Cancel</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>
