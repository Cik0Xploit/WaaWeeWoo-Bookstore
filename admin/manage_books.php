<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}


// Note: The session/security/variable initialization should be in header.php, 
// but since the original code had it here, we will include the necessary files first.

// Include header.php (which should contain session_start() and security checks)
include "header.php";

// Add Bootstrap CSS and Font Awesome
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
echo '<style>
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
</style>';

// Since header.php already handles the security, we can proceed with DB logic.
include ("../function/connectdb.php");

// --- START: PAGINATION LOGIC ---
$limit = 10; // Number of records to show per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit; // Starting record for the query

// 1. Get total records
$count_query = "SELECT COUNT(id) AS total FROM books";
$count_result = mysqli_query($conn, $count_query);
$total_books = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_books / $limit);

// Ensure the requested page is valid
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $start = ($page - 1) * $limit;
}

// 2. Fetch data for current page with LIMIT/OFFSET
$query = "SELECT b.*, c.name AS category_name 
          FROM books b 
          JOIN categories c ON b.category_id = c.id 
          ORDER BY b.id ASC 
          LIMIT $start, $limit"; // Apply LIMIT and OFFSET
$result = mysqli_query($conn, $query);
// --- END: PAGINATION LOGIC ---
?>

<main class="main-content">
    <div class="dashboard-container">
        <h1>Book Inventory</h1>
        <p>This is where you manage the entire collection of books, including adding, updating, and deleting entries.</p>

        <div style="width: 100%; text-align: right; margin-bottom: 15px;">
            <a href='add_book.php' class='btn btn-primary'>➕ Add New Book</a>
        </div>
        
        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped table-hover'>";
            echo "<thead class='table-light'>";
            echo "<tr>
                        <th>Book Details</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class='text-end'>Actions</th>
                    </tr>";
            echo "</thead>";
            echo "<tbody>";

            while($row = mysqli_fetch_assoc($result)){
                $stock_class = $row['stock'] < 15 ? 'text-danger' : '';
                $status_badge = match(strtolower($row['status'] ?? 'active')) {
                    'in stock', 'active' => 'badge bg-success',
                    'low stock' => 'badge bg-warning',
                    'out of stock' => 'badge bg-danger',
                    default => 'badge bg-secondary'
                };

                echo"<tr>";
                echo"<td>
                        <div>
                            <strong>" . htmlspecialchars($row['title']) . "</strong><br>
                            <small class='text-muted'>by " . htmlspecialchars($row['author']) . "</small>
                        </div>
                     </td>";
                echo"<td><span class='badge bg-secondary'>" . htmlspecialchars($row['category_name']) . "</span></td>";
                echo"<td>RM " . number_format($row['price'], 2) . "</td>";
                echo"<td class='$stock_class'>" . htmlspecialchars($row['stock']) . "</td>";
                echo"<td><span class='$status_badge'>" . htmlspecialchars(ucfirst($row['status'] ?? 'active')) . "</span></td>";
                echo"<td class='text-end'>
                        <div class='btn-group' role='group'>
                            <a href='../book_details.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-primary' title='View Book'><i class='fas fa-eye'></i></a>
                            <a href='update_book.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-secondary' title='Edit Book'><i class='fas fa-edit'></i></a>
                            <a href='delete_book.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Are you sure you want to delete \"" . htmlspecialchars($row['title']) . "\"?');\" title='Delete Book'><i class='fas fa-trash'></i></a>
                        </div>
                     </td>";
                echo"</tr>";
            };

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<div class='text-center mt-5'>
                    <p class='text-muted'>No books found in the inventory.</p>
                  </div>";
        }
        ?>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Book inventory pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    if ($start_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a>
                        </li>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
