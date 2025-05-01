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
            <h1 class="display-4">Conatct Vel Power Tools Store</h1>
            <p class="lead">Your trusted partner in Vel Power Tools since 2010</p>
        </div>
    </header>

<!-- Contact Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Contact Us</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="contact-info bg-white p-4 rounded shadow-sm">
                    <h3 class="h5 mb-4">Our Location</h3>
                    <!-- Google Maps Embed -->
                    <div class="map-container mb-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3916.4609491367964!2d77.0486986!3d11.004000999999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ba8571b091c16ad%3A0x293c9d073b137422!2s116%2C%20Irugur%20Rd%2C%20Ellammal%20Layout%2C%20Ondipudur%2C%20Coimbatore%2C%20Tamil%20Nadu%20641016!5e0!3m2!1sen!2sin!4v1745321958049!5m2!1sen!2sin" 
                            width="100%" 
                            height="300" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    
                    <div class="contact-details">
                        <h3 class="h5 mb-3">Contact Details</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                116, Irugur Rd, Ondipudur, Nandha Nagar,<br>
                                Ondipudur, Coimbatore, Tamil Nadu 641016
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                <a href="tel:8072604246" class="text-decoration-none text-dark">+91 8072604246</a>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <a href="mailto:info@powertoolsstore.com" class="text-decoration-none text-dark">info@powertoolsstore.com</a>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-clock me-2 text-primary"></i>
                                Hours: Mon-Sat: 9:00 AM - 6:00 PM
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="contact-form bg-white p-4 rounded shadow-sm">
                    <h3 class="h5 mb-4">Send us a Message</h3>
                    <form id="contactForm" action="process_contact.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
   
    <!-- Contact Section -->
   


</div>
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Vel Power Tools Store</h5>
                    <p>Your one-stop shop for all power tool needs</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <small>&copy; 2025 Vel Power Tools Store. All rights reserved.</small>
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