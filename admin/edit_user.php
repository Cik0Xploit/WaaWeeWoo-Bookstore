<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

require_once '../function/connectdb.php';

$message = "";
$user = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Fetch user details
    $user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $result = $user_query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $message = "User not found.";
    }
} else {
    $message = "Invalid user ID.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];

    if (empty($full_name) || empty($email)) {
        $message = "Full name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        // Check if email is already taken by another user
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check->bind_param("si", $email, $user_id);
        $email_check->execute();
        if ($email_check->get_result()->num_rows > 0) {
            $message = "Email address is already in use by another user.";
        } else {
            // Update user
            $update_query = $conn->prepare("
                UPDATE users
                SET full_name = ?, email = ?, phone = ?, address = ?, role = ?
                WHERE id = ?
            ");
            $update_query->bind_param("sssssi", $full_name, $email, $phone, $address, $role, $user_id);

            if ($update_query->execute()) {
                $message = "User updated successfully.";
                // Refresh user data
                header("Location: edit_user.php?id=" . $user_id . "&success=1");
                exit();
            } else {
                $message = "Failed to update user.";
            }
        }
    }
}

// Check for success message
if (isset($_GET['success'])) {
    $message = "User updated successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - WaaWeeWoo Bookstore</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .edit-form { max-width: 600px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-update { background: #8b5cf6; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-update:hover { background: #7c3aed; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include "header.php"; ?>

    <main class="main-content">
        <h1>Edit User</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <div class="edit-form">
                <form method="post">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

                    <div class="form-group">
                        <label>User ID:</label>
                        <input type="text" value="#<?php echo $user['id']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name:</label>
                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea name="address" id="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select name="role" id="role" required>
                            <option value="member" <?php echo ($user['role'] === 'member') ? 'selected' : ''; ?>>Member</option>
                            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Joined Date:</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['created_at']); ?>" readonly>
                    </div>

                    <button type="submit" name="update_user" class="btn-update">Update User</button>
                    <a href="manage_user.php" style="margin-left: 10px; color: #666;">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <p>User not found or invalid user ID.</p>
            <a href="manage_user.php">Back to User Management</a>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
<?php $conn->close(); ?>
