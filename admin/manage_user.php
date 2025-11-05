<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include ("../function/connectdb.php");
$query = "SELECT * FROM users ORDER BY id ASC"; 
$result = mysqli_query($conn, $query);

$fullname = $_SESSION['full_name'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books - WaaWeeWoo Bookstore</title>
    
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/navbar.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .btn-group .btn {
            margin-right: 0.25rem;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .text-danger {
            color: #dc3545 !important;
            font-weight: 500;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            color: #007bff;
            border-color: #dee2e6;
        }

        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .dashboard-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-container h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .dashboard-container > p {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-group {
                flex-direction: column;
                gap: 0.25rem;
            }

            .btn-group .btn {
                margin-right: 0;
                width: 100%;
            }
        }
    </style>
    

</head>
<body>


<div class="admin-page-wrapper">
    <?php include "header.php"; // This likely contains the header and navigation ?>

    <main class="main-content">
        <div class="dashboard-container">
            <h1>🏷️ User Management</h1>
            <p>Use this panel to view, add, modify, or delete book User.</p>
            
            <?php
            if (mysqli_num_rows($result) > 0) {
                echo "<div class='table-responsive'>";
                echo "<table class='table table-striped table-hover'>";
                echo "<thead class='table-light'>";
                echo "<tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Joined Date</th>
                            <th class='text-end'>Actions</th>
                        </tr>";
                echo "</thead>";
                echo "<tbody>";

                while($row = mysqli_fetch_assoc($result)){
                    $role_badge = match(strtolower($row['role'])) {
                        'admin' => 'badge bg-danger',
                        'user' => 'badge bg-primary',
                        default => 'badge bg-secondary'
                    };

                    echo"<tr>";
                    echo"<td><strong>" . htmlspecialchars($row['full_name']) . "</strong></td>";
                    echo"<td><span class='$role_badge'>" . htmlspecialchars(ucfirst($row['role'])) . "</span></td>";
                    echo"<td><a href='mailto:" . htmlspecialchars($row['email']) . "' class='text-decoration-none'>" . htmlspecialchars($row['email']) . "</a></td>";
                    echo"<td><small class='text-muted'>" . htmlspecialchars(date('M d, Y', strtotime($row['created_at']))) . "</small></td>";
                    echo"<td class='text-end'>
                            <div class='btn-group' role='group'>
                                <a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-secondary' title='Edit User'><i class='fas fa-edit'></i></a>
                                <a href='delete_user.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Are you sure you want to delete user " . htmlspecialchars($row['full_name']) . "?');\" title='Delete User'><i class='fas fa-trash'></i></a>
                            </div>
                         </td>";
                    echo"</tr>";
                };

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<div class='text-center mt-5'>
                        <p class='text-muted'>No users found.</p>
                      </div>";
            }
            ?>
            <div style="width: 100%; text-align: right; margin-bottom: 15px;">
                <a href='user_log.php' class='btn btn-primary'>➕ View User Logs</a>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
