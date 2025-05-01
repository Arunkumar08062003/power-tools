<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// If user is already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = "";

if(isset($_POST['register'])) {
    // Get and sanitize input data
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $confirm_password = mysqli_real_escape_string($conn, trim($_POST['confirm_password']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));

    // Validate input
    if(empty($name)) {
        $errors[] = "Name is required";
    }
    if(empty($email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if(empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif(!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Invalid phone number format";
    }
    if(empty($password)) {
        $errors[] = "Password is required";
    } elseif(strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    if($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    if(empty($address)) {
        $errors[] = "Address is required";
    }

    // Check if email already exists
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if(mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email already registered";
    }

    // If no errors, proceed with registration
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $current_datetime = date('Y-m-d H:i:s');

        $query = "INSERT INTO users (name, email, password, phone, address, created_at) 
                 VALUES ('$name', '$email', '$hashed_password', '$phone', '$address', '$current_datetime')";

        if(mysqli_query($conn, $query)) {
            $success = "Registration successful! Please login.";
            // Redirect to login page after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Vel Power Tools Store</title>
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
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }


	
		.container{
		
		   display:flex;
		   justify-content:center;
		   align-items:center;
		   width:600px;
		   height:100vh;
		
		}
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            position: relative;
        }

        .register-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .register-form {
            padding: 30px;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
        }

        .btn-register {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-2px);
        }

        .form-floating {
            margin-bottom: 15px;
        }

        .form-floating > .form-control {
            padding: 1rem 0.75rem;
        }

        .form-floating > label {
            padding: 1rem 0.75rem;
        }

        .alert {
            border-radius: 10px;
        }

        .register-image {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .current-time {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .register-container {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h4 class="mb-0">Create Account</h4>
                <div class="register-image">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>

            <div class="register-form">
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php foreach($errors as $error): ?>
                            <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="Full Name" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                <label for="name">Full Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <label for="email">Email Address</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" 
                                       name="password" placeholder="Password" required>
                                <label for="password">Password</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" placeholder="Confirm Password" required>
                                <label for="confirm_password">Confirm Password</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="Phone Number" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        <label for="phone">Phone Number</label>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="address" name="address" 
                                  placeholder="Address" style="height: 100px" required><?php 
                            echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; 
                        ?></textarea>
                        <label for="address">Address</label>
                    </div>

                    <button type="submit" name="register" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>

                    <div class="text-center mt-3">
                        Already have an account? 
                        <a href="login.php" class="text-decoration-none">Login here</a>
                    </div>
                </form>
            </div>
        </div>

 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Phone number validation
        document.getElementById('phone').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length !== 10) {
                this.setCustomValidity('Phone number must be 10 digits');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>