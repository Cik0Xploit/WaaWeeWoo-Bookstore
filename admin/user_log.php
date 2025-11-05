<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include "../function/connectdb.php";
$fullname = $_SESSION['fullname'] ?? 'Admin';

// Fetch all user logins
$query = "
    SELECT l.id, u.full_name, u.email, l.login_time
    FROM user_logins l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.login_time DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Logs - WaaWeeWoo Admin</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>

<div class="admin-container">
    <?php include "header.php"; ?>

    <main class="main-content">
        <div class="dashboard-container">
            <h1>🕓 User Login Logs</h1>
            <p>Below is a record of all user login activity.</p>

        <table class="log-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Login Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['login_time']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No login records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </main>
</div>

</body>
</html>
