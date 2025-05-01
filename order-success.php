<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("UTC");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is set
if(!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Get order details
$order_query = "SELECT o.*, u.name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.user_id 
                WHERE o.order_id = '$order_id' AND o.user_id = '$user_id'";
$order_result = mysqli_query($conn, $order_query);

if(mysqli_num_rows($order_result) == 0) {
    header("Location: orders.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Vel Power Tools Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1d3557;
            --secondary-color: #e63946;
            --accent-color: #457b9d;
            --light-color: #f1faee;
            --success-color: #2ecc71;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .success-container {
            max-width: 800px;
            margin: 3rem auto;
        }

        .success-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--success-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 2rem;
            animation: scale-in 0.5s ease;
        }

        @keyframes scale-in {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .order-details {
            background: var(--light-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .btn-action {
            padding: 0.8rem 2rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-success {
            background: var(--success-color);
            color: white;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="mb-4">Order Placed Successfully!</h2>
            <p class="text-muted mb-4">
                Thank you for your order. We've received your order and will begin processing it soon.
            </p>

            <div class="order-details">
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Order ID:</strong><br>
                        #<?php echo $order_id; ?>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <strong>Order Date:</strong><br>
                        <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Payment Status:</strong><br><br>
                    <span class="status-badge status-success">
                        <?php echo ucfirst($order['payment_method']); ?> - 
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Shipping Address:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?><br>
                    Phone: <?php echo htmlspecialchars($order['phone']); ?>
                </div>

                <hr>

                <div class="mb-3">
                    <strong>Order Items:</strong>
                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                        <div class="d-flex align-items-center mt-3">
                            <img src="../uploads/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                 class="product-image me-3" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">
                                    Quantity: <?php echo $item['quantity']; ?> � 
                                    RS.<?php echo number_format($item['price'], 2); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                RS.<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <hr>

                <div class="text-end">
                    <div class="mb-2">
                        <span class="me-3">Subtotal:</span>
                        <strong>RS.<?php echo number_format($order['total_amount'] / 1.18, 2); ?></strong>
                    </div>
                    <div class="mb-2">
                        <span class="me-3">GST (18%):</span>
                        <strong>RS.<?php echo number_format($order['total_amount'] - ($order['total_amount'] / 1.18), 2); ?></strong>
                    </div>
                    <div class="mb-2">
                        <span class="me-3">Total Amount:</span>
                        <strong>RS.<?php echo number_format($order['total_amount'], 2); ?></strong>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <a href="orders.php" class="btn btn-primary btn-action">
                    <i class="fas fa-box me-2"></i>View Orders
                </a>
                <a href="../index.php" class="btn btn-outline-primary btn-action">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>