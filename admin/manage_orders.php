<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include ("../function/connectdb.php");

// 2. Fetch data for current page with LIMIT/OFFSET
$query = "SELECT o.*, u.full_name, u.email, u.phone
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          ORDER BY o.id ASC";
$querycustomer = "SELECT * FROM order_items ORDER BY id ASC";
$result = mysqli_query($conn, $query);
$resultcustomer = mysqli_query($conn, $querycustomer);

$fullname = $_SESSION['fullname'] ?? 'Admin';
// --- END: NEW PAGINATION LOGIC ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders - WaaWeeWoo Bookstore</title>
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

        .status-select {
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #fff;
            font-size: 0.875rem;
            color: #495057;
            min-width: 140px;
        }

        .status-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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

            .status-select {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>

</head>
<body>

<div class="admin-container">
    <?php include "header.php"; ?>

    <main class="main-content">
        <div class="dashboard-container">
            <h1>Ongoing Orders</h1>
            <p>This is where you manage orders, including viewing and managing order details.</p>

            <div style="width: 100%; text-align: right; margin-bottom: 15px;">
                <a href='add_order.php' class='btn btn-primary'>➕ Add New Order</a>
            </div>

        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped table-hover'>";
            echo "<thead class='table-light'>";
            echo "<tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Update Status</th>
                        <th class='text-end'>Actions</th>
                    </tr>";
            echo "</thead>";
            echo "<tbody>";

            while($row = mysqli_fetch_assoc($result)){
                $status_badge = match(strtolower($row['status'])) {
                    'pending' => 'badge bg-warning',
                    'paid' => 'badge bg-info',
                    'shipped' => 'badge bg-primary',
                    'delivered' => 'badge bg-success',
                    'cancelled' => 'badge bg-danger',
                    default => 'badge bg-secondary'
                };

                echo"<tr>";
                echo"<td><strong>#" . htmlspecialchars($row['id']) . "</strong></td>";
                echo"<td>
                        <div class='customer-info'>
                            <div class='fw-bold'>" . htmlspecialchars($row['full_name'] ?? 'N/A') . "</div>
                            <div class='text-muted small'>" . htmlspecialchars($row['email'] ?? '') . "</div>
                            <div class='text-muted small'>" . htmlspecialchars($row['phone'] ?? '') . "</div>
                        </div>
                     </td>";
                echo"<td><span class='$status_badge'>" . htmlspecialchars(ucfirst($row['status'])) . "</span></td>";
                echo"<td class='action-cell'>";
                if (strtolower($row['status']) !== 'delivered') {
                    echo "<select onchange=\"updateStatus(" . $row['id'] . ", this.value)\" class='status-select'>";
                    echo "<option value=''>Change Status</option>";
                    echo "<option value='pending'" . (strtolower($row['status']) === 'pending' ? ' selected' : '') . ">Pending</option>";
                    echo "<option value='paid'" . (strtolower($row['status']) === 'paid' ? ' selected' : '') . ">Paid</option>";
                    echo "<option value='shipped'" . (strtolower($row['status']) === 'shipped' ? ' selected' : '') . ">Shipped</option>";
                    echo "<option value='delivered'" . (strtolower($row['status']) === 'delivered' ? ' selected' : '') . ">Delivered</option>";
                    echo "<option value='cancelled'" . (strtolower($row['status']) === 'cancelled' ? ' selected' : '') . ">Cancelled</option>";
                    echo "</select>";
                } else {
                    echo "<span class='text-success'><i class='fas fa-check-circle'></i> Completed</span>";
                }
                echo "</td>";
                echo"<td class='text-end'>
                        <div class='btn-group' role='group'>
                            <a href='edit_order.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-secondary' title='Edit Order'><i class='fas fa-edit'></i></a>
                            <a href='delete_order.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Are you sure you want to delete order #" . $row['id'] . "?');\" title='Delete Order'><i class='fas fa-trash'></i></a>
                        </div>
                     </td>";
                echo"</tr>";
            };

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<div class='text-center mt-5'>
                    <p class='text-muted'>No orders found.</p>
                  </div>";
        }
        ?>

        </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function updateStatus(orderId, newStatus) {
    if (newStatus === '') return; // Ignore empty selection

    if (confirm('Are you sure you want to change the status of order #' + orderId + ' to ' + newStatus + '?')) {
        window.location.href = 'update_order_status.php?id=' + orderId + '&status=' + newStatus;
    } else {
        // Reset the select to current value
        event.target.selectedIndex = 0;
    }
}
</script>
</body>
</html>
