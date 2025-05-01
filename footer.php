    </div><!-- Close main content container -->

    <!-- Footer -->
    <footer class="mt-auto bg-dark text-light">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-uppercase mb-4">Vel Power Tools</h5>
                    <p class="small">Your one-stop shop for professional-grade Vel Power Tools. Quality, reliability, and excellent service guaranteed.</p>
                    <div class="mt-4">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-uppercase mb-4">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="products.php" class="text-light text-decoration-none">Products</a></li>
                        <li class="mb-2"><a href="about.php" class="text-light text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-uppercase mb-4">Categories</h6>
                    <ul class="list-unstyled">
                        <?php
                        $cat_query = mysqli_query($conn, "SELECT * FROM categories LIMIT 5");
                        while($category = mysqli_fetch_assoc($cat_query)):
                        ?>
                        <li class="mb-2">
                            <a href="products.php?category=<?php echo $category['category_id']; ?>" 
                               class="text-light text-decoration-none">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-uppercase mb-4">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i> 123 Tool Street, Workshop City
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i> +1234567890
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i> info@powertools.com
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock me-2"></i> Mon - Sat: 9:00 AM - 6:00 PM
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="bg-secondary py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <p class="small text-white mb-0">
                            &copy; <?php echo date('Y'); ?> Vel Power Tools. All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <img src="assets/images/payment-methods.png" alt="Payment Methods" 
                             style="height: 30px;">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JavaScript -->
    <script>
        // Add to cart animation
        function addToCartAnimation(button) {
            button.classList.add('added');
            setTimeout(() => {
                button.classList.remove('added');
            }, 1500);
        }

        // Sticky navbar adjustment
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                document.querySelector('.navbar').classList.add('shadow');
            } else {
                document.querySelector('.navbar').classList.remove('shadow');
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>