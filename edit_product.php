<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if product ID is provided
if(!isset($_GET['id'])) {
    $_SESSION['error'] = "No product specified for editing.";
    header("Location: products.php");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['id']);
$errors = [];
$success = "";

// Fetch product details
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          WHERE p.product_id = '$product_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Product not found.";
    header("Location: products.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Handle form submission
if(isset($_POST['submit'])) {
    // Validate input
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $brand = mysqli_real_escape_string($conn, trim($_POST['brand']));
    $power_rating = mysqli_real_escape_string($conn, trim($_POST['power_rating']));
    $voltage = mysqli_real_escape_string($conn, trim($_POST['voltage']));
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $warranty_period = mysqli_real_escape_string($conn, trim($_POST['warranty_period']));

    // Validation
    if(empty($name)) $errors[] = "Product name is required.";
    if(empty($description)) $errors[] = "Description is required.";
    if(!is_numeric($price) || $price <= 0) $errors[] = "Valid price is required.";
    if(!is_numeric($stock) || $stock < 0) $errors[] = "Valid stock quantity is required.";
    if(empty($brand)) $errors[] = "Brand is required.";
    if(empty($power_rating)) $errors[] = "Power rating is required.";
    if(empty($voltage)) $errors[] = "Voltage is required.";
    if(!is_numeric($weight) || $weight <= 0) $errors[] = "Valid weight is required.";
    if(empty($warranty_period)) $errors[] = "Warranty period is required.";

    // Handle image upload
    $image_update = "";
    if(isset($_FILES['image']) && $_FILES['image']['error'] != 4) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['image']['size'];
        
        if(!in_array($filetype, $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif($filesize > 5000000) {
            $errors[] = "File size must be less than 5MB.";
        } else {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = "../uploads/products/" . $new_filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if($product['image'] && file_exists("../uploads/products/" . $product['image'])) {
                    unlink("../uploads/products/" . $product['image']);
                }
                $image_update = ", image = '$new_filename'";
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // Update product if no errors
    if(empty($errors)) {
        $update_query = "UPDATE products SET 
                        category_id = '$category_id',
                        name = '$name',
                        description = '$description',
                        price = '$price',
                        stock = '$stock',
                        brand = '$brand',
                        power_rating = '$power_rating',
                        voltage = '$voltage',
                        weight = '$weight',
                        warranty_period = '$warranty_period'
                        $image_update
                        WHERE product_id = '$product_id'";
        
        if(mysqli_query($conn, $update_query)) {
            $success = "Product updated successfully!";
            // Refresh product data
            $result = mysqli_query($conn, $query);
            $product = mysqli_fetch_assoc($result);
        } else {
            $errors[] = "Error updating product: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Vel Power Tools Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1d3557;
            --secondary-color: #e63946;
            --accent-color: #457b9d;
            --light-color: #f1faee;
        }

        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            background: var(--primary-color);
            min-height: 100vh;
            color: white;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .toggle-sidebar {
                display: block !important;
            }
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover, .sidebar-link.active {
            background: var(--secondary-color);
            border-left-color: var(--light-color);
        }

        .form-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .toggle-sidebar {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(69, 123, 157, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="btn btn-primary toggle-sidebar" type="button">
        <i class="fas fa-bars"></i>
    </button>

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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Edit Product</h4>
            <div class="text-muted">
                <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>

        <!-- Alerts -->
        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong>
                <ul class="mb-0">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php mysqli_data_seek($categories, 0); ?>
                            <?php while($category = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo $category['category_id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?php 
                            echo htmlspecialchars($product['description']); 
                        ?></textarea>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price (?)</label>
                        <input type="number" name="price" class="form-control" step="0.01" 
                               value="<?php echo $product['price']; ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" 
                               value="<?php echo $product['stock']; ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control" 
                               value="<?php echo htmlspecialchars($product['brand']); ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Power Rating</label>
                        <input type="text" name="power_rating" class="form-control" 
                               value="<?php echo htmlspecialchars($product['power_rating']); ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Voltage</label>
                        <input type="text" name="voltage" class="form-control" 
                               value="<?php echo htmlspecialchars($product['voltage']); ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" name="weight" class="form-control" step="0.01" 
                               value="<?php echo $product['weight']; ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Warranty Period</label>
                        <input type="text" name="warranty_period" class="form-control" 
                               value="<?php echo htmlspecialchars($product['warranty_period']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Current Image</label>
                        <div>
                            <?php if($product['image'] && file_exists("../uploads/products/" . $product['image'])): ?>
                                <img src="../uploads/products/<?php echo $product['image']; ?>" 
                                     class="preview-image" alt="Current product image">
                            <?php else: ?>
                                <p class="text-muted">No image available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">New Image (optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>

                    <div class="col-12 text-end">
                        <a href="products.php" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Preview image before upload
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            if(e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.preview-image');
                    if(preview) {
                        preview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const price = document.querySelector('input[name="price"]').value;
            const stock = document.querySelector('input[name="stock"]').value;
            const weight = document.querySelector('input[name="weight"]').value;

            if(price <= 0 || stock < 0 || weight <= 0) {
                e.preventDefault();
                alert('Please enter valid values for price, stock, and weight.');
            }
        });
    </script>
</body>
</html>