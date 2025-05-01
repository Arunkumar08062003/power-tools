<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize date filters
$default_from = date('Y-m-d', strtotime('-30 days'));
$default_to = date('Y-m-d');

$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : $default_from;
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : $default_to;

// Overall Sales Statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as average_order_value,
    SUM(CASE WHEN payment_status = 'completed' THEN total_amount ELSE 0 END) as paid_amount,
    SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Daily Sales Data for Chart
$daily_sales_query = "SELECT 
    DATE(created_at) as sale_date,
    COUNT(*) as orders_count,
    SUM(total_amount) as daily_revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'
    GROUP BY DATE(created_at)
    ORDER BY sale_date";

$daily_sales_result = mysqli_query($conn, $daily_sales_query);
$daily_sales_data = [];
while($row = mysqli_fetch_assoc($daily_sales_result)) {
    $daily_sales_data[] = $row;
}

// Top Selling Products
$top_products_query = "SELECT 
    p.product_id,
    p.name as product_name,
    p.image,
    COUNT(oi.order_item_id) as times_ordered,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.quantity * oi.price) as total_revenue
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.order_id
    WHERE DATE(o.created_at) BETWEEN '$date_from' AND '$date_to'
    GROUP BY p.product_id
    ORDER BY total_revenue DESC
    LIMIT 10";

$top_products_result = mysqli_query($conn, $top_products_query);

// Payment Method Distribution
$payment_methods_query = "SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(total_amount) as total_amount
    FROM orders
    WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'
    GROUP BY payment_method";

$payment_methods_result = mysqli_query($conn, $payment_methods_query);
$payment_methods_data = [];
while($row = mysqli_fetch_assoc($payment_methods_result)) {
    $payment_methods_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics - Vel Power Tools Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }

        .toggle-sidebar {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
        }

        .date-filter-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
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
        <a href="products.php" class="sidebar-link">
            <i class="fas fa-box me-2"></i> Products
        </a>
        <a href="orders.php" class="sidebar-link">
            <i class="fas fa-shopping-cart me-2"></i> Orders
        </a>
        <a href="sales.php" class="sidebar-link active">
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
            <h4 class="mb-0">Sales Analytics</h4>
            <div class="text-muted">
                <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="date-filter-card">
            <form method="GET" class="row align-items-center">
                <div class="col-md-8">
                    <input type="text" name="daterange" class="form-control" 
                           value="<?php echo $date_from; ?> - <?php echo $date_to; ?>">
                    <input type="hidden" name="date_from" value="<?php echo $date_from; ?>">
                    <input type="hidden" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stats-card p-4">
                    <h6 class="text-muted mb-3">Total Revenue</h6>
                    <h3 class="mb-3">RS.<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" style="width: <?php 
                            echo $stats['total_revenue'] > 0 ? 
                                ($stats['paid_amount'] / $stats['total_revenue'] * 100) : 0; 
                        ?>%"></div>
                    </div>
                    <small class="text-muted">
                        Paid: RS.<?php echo number_format($stats['paid_amount'], 2); ?><br>
                        Pending: RS.<?php echo number_format($stats['pending_amount'], 2); ?>
                    </small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card p-4">
                    <h6 class="text-muted mb-3">Total Orders</h6>
                    <h3 class="mb-3"><?php echo number_format($stats['total_orders']); ?></h3>
                    <h6 class="text-success mb-0">
                        Avg. Order Value: RS.<?php echo number_format($stats['average_order_value'], 2); ?>
                    </h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card p-4">
                    <h6 class="text-muted mb-3">Daily Average</h6>
                    <h3 class="mb-3">RS.<?php 
                        $days = max(1, (strtotime($date_to) - strtotime($date_from)) / 86400);
                        echo number_format($stats['total_revenue'] / $days, 2); 
                    ?></h3>
                    <h6 class="text-primary mb-0">
                        Orders/Day: <?php echo number_format($stats['total_orders'] / $days, 1); ?>
                    </h6>
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="chart-card mb-4">
            <h5 class="mb-4">Daily Sales Trend</h5>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Top Products and Payment Methods -->
        <div class="row">
            <div class="col-md-8">
                <div class="chart-card">
                    <h5 class="mb-4">Top Selling Products</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Orders</th>
                                    <th>Quantity</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = mysqli_fetch_assoc($top_products_result)): ?>
                                <tr>
                                    <td>
                                        <?php if($product['image']): ?>
                                        <img src="../uploads/products/<?php echo $product['image']; ?>" 
                                             class="product-image me-2">
                                        <?php endif; ?>
                                        <?php echo $product['product_name']; ?>
                                    </td>
                                    <td><?php echo $product['times_ordered']; ?></td>
                                    <td><?php echo $product['total_quantity']; ?></td>
                                    <td>RS.<?php echo number_format($product['total_revenue'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card">
                    <h5 class="mb-4">Payment Methods</h5>
                    <canvas id="paymentChart"></canvas>
                    <div class="mt-4">
                        <?php foreach($payment_methods_data as $method): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span><?php echo ucfirst($method['payment_method']); ?></span>
                                <span>RS.<?php echo number_format($method['total_amount'], 2); ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php 
                                    echo ($method['total_amount'] / $stats['total_revenue'] * 100);
                                ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        $('.toggle-sidebar').click(function() {
            $('.sidebar').toggleClass('active');
        });

        // Initialize date range picker
        $('input[name="daterange"]').daterangepicker({
            startDate: moment('<?php echo $date_from; ?>'),
            endDate: moment('<?php echo $date_to; ?>'),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
            $('input[name="date_from"]').val(picker.startDate.format('YYYY-MM-DD'));
            $('input[name="date_to"]').val(picker.endDate.format('YYYY-MM-DD'));
        });

       // Replace the existing salesChart creation code with this:
const salesChart = new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($daily_sales_data, 'sale_date')); ?>,
        datasets: [{
            label: 'Daily Revenue',
            data: <?php echo json_encode(array_column($daily_sales_data, 'daily_revenue')); ?>,
            backgroundColor: '#1d3557',
            borderWidth: 1
        }, {
            label: 'Orders Count',
            data: <?php echo json_encode(array_column($daily_sales_data, 'orders_count')); ?>,
            backgroundColor: '#e63946',
            borderWidth: 1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Revenue (₹)'
                }
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Orders Count'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.datasetIndex === 0) {
                            label += '₹' + context.raw.toFixed(2);
                        } else {
                            label += context.raw;
                        }
                        return label;
                    }
                }
            }
        }
    }
});

        // Payment Methods Chart
        const paymentChart = new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($payment_methods_data, 'payment_method')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($payment_methods_data, 'total_amount')); ?>,
                    backgroundColor: ['#1d3557', '#e63946', '#457b9d', '#a8dadc']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>