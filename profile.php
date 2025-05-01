<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("UTC");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get user's order statistics
$stats_query = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN order_status IN ('pending', 'processing', 'shipped') THEN 1 ELSE 0 END) as active_orders,
                SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(total_amount) as total_spent
                FROM orders 
                WHERE user_id = '$user_id'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent orders
$orders_query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 5";
$orders_result = mysqli_query($conn, $orders_query);

// Handle profile update
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));

    // Validate inputs
    if(empty($name)) {
        $error = "Name is required";
    } elseif(empty($email)) {
        $error = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif(empty($phone)) {
        $error = "Phone number is required";
    } elseif(!preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Invalid phone number format";
    } elseif(empty($address)) {
        $error = "Address is required";
    } else {
        // Check if email exists for other users
        $check_query = "SELECT user_id FROM users WHERE email = '$email' AND user_id != '$user_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            $error = "Email already exists";
        } else {
            $update_query = "UPDATE users SET 
                           name = '$name',
                           email = '$email',
                           phone = '$phone',
                           address = '$address'
                           WHERE user_id = '$user_id'";
            
            if(mysqli_query($conn, $update_query)) {
                $success = "Profile updated successfully!";
                // Refresh user data
                $user_result = mysqli_query($conn, $user_query);
                $user = mysqli_fetch_assoc($user_result);
            } else {
                $error = "Error updating profile";
            }
        }
    }
}

// Handle password change
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if(!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect";
    } elseif(strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long";
    } elseif($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
        
        if(mysqli_query($conn, $update_query)) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error changing password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Vel Power Tools Store</title>
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
            min-height: 100vh;
        }

        .navbar {
            background: var(--primary-color);
            padding: 1rem;
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--accent-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--accent-color);
            border-color: var(--accent-color);
        }

        .order-card {
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateX(5px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .current-utc {
            color: var(--accent-color);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-tools me-2"></i>Vel Power Tools
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
				
				
				
						 <li class="nav-item">
                        <a class="nav-link text-white" href="../index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    
					
					  <li class="nav-item">
                        <a class="nav-link text-white" href="dashboard.php">
                            <i class="fas fa-user me-1"></i>Dashboard
                        </a>
                    </li>
                   <li class="nav-item">
                        <a class="nav-link text-white" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                        </a>
                    </li>
                  
                    <li class="nav-item">
                        <a class="nav-link text-white" href="orders.php">
                            <i class="fas fa-box me-1"></i>My Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
				
				
				
				
				
				
				
				
				
                </ul>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                        <div class="current-utc">
                            <i class="fas fa-clock me-1"></i>Current UTC: <?php echo gmdate('Y-m-d H:i:s'); ?>
                        </div>
                    </div>

                    <!-- Profile Update Form -->
                    <form method="POST" id="profile-form">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                       pattern="[0-9]{10}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Shipping Address</label>
                            <textarea name="address" class="form-control" rows="3" required><?php 
                                echo htmlspecialchars($user['address']); 
                            ?></textarea>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Password Change Form -->
                    <h5 class="mb-4">Change Password</h5>
                    <form method="POST" id="password-form">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" 
                                       minlength="6" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" 
                                       minlength="6" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Statistics -->
            <div class="col-lg-4">
                <div class="stats-card text-center">
                    <h3><?php echo $stats['total_orders']; ?></h3>
                    <p class="mb-0 text-muted">Total Orders</p>
                </div>
                <div class="stats-card text-center">
                    <h3><?php echo $stats['completed_orders']; ?></h3>
                    <p class="mb-0 text-success">Completed Orders</p>
                </div>
                <div class="stats-card text-center">
                    <h3><?php echo $stats['active_orders']; ?></h3>
                    <p class="mb-0 text-primary">Active Orders</p>
                </div>
                <div class="stats-card text-center">
                    <h3>RS.<?php echo number_format($stats['total_spent'], 2); ?></h3>
                    <p class="mb-0 text-success">Total Spent</p>
                </div>

                <!-- Recent Orders -->
                <div class="profile-card">
                    <h5 class="mb-4">Recent Orders</h5>
                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                        <div class="order-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Order #<?php echo $order['order_id']; ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="status-badge bg-<?php 
                                        echo match($order['order_status']) {
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span><br>
                                    <strong>RS.<?php echo number_format($order['total_amount'], 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="text-center mt-3">
                        <a href="orders.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-list me-2"></i>View All Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);

        // Form validation
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            const phone = document.querySelector('input[name="phone"]');
            const email = document.querySelector('input[name="email"]');

            if(!phone.value.match(/^[0-9]{10}$/)) {
                e.preventDefault();
                alert('Please enter a valid 10-digit phone number');
                return;
            }

            if(!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
        });

        document.getElementById('password-form').addEventListener('submit', function(e) {
            const newPassword = document.querySelector('input[name="new_password"]');
            const confirmPassword = document.querySelector('input[name="confirm_password"]');

            if(newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                alert('New passwords do not match');
                return;
            }
        });

        // Real-time UTC clock update
        function updateUTCClock() {
            const now = new Date();
            const utcString = now.toISOString().slice(0, 19).replace('T', ' ');
            document.querySelector('.current-utc').innerHTML = 
                '<i class="fas fa-clock me-1"></i>Current UTC: ' + utcString;
        }

        setInterval(updateUTCClock, 1000);
    </script>
</body>
</html>