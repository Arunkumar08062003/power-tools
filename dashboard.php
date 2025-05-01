<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get recent orders
$orders_query = "SELECT o.*, 
                 (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as items_count
                 FROM orders o 
                 WHERE o.user_id = '$user_id' 
                 ORDER BY o.created_at DESC 
                 LIMIT 5";
$recent_orders = mysqli_query($conn, $orders_query);

// Get order statistics
$stats_query = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_spent,
                COUNT(CASE WHEN order_status = 'delivered' THEN 1 END) as delivered_orders,
                COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending_orders
                FROM orders 
                WHERE user_id = '$user_id'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get cart items count
$cart_query = "SELECT COUNT(*) as cart_count FROM cart WHERE user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);
$cart_count = mysqli_fetch_assoc($cart_result)['cart_count'];

// Get wishlist count (if you have a wishlist table)
//$wishlist_count = 0; // Implement if you have wishlist functionality

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Vel Power Tools Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1d3557;
            --secondary-color: #e63946;
            --accent-color: #457b9d;
            --light-color: #f1faee;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem;
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: white !important;
        }

        .dashboard-container {
            padding: 2rem;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .recent-orders {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            text-decoration: none;
            color: var(--primary-color);
        }

        .action-card:hover {
            transform: translateY(-5px);
            color: var(--accent-color);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .custom-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
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
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                            <?php if($cart_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-box me-1"></i>Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-1"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <div class="row align-items-center">
              
                <div class="col">
                    <h2 class="mb-1">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        <?php echo date('Y-m-d H:i:s'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="cart.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if($cart_count > 0): ?>
                        <span class="custom-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </div>
                <div>My Cart</div>
            </a>
            <a href="orders.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div>My Orders</div>
            </a>
            <a href="profile.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>Profile</div>
            </a>
            <a href="../index.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div>Shop Now</div>
            </a>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon bg-primary text-white">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3><?php echo $stats['total_orders']; ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon bg-success text-white">
                        <i class="fas fa-check"></i>
                    </div>
                    <h3><?php echo $stats['delivered_orders']; ?></h3>
                    <p class="text-muted mb-0">Delivered Orders</p>
                </div>
            </div>
        
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon bg-info text-white">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <h3>RS.<?php echo number_format($stats['total_spent'], 2); ?></h3>
                    <p class="text-muted mb-0">Total Spent</p>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders">
            <h4 class="mb-4">Recent Orders</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                            <td><?php echo $order['items_count']; ?> items</td>
                            <td>RS.<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="order-status bg-<?php 
                                    echo match($order['order_status']) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipped' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?> text-white">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="orders.php?id=<?php echo $order['order_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($recent_orders) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No orders yet</p>
                                <a href="../index.php" class="btn btn-primary mt-3">Start Shopping</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(mysqli_num_rows($recent_orders) > 0): ?>
            <div class="text-end mt-3">
                <a href="orders.php" class="btn btn-primary">
                    View All Orders
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Add animation to stats cards
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('mouseover', function() {
                this.style.transform = 'translateY(-10px)';
            });
            card.addEventListener('mouseout', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>