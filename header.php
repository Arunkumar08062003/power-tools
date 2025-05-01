<?php
session_start();
include("dbconnect.php");
date_default_timezone_set("Asia/Calcutta");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vel Power Tools Store</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #1d3557;
            --light-color: #f1faee;
            --dark-color: #2b2d42;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: var(--dark-color) !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .navbar-top {
            background-color: var(--secondary-color);
            color: white;
            padding: 5px 0;
            font-size: 0.9rem;
        }

        .search-form {
            width: 100%;
            max-width: 400px;
        }

        .search-form .form-control {
            border-radius: 20px 0 0 20px;
            border: 2px solid #ddd;
        }

        .search-form .btn {
            border-radius: 0 20px 20px 0;
            background-color: var(--primary-color);
            color: white;
        }

        .cart-icon {
            position: relative;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .search-form {
                margin: 1rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="navbar-top">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-auto">
                    <span><i class="fas fa-phone me-2"></i> +1234567890</span>
                    <span class="ms-3"><i class="fas fa-envelope me-2"></i> info@powertools.com</span>
                </div>
                <div class="col-auto">
                    <span><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tools me-2"></i>
                Vel Power Tools
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Search Form -->
                <form class="search-form mx-auto d-flex">
                    <input class="form-control" type="search" placeholder="Search for tools..." aria-label="Search">
                    <button class="btn" type="submit"><i class="fas fa-search"></i></button>
                </form>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link cart-icon" href="user/cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <?php
                                $user_id = $_SESSION['user_id'];
                                $cart_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id");
                                $cart_count = mysqli_fetch_assoc($cart_query)['count'];
                                if($cart_count > 0):
                                ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container my-4">