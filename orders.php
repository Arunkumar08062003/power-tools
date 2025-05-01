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

// Get filters
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$date_filter = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Base query
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as items_count
          FROM orders o 
          WHERE o.user_id = '$user_id'";

// Apply filters
if($status_filter) {
    $query .= " AND o.order_status = '$status_filter'";
}

if($date_filter) {
    switch($date_filter) {
        case 'today':
            $query .= " AND DATE(o.created_at) = CURDATE()";
            break;
        case 'week':
            $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
        case 'year':
            $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            break;
    }
}

if($search) {
    $query .= " AND o.order_id LIKE '%$search%'";
}

// Add sorting
$query .= " ORDER BY o.created_at DESC";

// Execute query
$result = mysqli_query($conn, $query);

// Get order statistics
$stats_query = "SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN order_status = 'processing' THEN 1 END) as processing_orders,
                COUNT(CASE WHEN order_status = 'shipped' THEN 1 END) as shipped_orders,
                COUNT(CASE WHEN order_status = 'delivered' THEN 1 END) as delivered_orders,
                COUNT(CASE WHEN order_status = 'cancelled' THEN 1 END) as cancelled_orders,
                SUM(total_amount) as total_spent
                FROM orders 
                WHERE user_id = '$user_id'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get success message if exists
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Vel Power Tools Store</title>
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
            --danger-color: #e74c3c;
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

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: white !important;
        }

        .orders-container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .order-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-5px);
        }

        .order-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .order-body {
            padding: 1.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background: var(--warning-color);
            color: white;
        }

        .status-processing {
            background: var(--accent-color);
            color: white;
        }

        .status-shipped {
            background: var(--primary-color);
            color: white;
        }

        .status-delivered {
            background: var(--success-color);
            color: white;
        }

        .status-cancelled {
            background: var(--danger-color);
            color: white;
        }

        .filter-bar {
            background: white;
            padding: 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .progress-track {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 2rem 0;
        }

        .progress-step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            z-index: 1;
        }

        .progress-step.active {
            background: var(--success-color);
            color: white;
        }

        .progress-line {
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #eee;
        }

        .progress-line-fill {
            height: 100%;
            background: var(--success-color);
            transition: width 0.3s;
        }

        @media (max-width: 768px) {
            .orders-container {
                padding: 1rem;
            }
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-user me-1"></i>Dashboard
                        </a>
                    </li>
                   <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
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

    <div class="orders-container">
        <?php if($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Order Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center">
                    <h3><?php echo $stats['total_orders']; ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center">
                    <h3><?php echo $stats['delivered_orders']; ?></h3>
                    <p class="text-muted mb-0">Delivered Orders</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center">
                    <h3><?php echo $stats['pending_orders'] + $stats['processing_orders']; ?></h3>
                    <p class="text-muted mb-0">Active Orders</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card text-center">
                    <h3>RS.<?php echo number_format($stats['total_spent'], 2); ?></h3>
                    <p class="text-muted mb-0">Total Spent</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-bar">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="date" class="form-select">
                        <option value="">All Time</option>
                        <option value="today" <?php echo $date_filter == 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $date_filter == 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo $date_filter == 'month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="year" <?php echo $date_filter == 'year' ? 'selected' : ''; ?>>This Year</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by Order ID" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders List -->
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($result)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>Order #<?php echo $order['order_id']; ?></strong><br>
                                <small class="text-muted">
                                    <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <strong>Amount:</strong><br>
                                RS.<?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Items:</strong><br>
                                <?php echo $order['items_count']; ?> items
                            </div>
                            <div class="col-md-3 text-end">
                                <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="order-body">
                        <!-- Order Progress -->
                        <div class="progress-track">
                            <div class="progress-line">
                                <div class="progress-line-fill" style="width: 
                                    <?php
                                        echo match($order['order_status']) {
                                            'pending' => '0%',
                                            'processing' => '33%',
                                            'shipped' => '66%',
                                            'delivered' => '100%',
                                            default => '0%'
                                        };
                                    ?>">
                                </div>
                            </div>
                            <div class="progress-step <?php echo in_array($order['order_status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="progress-step <?php echo in_array($order['order_status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="progress-step <?php echo in_array($order['order_status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="progress-step <?php echo $order['order_status'] == 'delivered' ? 'active' : ''; ?>">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>

                        <!-- Order Actions -->
                        <div class="text-end">
                            <a href="order-success.php?order_id=<?php echo $order['order_id']; ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h4>No orders found</h4>
                <p class="text-muted">Looks like you haven't placed any orders yet.</p>
                <a href="../index.php" class="btn btn-primary mt-3">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 3000);
        });
    </script>
</body>
</html>