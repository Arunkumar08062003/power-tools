<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Vel Power Tools Store</title>
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
		
		.container{
		width:80%;
		}

        /* Navbar Styles */
        .navbar {
            background: var(--primary-color);
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link:hover {
            color: white !important;
        }

        /* Header Styles */
        .page-header {
            background: linear-gradient(rgba(29, 53, 87, 0.9), rgba(29, 53, 87, 0.9)),
                        url('assets/images/header-bg.jpg') center/cover;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        /* Service Card Styles */
        .service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }

        .service-content {
            padding: 20px;
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
            padding: 50px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background: var(--primary-color);
            transform: translateX(-50%);
        }

        .timeline-item {
            margin-bottom: 50px;
            position: relative;
        }

        .timeline-content {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            width: 45%;
            margin-left: auto;
        }

        .timeline-item:nth-child(even) .timeline-content {
            margin-left: 0;
        }

        .timeline-content::before {
            content: '';
            position: absolute;
            top: 20px;
            right: 100%;
            border: 10px solid transparent;
            border-right-color: white;
        }

        .timeline-item:nth-child(even) .timeline-content::before {
            right: auto;
            left: 100%;
            border-right-color: transparent;
            border-left-color: white;
        }

        /* Stats Counter */
        .stats-counter {
            background: var(--primary-color);
            color: white;
            padding: 50px 0;
        }

        .counter-item {
            text-align: center;
        }

        .counter-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Current Time Display */
        .current-time {
            background: var(--accent-color);
            color: white;
            padding: 10px 0;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .timeline::before {
                left: 30px;
            }

            .timeline-content {
                width: calc(100% - 60px);
                margin-left: 60px;
            }

            .timeline-item:nth-child(even) .timeline-content {
                margin-left: 60px;
            }

            .timeline-content::before {
                left: -20px;
                border: 10px solid transparent;
                border-right-color: white;
            }

            .timeline-item:nth-child(even) .timeline-content::before {
                left: -20px;
                border-left-color: transparent;
                border-right-color: white;
            }
        }
    </style>
</head>
<body>


 <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tools me-2"></i>Vel Power Tools
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Search Form -->
     

                <!-- Navigation Links -->
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link cart-link" href="user/cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <?php if($cart_count > 0): ?>
                                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="user/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="user/orders.php">Orders</a></li>
                                <li><a class="dropdown-item" href="user/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="user/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user/register.php">Register</a>
                        </li>
						
						 <li class="nav-item">
                            <a class="nav-link" href="about.php">About</a>
                        </li>
						
						
						 <li class="nav-item">
                            <a class="nav-link" href="contact.php">Contact</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
	
<div class="container">
    <!-- Header Section -->
    <header class="page-header">
        <div class="container">
            <h1 class="display-4">About Vel Power Tools Store</h1>
            <p class="lead">Your trusted partner in Vel Power Tools since 2010</p>
        </div>
    </header>

    <!-- Main Services Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Our Services</h2>
            <div class="row g-4">
                <!-- Sales -->
                <div class="col-md-4">
                    <div class="service-card">
                        <img src="assets/images/sales.jpg" class="service-image" alt="Vel Power Tools Sales">
                        <div class="service-content">
                            <h3><i class="fas fa-shopping-cart me-2"></i>Sales</h3>
                            <p>We offer a wide range of high-quality Vel Power Tools from leading manufacturers. Our extensive collection includes:</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check me-2 text-success"></i>Professional-grade tools</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Latest models</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Competitive pricing</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Warranty coverage</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Rentals -->
                <div class="col-md-4">
                    <div class="service-card">
                        <img src="assets/images/rentals.jpg" class="service-image" alt="Tool Rentals">
                        <div class="service-content">
                            <h3><i class="fas fa-handshake me-2"></i>Rentals</h3>
                            <p>Need tools for a short-term project? Our rental service provides:</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check me-2 text-success"></i>Flexible rental periods</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Well-maintained equipment</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Competitive rates</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Delivery available</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Services -->
                <div class="col-md-4">
                    <div class="service-card">
                        <img src="assets/images/service.jpg" class="service-image" alt="Tool Repair Services">
                        <div class="service-content">
                            <h3><i class="fas fa-wrench me-2"></i>Repair Services</h3>
                            <p>Our expert technicians provide comprehensive repair services:</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check me-2 text-success"></i>Professional repairs</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Quick turnaround</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Genuine parts</li>
                                <li><i class="fas fa-check me-2 text-success"></i>Service warranty</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Counter Section -->
    <section class="stats-counter">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="counter-item">
                        <div class="counter-number">15+</div>
                        <div>Years of Experience</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="counter-item">
                        <div class="counter-number">10000+</div>
                        <div>Happy Customers</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="counter-item">
                        <div class="counter-number">500+</div>
                        <div>Tools Available</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="counter-item">
                        <div class="counter-number">24/7</div>
                        <div>Customer Support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

   
    <!-- Contact Section -->
   


</div>
    <!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5><i class="fas fa-tools me-2"></i>Vel Power Tools Store</h5>
                <p class="text-muted">Your one-stop shop for quality Vel Power Tools</p>
                
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="about.php" class="text-light text-decoration-none">
                        <i class="fas fa-angle-right me-2"></i>About Us
                    </a></li>
                    <li><a href="contact.php" class="text-light text-decoration-none">
                        <i class="fas fa-angle-right me-2"></i>Contact
                    </a></li>
                    <li><a href="terms.php" class="text-light text-decoration-none">
                        <i class="fas fa-angle-right me-2"></i>Terms & Conditions
                    </a></li>
                    <li><a href="privacy.php" class="text-light text-decoration-none">
                        <i class="fas fa-angle-right me-2"></i>Privacy Policy
                    </a></li>
                    <li><a href="faq.php" class="text-light text-decoration-none">
                        <i class="fas fa-angle-right me-2"></i>FAQs
                    </a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-phone me-2 text-muted"></i>
                        <a href="tel:+918072604246" class="text-light text-decoration-none">
                            +91 807 260 4246
                        </a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        <a href="mailto:info@powertoolsstore.com" class="text-light text-decoration-none">
                            info@powertoolsstore.com
                        </a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                        <a href="https://goo.gl/maps/YourGoogleMapsLink" class="text-light text-decoration-none">
                            116, Irugur Rd, Ondipudur,<br>
                            Nandha Nagar, Ondipudur,<br>
                            Coimbatore, Tamil Nadu 641016
                        </a>
                    </li>
                </ul>
                <div class="mt-3">
                    <a href="#" class="text-light me-3" title="Follow us on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Follow us on Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Follow us on Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-light" title="Follow us on LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-muted">
                    � <?php echo date('Y'); ?> Vel Power Tools Store. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <img src="https://static-assets-web.flixcart.com/batman-returns/batman-returns/p/images/payment-method-c454fb.svg" alt="Payment Methods" 
                     style="height: 30px;">
            </div>
        </div>
    </div>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update UTC time
        function updateUTCTime() {
            const now = new Date();
            const utcString = now.toISOString().slice(0, 19).replace('T', ' ');
            document.querySelector('.current-time .col-md-6').innerHTML = 
                '<i class="fas fa-clock me-2"></i>Current Time (UTC): ' + utcString;
        }

        // Update time every second
        setInterval(updateUTCTime, 1000);
        updateUTCTime();
    </script>
</body>
</html>