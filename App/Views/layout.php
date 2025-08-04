<?php
if (!isset($_SESSION)) {
    session_start();
    ob_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'CrowdFunding Platform'; ?></title>

    <!-- Meta Tags for SEO -->
    <meta name="description" content="Modern crowdfunding platform to support innovative projects and creative ideas">
    <meta name="keywords" content="crowdfunding, projects, innovation, funding, startup">
    <meta name="author" content="CrowdFunding Platform">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $title ?? 'CrowdFunding Platform'; ?>">
    <meta property="og:description" content="Support innovative projects and bring creative ideas to life">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo app_url($_SERVER['REQUEST_URI'] ?? ''); ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo app_url('public/favicon.ico'); ?>">

    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo app_url('public/css/style.css'); ?>" rel="stylesheet">
    <link href="<?php echo app_url('public/css/modern-theme.css'); ?>" rel="stylesheet">

    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body class="animate-fade-in">
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
         style="background: rgba(255,255,255,0.9); z-index: 9999; backdrop-filter: blur(5px); display: none;">
        <div class="text-center">
            <div class="loading-spinner mb-3"></div>
            <p class="text-muted">Loading...</p>
        </div>
    </div>

    <!-- Enhanced Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand animate-slide-in-left" href="<?php echo app_url('public/index.php?action=home'); ?>">
                <i class="fas fa-rocket me-2"></i>
                <span class="fw-bold">CrowdFund</span><span class="text-primary">Pro</span>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($_GET['action'] ?? 'home') == 'home' ? 'active' : ''; ?>"
                           href="<?php echo app_url('public/index.php?action=home'); ?>">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($_GET['action'] ?? '') == 'list' ? 'active' : ''; ?>"
                           href="<?php echo app_url('public/index.php?action=list'); ?>">
                            <i class="fas fa-grid-2 me-1"></i>Discover
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="exploreDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-compass me-1"></i>Explore
                        </a>
                        <ul class="dropdown-menu shadow-lg border-0 rounded-3">
                            <li><a class="dropdown-item" href="<?php echo app_url('public/index.php?action=categories'); ?>">
                                <i class="fas fa-th-large me-2 text-primary"></i>Categories
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo app_url('public/index.php?action=trending'); ?>">
                                <i class="fas fa-fire me-2 text-danger"></i>Trending
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo app_url('public/index.php?action=ending_soon'); ?>">
                                <i class="fas fa-clock me-2 text-warning"></i>Ending Soon
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo app_url('public/index.php?action=search'); ?>">
                                <i class="fas fa-search me-2 text-info"></i>Advanced Search
                            </a></li>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($_GET['action'] ?? '') == 'create' ? 'active' : ''; ?>"
                               href="<?php echo app_url('public/index.php?action=create'); ?>">
                                <i class="fas fa-plus-circle me-1"></i>Start Project
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Search Bar -->
                <form class="d-flex me-3" method="GET" action="<?php echo app_url('public/index.php'); ?>">
                    <input type="hidden" name="action" value="search">
                    <div class="input-group">
                        <input class="form-control border-0 bg-light" type="search" name="q"
                               placeholder="Search projects..." aria-label="Search"
                               value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        <button class="btn btn-outline-primary border-0" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- User Menu -->
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Notifications -->
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative" href="#" id="notificationsDropdown"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                      style="font-size: 0.6rem;">3</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" style="width: 300px;">
                                <li class="dropdown-header">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2" href="#">
                                    <div class="d-flex">
                                        <i class="fas fa-heart text-danger me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-semibold">New donation received!</div>
                                            <small class="text-muted">Someone backed your project</small>
                                        </div>
                                    </div>
                                </a></li>
                                <li><a class="dropdown-item py-2" href="#">
                                    <div class="d-flex">
                                        <i class="fas fa-star text-warning me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-semibold">Project featured!</div>
                                            <small class="text-muted">Your project is now featured</small>
                                        </div>
                                    </div>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center text-primary" href="#">View all notifications</a></li>
                            </ul>
                        </li>

                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    <?php echo strtoupper(substr($_SESSION['user']['name'], 0, 2)); ?>
                                </div>
                                <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                                <li class="dropdown-header">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                             style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($_SESSION['user']['name'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></small>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('public/index.php?action=dashboard'); ?>">
                                    <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('public/index.php?action=profile'); ?>">
                                    <i class="fas fa-user me-2 text-info"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('public/index.php?action=settings'); ?>">
                                    <i class="fas fa-cog me-2 text-secondary"></i>Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo app_url('public/index.php?action=logout'); ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="<?php echo app_url('public/index.php?action=login'); ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm" href="<?php echo app_url('public/index.php?action=register'); ?>">
                                <i class="fas fa-user-plus me-1"></i>Get Started
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content" style="padding-top: 100px;">
        <!-- Page Content -->
        <div class="container-fluid">
            <?php
            if (isset($content)) {
                echo $content;
            } else {
                echo '<div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <h4>Content Not Available</h4>
                        <p>The requested content could not be loaded.</p>
                      </div>';
            }
            ?>
        </div>
    </main>

    <!-- Enhanced Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-rocket me-2"></i>
                        <span class="fw-bold">CrowdFund</span><span class="text-primary">Pro</span>
                    </h5>
                    <p class="text-muted mb-3">
                        Empowering innovators and creators to bring their ideas to life through community-driven funding.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-decoration-none">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-decoration-none">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-decoration-none">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-decoration-none">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Explore</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo app_url('public/index.php?action=categories'); ?>">Categories</a></li>
                        <li class="mb-2"><a href="<?php echo app_url('public/index.php?action=trending'); ?>">Trending</a></li>
                        <li class="mb-2"><a href="<?php echo app_url('public/index.php?action=ending_soon'); ?>">Ending Soon</a></li>
                        <li class="mb-2"><a href="<?php echo app_url('public/index.php?action=search'); ?>">Search</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Support</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Help Center</a></li>
                        <li class="mb-2"><a href="#">Contact Us</a></li>
                        <li class="mb-2"><a href="#">Community</a></li>
                        <li class="mb-2"><a href="#">Guidelines</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Legal</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#">Terms of Service</a></li>
                        <li class="mb-2"><a href="#">Cookie Policy</a></li>
                        <li class="mb-2"><a href="#">Security</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Stay Updated</h5>
                    <p class="text-muted small mb-3">Get the latest projects and updates</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control form-control-sm" placeholder="Your email">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4">

            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted small mb-0">
                        &copy; <?php echo date('Y'); ?> CrowdFundPro. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted small mb-0">
                        Made with <i class="fas fa-heart text-danger"></i> for innovators worldwide
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle"
            style="width: 50px; height: 50px; display: none; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Enhanced JavaScript for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loading overlay
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 500);
            }

            // Navbar scroll effect
            const navbar = document.getElementById('mainNavbar');
            let lastScrollTop = 0;

            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                // Hide/show navbar on scroll
                if (scrollTop > lastScrollTop && scrollTop > 200) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                }
                lastScrollTop = scrollTop;
            });

            // Back to top button
            const backToTopBtn = document.getElementById('backToTop');

            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            });

            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Enhanced form interactions
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                        submitBtn.disabled = true;
                    }
                });
            });

            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, observerOptions);

            // Observe cards and other elements
            document.querySelectorAll('.card, .alert, .badge').forEach(el => {
                observer.observe(el);
            });

            // Enhanced tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Search suggestions (if search input exists)
            const searchInput = document.querySelector('input[name="q"]');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();

                    if (query.length >= 2) {
                        searchTimeout = setTimeout(() => {
                            // Implement search suggestions here
                            console.log('Search suggestions for:', query);
                        }, 300);
                    }
                });
            }
        });

        // Global utility functions
        window.showNotification = function(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 5000);
        };

        // Loading state for AJAX requests
        window.showLoading = function() {
            document.getElementById('loading-overlay').style.display = 'flex';
        };

        window.hideLoading = function() {
            document.getElementById('loading-overlay').style.display = 'none';
        };
    </script>
</body>
</html>
