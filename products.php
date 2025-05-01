<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle search and filtering
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Build query
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          WHERE 1=1";

if($search) {
    $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%' OR p.brand LIKE '%$search%')";
}

if($category) {
    $query .= " AND p.category_id = '$category'";
}

$query .= " ORDER BY p.created_at DESC";

// Execute query
$result = mysqli_query($conn, $query);

// Fetch categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Vel Power Tools Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: #1d3557;
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 250px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar-link:hover {
            background: #152a45;
            color: white;
        }
        .sidebar-link.active {
            background: #e63946;
        }
        .admin-header {
            background: white;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .table-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-3 mb-4 text-center">
            <h5><i class="fas fa-tools me-2"></i>Vel Power Tools</h5>
            <small>Admin Panel</small>
        </div>
        <a href="adminhome.php" class="sidebar-link">
            <i class="fas fa-dashboard me-2"></i> Dashboard
        </a>
        <a href="products.php" class="sidebar-link active">
            <i class="fas fa-box me-2"></i> Products
        </a>
        <a href="orders.php" class="sidebar-link">
            <i class="fas fa-shopping-cart me-2"></i> Orders
        </a>
        <a href="sales.php" class="sidebar-link">
            <i class="fas fa-chart-bar me-2"></i> Sales
        </a>
        <a href="logout.php" class="sidebar-link">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="admin-header mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Products Management</h4>
            <div class="d-flex align-items-center">
                <span class="me-3"><?php echo date('Y-m-d H:i:s'); ?></span>
                <a href="add_product.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search products..." value="<?php echo $search; ?>">
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['category_id']; ?>"
                                <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-striped" id="productsTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Power Rating</th>
                            <th>Voltage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <img src="../uploads/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                     class="product-image" alt="<?php echo $product['name']; ?>">
                            </td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td><?php echo $product['brand']; ?></td>
                            <td>RS.<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td><?php echo $product['power_rating']; ?></td>
                            <td><?php echo $product['voltage']; ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?php echo $product['product_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this product?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="deleteButton" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#productsTable').DataTable({
                "pageLength": 10,
                "order": [[1, "asc"]],
                "language": {
                    "search": "Search within table:",
                    "lengthMenu": "Show _MENU_ products per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ products"
                }
            });
        });

        function confirmDelete(productId) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('deleteButton').href = 'delete_product.php?id=' + productId;
            modal.show();
        }
    </script>
</body>
</html>