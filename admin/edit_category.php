<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php';
$fullname = $_SESSION['fullname'] ?? 'Admin';
$error = '';
$success = '';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_category.php");
    exit();
}

// Fetch category details
$query = "SELECT * FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    header("Location: manage_category.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        $update_query = "UPDATE categories SET name = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $name, $id);

        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['message'] = "Category '{$name}' updated successfully!";
            header("Location: manage_category.php");
            exit();
        } else {
            $error = "Error updating category: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/add_book.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include "header.php";?>
<main class="main-content">  
    <div class="dashboard-container">
        <h1><i class="fas fa-edit"></i>Edit Category</h1>
        <p>Update the category details below.</p>

        <div class="content-card">
            <div class="form-card large-form">
                <div class="card-header">
                    <h2>Edit Category</h2>
                    <a href="manage_category.php" class="close-icon">&times;</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert-error"><?= $error ?></div>
                <?php endif; ?>

                <form action="edit_category.php?id=<?= $id ?>" method="POST">
                    <div class="form-group">
                        <label for="name">Category Name:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>

                    <div class="form-actions">
                        <a href="manage_category.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
