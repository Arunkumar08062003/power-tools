<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle quantity update
if(isset($_POST['update_quantity'])) {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    $quantity = (int)$_POST['quantity'];
    
    // Get product stock
    $stock_query = "SELECT p.stock FROM cart c 
                   JOIN products p ON c.product_id = p.product_id 
                   WHERE c.cart_id = '$cart_id'";
    $stock_result = mysqli_query($conn, $stock_query);
    $stock_data = mysqli_fetch_assoc($stock_result);
    
    if($quantity > 0 && $quantity <= $stock_data['stock']) {
        mysqli_query($conn, "UPDATE cart SET quantity = '$quantity' WHERE cart_id = '$cart_id' AND user_id = '$user_id'");
        $success_message = "Cart updated successfully!";
    } else {
        $error_message = "Invalid quantity! Maximum available: " . $stock_data['stock'];
    }
}

// Handle remove item
if(isset($_POST['remove_item'])) {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    mysqli_query($conn, "DELETE FROM cart WHERE cart_id = '$cart_id' AND user_id = '$user_id'");
    $success_message = "Item removed from cart!";
}

// Handle clear cart
if(isset($_POST['clear_cart'])) {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");
    $success_message = "Cart cleared successfully!";
}

// Get cart items with product details
$cart_query = "SELECT c.*, p.name, p.price, p.image, p.stock, 
               (p.price * c.quantity) as subtotal
               FROM cart c
               JOIN products p ON c.product_id = p.product_id
               WHERE c.user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);

// Calculate totals
$total_items = 0;
$total_amount = 0;
$cart_items = [];

while($item = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $item;
    $total_items += $item['quantity'];
    $total_amount += $item['subtotal'];
}

// Calculate tax (assuming 18% GST)
$tax_rate = 0.18;
$tax_amount = $total_amount * $tax_rate;
$final_amount = $total_amount + $tax_amount;

// Get user address for checkout
$user_query = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Vel Power Tools Store</title>
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
            padding-bottom: 60px;
            position: relative;
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

        .cart-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
            transition: all 0.3s;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .quantity-input {
            width: 70px;
            text-align: center;
            border-radius: 20px;
            border: 1px solid #ddd;
        }

        .btn-remove {
            color: var(--secondary-color);
            border: none;
            background: none;
            transition: all 0.3s;
        }

        .btn-remove:hover {
            transform: scale(1.2);
        }

        .summary-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 1.5rem;
            position: sticky;
            top: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .btn-checkout {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .btn-checkout:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-continue {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-continue:hover {
            background: var(--primary-color);
            color: white;
        }

        .empty-cart {
            text-align: center;
            padding: 3rem;
        }

        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .cart-container {
                padding: 1rem;
            }

            .product-image {
                width: 80px;
                height: 80px;
            }

            .summary-card {
                margin-top: 2rem;
                position: static;
            }
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-quantity {
            background: var(--light-color);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .btn-quantity:hover {
            background: var(--accent-color);
            color: white;
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

    <!-- Main Content -->
    <div class="container">
        <?php if($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="cart-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Shopping Cart (<?php echo $total_items; ?> items)</h4>
                        <?php if($total_items > 0): ?>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="clear_cart" class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to clear your cart?')">
                                    <i class="fas fa-trash me-1"></i>Clear Cart
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($cart_items)): ?>
                        <?php foreach($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="../uploads/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                             class="product-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="text-muted mb-0">RS.<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="POST" class="quantity-controls">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <button type="button" class="btn-quantity" onClick="updateQuantity(this, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                   min="1" max="<?php echo $item['stock']; ?>" class="quantity-input"
                                                   onchange="this.form.submit()">
                                            <button type="button" class="btn-quantity" onClick="updateQuantity(this, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <input type="hidden" name="update_quantity" value="1">
                                        </form>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <h6 class="mb-0">RS.<?php echo number_format($item['subtotal'], 2); ?></h6>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <button type="submit" name="remove_item" class="btn-remove" 
                                                    onclick="return confirm('Remove this item?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h4>Your cart is empty</h4>
                            <p class="text-muted">Looks like you haven't added anything to your cart yet.</p>
                            <a href="../index.php" class="btn btn-primary mt-3">
                                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <?php if(!empty($cart_items)): ?>
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h5 class="mb-4">Order Summary</h5>
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span>RS.<?php echo number_format($total_amount, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Tax (18% GST)</span>
                            <span>RS.<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <strong>Total Amount</strong>
                            <strong>RS.<?php echo number_format($final_amount, 2); ?></strong>
                        </div>
                        <a href="checkout.php" class="btn btn-checkout">
                            <i class="fas fa-lock me-2"></i>Proceed to Checkout
                        </a>
                        <a href="../index.php" class="btn btn-continue">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateQuantity(button, change) {
            const input = button.parentElement.querySelector('input[name="quantity"]');
            const currentValue = parseInt(input.value);
            const newValue = currentValue + change;
            const maxValue = parseInt(input.getAttribute('max'));
            
            if(newValue >= 1 && newValue <= maxValue) {
                input.value = newValue;
                input.form.submit();
            }
        }

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