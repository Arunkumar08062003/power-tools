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
$error = '';
$success = '';

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.image, p.stock, 
               (p.price * c.quantity) as subtotal
               FROM cart c
               JOIN products p ON c.product_id = p.product_id
               WHERE c.user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);

// Calculate totals
$total_items = 0;
$subtotal = 0;
$cart_items = [];

while($item = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $item;
    $total_items += $item['quantity'];
    $subtotal += $item['subtotal'];
}

// If cart is empty, redirect to cart page
if(empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate tax and total
$tax_rate = 0.18; // 18% GST
$tax = $subtotal * $tax_rate;
$total = $subtotal + $tax;

// Handle checkout process
if(isset($_POST['place_order'])) {
    // Validate inputs
    $shipping_address = mysqli_real_escape_string($conn, trim($_POST['shipping_address']));
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));

    if(empty($shipping_address)) {
        $error = "Shipping address is required";
    } elseif(empty($phone)) {
        $error = "Phone number is required";
    } elseif(!preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Invalid phone number format";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        try {
            // Create order
            $order_date = date('Y-m-d H:i:s');
            $order_query = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, 
                           payment_status, order_status, phone, created_at) 
                           VALUES ('$user_id', '$total', '$shipping_address', '$payment_method', 
                           'pending', 'pending', '$phone', '$order_date')";
            
            mysqli_query($conn, $order_query);
            $order_id = mysqli_insert_id($conn);

            // Add order items
            foreach($cart_items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];

                // Check stock availability
                $stock_query = "SELECT stock FROM products WHERE product_id = '$product_id'";
                $stock_result = mysqli_query($conn, $stock_query);
                $stock_data = mysqli_fetch_assoc($stock_result);

                if($stock_data['stock'] < $quantity) {
                    throw new Exception("Not enough stock for " . $item['name']);
                }

                // Add order item
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES ('$order_id', '$product_id', '$quantity', '$price')";
                mysqli_query($conn, $item_query);

                // Update product stock
                $update_stock = "UPDATE products SET stock = stock - $quantity 
                               WHERE product_id = '$product_id'";
                mysqli_query($conn, $update_stock);
            }

            // Clear cart
            mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");

            // Commit transaction
            mysqli_commit($conn);

            // If payment method is COD
           // In checkout.php, modify the payment handling section
if($payment_method == 'cod') {
    // For COD, set payment status as pending
    mysqli_query($conn, "UPDATE orders SET payment_status = 'completed' ,
                        order_status = 'processing' WHERE order_id = '$order_id'");
    header("Location: order-success.php?order_id=$order_id");
    exit();
} elseif($payment_method == 'upi') {
    // For UPI, set payment status as completed
    mysqli_query($conn, "UPDATE orders SET payment_status = 'completed' ,
                        order_status = 'processing' WHERE order_id = '$order_id'");
    header("Location: order-success.php?order_id=$order_id");
    exit();
} else {
    mysqli_query($conn, "UPDATE orders SET payment_status = 'completed' ,
                        order_status = 'processing' WHERE order_id = '$order_id'");
    header("Location: order-success.php?order_id=$order_id");
    exit();
}

        } catch(Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Vel Power Tools Store</title>
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

        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .checkout-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
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

        .payment-method {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method:hover {
            border-color: var(--accent-color);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background: var(--light-color);
        }

        .btn-place-order {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-place-order:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .checkout-card {
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
        </div>
    </nav>

    <div class="checkout-container">
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="checkout-form">
                    <!-- Shipping Information -->
                    <div class="checkout-card">
                        <h5 class="mb-4">Shipping Information</h5>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>"
                                   pattern="[0-9]{10}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Address</label>
                            <textarea name="shipping_address" class="form-control" rows="3" required><?php 
                                echo htmlspecialchars($user['address']); 
                            ?></textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-card">
                        <h5 class="mb-4">Payment Method</h5>
                        <div class="payment-method" onClick="selectPayment('card')">
                            <input type="radio" name="payment_method" value="card" required>
                            <i class="fas fa-credit-card me-2"></i>Credit/Debit Card
                        </div>
                        <div class="payment-method" onClick="selectPayment('upi')">
                            <input type="radio" name="payment_method" value="upi" required>
                            <i class="fas fa-mobile-alt me-2"></i>UPI Payment
                        </div>
                        <div class="payment-method" onClick="selectPayment('cod')">
                            <input type="radio" name="payment_method" value="cod" required>
                            <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="checkout-card">
                    <h5 class="mb-4">Order Summary</h5>
                    
                    <!-- Items -->
                    <?php foreach($cart_items as $item): ?>
                        <div class="summary-item">
                            <div class="d-flex align-items-center">
                                <img src="../uploads/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                     class="product-image me-3" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">
                                        Quantity: <?php echo $item['quantity']; ?>
                                    </small>
                                </div>
                            </div>
                            <div>
                                RS.<?php echo number_format($item['subtotal'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Totals -->
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>RS.<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>GST (18%)</span>
                        <span>RS.<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <strong>Total Amount</strong>
                        <strong>RS.<?php echo number_format($total, 2); ?></strong>
                    </div>

                    <button type="submit" form="checkout-form" name="place_order" class="btn-place-order mt-3">
                        <i class="fas fa-lock me-2"></i>Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPayment(method) {
            // Remove selected class from all payment methods
            document.querySelectorAll('.payment-method').forEach(element => {
                element.classList.remove('selected');
            });

            // Add selected class to clicked payment method
            const selectedMethod = document.querySelector(`.payment-method input[value="${method}"]`);
            selectedMethod.checked = true;
            selectedMethod.closest('.payment-method').classList.add('selected');
        }

        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const phone = document.querySelector('input[name="phone"]');
            const address = document.querySelector('textarea[name="shipping_address"]');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');

            if(!phone.value.match(/^[0-9]{10}$/)) {
                e.preventDefault();
                alert('Please enter a valid 10-digit phone number');
                return;
            }

            if(!address.value.trim()) {
                e.preventDefault();
                alert('Please enter a shipping address');
                return;
            }

            if(!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return;
            }
        });
    </script>
</body>
</html>