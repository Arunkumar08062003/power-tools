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

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    
    // Validate input
    if(empty($email)) {
        $errors[] = "Email is required";
    }
    if(empty($password)) {
        $errors[] = "Password is required";
    }

    if(empty($errors)) {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if(mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if(password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login time
               
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "Email not registered";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vel Power Tools Store</title>
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
		   width:400px;
		   height:100vh;
		
		}
		
		

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.5s ease;
			
			
			
			
			
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .login-form {
            padding: 30px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
        }

        .btn-login {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .current-time {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .social-login {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }

        .social-login a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-login a:hover {
            transform: scale(1.1);
        }

        .facebook { background: #3b5998; }
        .google { background: #db4a39; }
        .twitter { background: #00acee; }

        .remember-me {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }

        .remember-me input {
            margin-right: 10px;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .back-to-home {
            position: fixed;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .back-to-home:hover {
            color: var(--light-color);
            transform: translateX(-5px);
        }

        @media (max-width: 576px) {
            .login-container {
                margin: 10px;
            }
            .back-to-home {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>

    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h4 class="mb-0">Welcome Back!</h4>
                <p class="mb-0 mt-2">Please login to your account</p>
            </div>

            <div class="login-form">
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php foreach($errors as $error): ?>
                            <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <label for="email">Email Address</label>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" 
                               name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>

                    <button type="submit" name="login" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                <div class="social-login">
                    <a href="#" class="facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="google">
                        <i class="fab fa-google"></i>
                    </a>
                    <a href="#" class="twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>

                <div class="login-footer">
                    <div class="mb-2">
                        Don't have an account? 
                        <a href="register.php" class="text-decoration-none">Register here</a>
                    </div>
                   
                </div>
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

        // Remember me functionality
        const rememberCheckbox = document.getElementById('remember');
        const emailInput = document.getElementById('email');

        // Load remembered email if exists
        window.addEventListener('load', () => {
            const rememberedEmail = localStorage.getItem('rememberedEmail');
            if (rememberedEmail) {
                emailInput.value = rememberedEmail;
                rememberCheckbox.checked = true;
            }
        });

        // Save email if remember me is checked
        document.querySelector('form').addEventListener('submit', () => {
            if (rememberCheckbox.checked) {
                localStorage.setItem('rememberedEmail', emailInput.value);
            } else {
                localStorage.removeItem('rememberedEmail');
            }
        });
    </script>
</body>
</html>