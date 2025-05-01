<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch categories for the dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Initialize variables to store form data and errors
$errors = [];
$form_data = [
    'category_id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'brand' => '',
    'power_rating' => '',
    'voltage' => '',
    'weight' => '',
    'warranty_period' => ''
];

// Handle form submission
if(isset($_POST['submit'])) {
    // Sanitize and validate input data
    $form_data = [
        'category_id' => mysqli_real_escape_string($conn, $_POST['category_id']),
        'name' => mysqli_real_escape_string($conn, $_POST['name']),
        'description' => mysqli_real_escape_string($conn, $_POST['description']),
        'price' => mysqli_real_escape_string($conn, $_POST['price']),
        'stock' => mysqli_real_escape_string($conn, $_POST['stock']),
        'brand' => mysqli_real_escape_string($conn, $_POST['brand']),
        'power_rating' => mysqli_real_escape_string($conn, $_POST['power_rating']),
        'voltage' => mysqli_real_escape_string($conn, $_POST['voltage']),
        'weight' => mysqli_real_escape_string($conn, $_POST['weight']),
        'warranty_period' => mysqli_real_escape_string($conn, $_POST['warranty_period'])
    ];

    // Validation
    if(empty($form_data['category_id'])) $errors[] = "Category is required";
    if(empty($form_data['name'])) $errors[] = "Product name is required";
    if(empty($form_data['price']) || !is_numeric($form_data['price'])) $errors[] = "Valid price is required";
    if(empty($form_data['stock']) || !is_numeric($form_data['stock'])) $errors[] = "Valid stock quantity is required";

    // Handle image upload
    $image_name = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(!in_array($filetype, $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed";
        } else {
            // Generate unique filename
            $image_name = uniqid() . '_' . time() . '.' . $filetype;
            $upload_path = "../uploads/products/" . $image_name;
            
            // Check file size (5MB max)
            if($_FILES['image']['size'] > 5000000) {
                $errors[] = "File size must be less than 5MB";
            }
            
            // Attempt to upload
            if(!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload image";
            }
        }
    } else {
        $errors[] = "Product image is required";
    }

    // If no errors, insert into database
    if(empty($errors)) {
        $current_datetime = date('Y-m-d H:i:s');
        
        $query = "INSERT INTO products (
            category_id, 
            name, 
            description, 
            price, 
            stock, 
            brand, 
            power_rating, 
            voltage, 
            weight, 
            warranty_period, 
            image, 
            created_at
        ) VALUES (
            '{$form_data['category_id']}',
            '{$form_data['name']}',
            '{$form_data['description']}',
            '{$form_data['price']}',
            '{$form_data['stock']}',
            '{$form_data['brand']}',
            '{$form_data['power_rating']}',
            '{$form_data['voltage']}',
            '{$form_data['weight']}',
            '{$form_data['warranty_period']}',
            '$image_name',
            '$current_datetime'
        )";

        if(mysqli_query($conn, $query)) {
            echo "<script>
                alert('Product added successfully!');
                window.location.href='products.php';
            </script>";
            exit();
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
            // Delete uploaded image if database insert fails
            if(file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Vel Power Tools Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1d3557;
            --secondary-color: #e63946;
            --light-color: #f8f9fa;
        }

        /* Layout styles */
        body {
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar styles */
        .sidebar {
            background: var(--primary-color);
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                height: 100%;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background: var(--light-color);
            transition: all 0.3s;
        }

        /* Sidebar links */
        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(255,255,255,0.1);
            border-left-color: var(--secondary-color);
        }

        /* Form card styles */
        .form-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .page-header {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        /* Form control focus states */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(29, 53, 87, 0.25);
        }

        /* Custom file input styling */
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
        }

        /* Toggle button for mobile */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
            .main-content {
                padding-top: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="btn btn-primary sidebar-toggle" type="button">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-3 mb-4 text-center text-white">
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
        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Add New Product</h4>
            <span class="text-muted"><?php echo date('Y-m-d H:i:s'); ?></span>
        </div>

        <!-- Error Display -->
        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="form-card">
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php while($category = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo ($form_data['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price (?) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($form_data['price']); ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Stock <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" min="0"
                               value="<?php echo htmlspecialchars($form_data['stock']); ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control"
                               value="<?php echo htmlspecialchars($form_data['brand']); ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Power Rating (Watts)</label>
                        <input type="text" name="power_rating" class="form-control"
                               value="<?php echo htmlspecialchars($form_data['power_rating']); ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Voltage</label>
                        <input type="text" name="voltage" class="form-control"
                               value="<?php echo htmlspecialchars($form_data['voltage']); ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" name="weight" class="form-control" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($form_data['weight']); ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Warranty Period</label>
                        <input type="text" name="warranty_period" class="form-control"
                               value="<?php echo htmlspecialchars($form_data['warranty_period']); ?>"
                               placeholder="e.g., 1 Year">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Product Image <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*" required
                               onchange="previewImage(this)">
                        <div class="image-preview mt-2" id="imagePreview"></div>
                    </div>

                    <div class="col-12 text-end">
                        <a href="products.php" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Image preview functionality
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-fluid', 'image-preview');
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Mobile sidebar toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const requiredFields = ['category_id', 'name', 'price', 'stock'];
            let isValid = true;

            requiredFields.forEach(field => {
                const input = this.elements[field];
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (formChanged) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    </script>
</body>
</html>