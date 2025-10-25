<?php
// Always include DB connection before header
include "function/connectdb.php";
session_start();

include "header.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// === FETCH USER DATA ===
$stmt = $conn->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// === HANDLE FORM SUBMIT ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    // Basic validation
    $errors = [];
    if (empty($fullname)) $errors[] = "Full name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (!empty($phone) && !preg_match('/^[0-9+\-\s()]+$/', $phone)) $errors[] = "Invalid phone number format.";

    if (empty($errors)) {
        // Optional password update
        if (!empty($_POST["new_password"])) {
            $hashed = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?";
            $update = $conn->prepare($sql);
            $update->bind_param("sssssi", $fullname, $email, $phone, $address, $hashed, $user_id);
        } else {
            $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $update = $conn->prepare($sql);
            $update->bind_param("ssssi", $fullname, $email, $phone, $address, $user_id);
        }

        if ($update->execute()) {
            $_SESSION["success_message"] = "✅ Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "❌ Error updating profile. Please try again.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<link rel="stylesheet" href="css/profile.css">

<main class="profile-page">
    <h1>👤 My Profile</h1>

    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert success"><?php echo $_SESSION["success_message"]; unset($_SESSION["success_message"]); ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST" class="profile-form">
        <label>Full Name</label>
        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

        <label>Address</label>
        <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>

        <label>New Password (optional)</label>
        <input type="password" name="new_password" placeholder="Leave blank to keep current password">

        <button type="submit" class="btn-primary">Save Changes</button>
    </form>
</main>

<?php include "footer.php"; ?>
