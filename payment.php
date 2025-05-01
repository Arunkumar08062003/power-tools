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

// Check if order_id is provided
if(!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

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

// Calculate totals
$subtotal = 0;
while($item = mysqli_fetch_assoc($items_result)) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * 0.18; // 18% GST
$total = $order['total_amount'];

// Handle payment processing
if(isset($_POST['process_payment'])) {
    $card_number = mysqli_real_escape_string($conn, str_replace(' ', '', $_POST['card_number']));
    $card_holder = mysqli_real_escape_string($conn, $_POST['card_holder']);
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry']);
    $cvv = mysqli_real_escape_string($conn, $_POST['cvv']);

    // Basic validation
    if(empty($card_number) || !preg_match('/^[0-9]{16}$/', $card_number)) {
        $error = "Invalid card number";
    } elseif(empty($card_holder)) {
        $error = "Card holder name is required";
    } elseif(empty($expiry) || !preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry)) {
        $error = "Invalid expiry date (MM/YY format required)";
    } elseif(empty($cvv) || !preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $error = "Invalid CVV";
    } else {
        // In a real application, you would integrate with a payment gateway here
        // For demo purposes, we'll simulate a successful payment
        
        // Update order status
        $update_query = "UPDATE orders SET 
                        payment_status = 'completed',
                        order_status = 'processing'
                        WHERE order_id = '$order_id'";
        
// In payment.php, after successful payment processing
if(mysqli_query($conn, $update_query)) {
    header("Location: order-success.php?order_id=$order_id");
    exit();
} else {
            $error = "Payment processing failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Vel Power Tools Store</title>
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

        .payment-container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-input {
            background: var(--light-color);
            border: 2px solid #eee;
            padding: 1rem;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .card-input:focus {
            border-color: var(--accent-color);
            box-shadow: none;
        }

        .card-icons {
            font-size: 2rem;
            color: #666;
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

        .btn-process {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-process:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .card-preview {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .card-chip {
            width: 50px;
            height: 40px;
            background: #ffd700;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .card-number-preview {
            font-size: 1.5rem;
            letter-spacing: 4px;
            margin-bottom: 1rem;
        }

        .card-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }

        .card-network {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            opacity: 0.8;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem;
            background: var(--light-color);
            border-radius: 10px;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .payment-card {
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

    <div class="payment-container">
        <div class="row">
            <!-- Payment Form -->
            <div class="col-lg-8">
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="payment-card">
                    <h4 class="mb-4">Secure Payment</h4>

                    <!-- Card Preview -->
                    <div class="card-preview">
                        <div class="card-network">
                            <i class="fab fa-cc-visa"></i>
                        </div>
                        <div class="card-chip"></div>
                        <div class="card-number-preview" id="numberPreview">
                            ���� ���� ���� ����
                        </div>
                        <div class="card-details">
                            <div>
                                <div class="text-uppercase" style="font-size: 0.8rem;">Card Holder</div>
                                <div id="holderPreview">YOUR NAME</div>
                            </div>
                            <div>
                                <div class="text-uppercase" style="font-size: 0.8rem;">Expires</div>
                                <div id="expiryPreview">MM/YY</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" id="payment-form">
                        <div class="mb-4">
                            <label class="form-label">Card Number</label>
                            <div class="input-group">
                                <input type="text" name="card_number" class="form-control card-input" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" required>
                                <span class="input-group-text card-icons">
                                    <i class="fab fa-cc-visa me-2"></i>
                                    <i class="fab fa-cc-mastercard me-2"></i>
                                    <i class="fab fa-cc-amex"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Card Holder Name</label>
                            <input type="text" name="card_holder" class="form-control card-input" 
                                   placeholder="Name on card" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" name="expiry" class="form-control card-input" 
                                       placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CVV</label>
                                <input type="password" name="cvv" class="form-control card-input" 
                                       placeholder="***" maxlength="4" required>
                            </div>
                        </div>

                        <div class="secure-badge">
                            <i class="fas fa-lock"></i>
                            <div>
                                <strong>Secure Payment</strong>
                                <div class="text-muted small">Your payment information is encrypted</div>
                            </div>
                        </div>

                        <button type="submit" name="process_payment" class="btn-process mt-4">
                            <i class="fas fa-lock me-2"></i>Pay RS.<?php echo number_format($total, 2); ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="payment-card">
                    <h5 class="mb-4">Order Summary</h5>
                    <div class="summary-item">
                        <span>Order ID</span>
                        <strong>#<?php echo $order_id; ?></strong>
                    </div>
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
                </div>

                <div class="payment-card">
                    <h5 class="mb-4">Shipping Details</h5>
                    <p><strong><?php echo htmlspecialchars($order['name']); ?></strong></p>
                    <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Format card number with spaces
        document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formatted = '';
            for(let i = 0; i < value.length; i++) {
                if(i > 0 && i % 4 === 0) {
                    formatted += ' ';
                }
                formatted += value[i];
            }
            e.target.value = formatted;
            
            // Update preview
            document.getElementById('numberPreview').textContent = 
                formatted || '���� ���� ���� ����';
        });

        // Format expiry date
        document.querySelector('input[name="expiry"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if(value.length >= 2) {
                value = value.substr(0,2) + '/' + value.substr(2);
            }
            e.target.value = value;
            
            // Update preview
            document.getElementById('expiryPreview').textContent = 
                value || 'MM/YY';
        });

        // Update card holder preview
        document.querySelector('input[name="card_holder"]').addEventListener('input', function(e) {
            document.getElementById('holderPreview').textContent = 
                e.target.value.toUpperCase() || 'YOUR NAME';
        });

        // Form validation
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const cardNumber = document.querySelector('input[name="card_number"]').value.replace(/\s/g, '');
            const expiry = document.querySelector('input[name="expiry"]').value;
            const cvv = document.querySelector('input[name="cvv"]').value;

            if(!/^[0-9]{16}$/.test(cardNumber)) {
                e.preventDefault();
                alert('Please enter a valid 16-digit card number');
                return;
            }

            if(!/^(0[1-9]|1[0-2])\/([0-9]{2})$/.test(expiry)) {
                e.preventDefault();
                alert('Please enter a valid expiry date (MM/YY)');
                return;
            }

            if(!/^[0-9]{3,4}$/.test(cvv)) {
                e.preventDefault();
                alert('Please enter a valid CVV');
                return;
            }
        });
    </script>
</body>
</html>