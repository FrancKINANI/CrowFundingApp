<?php
$title = "Home - " . APP_NAME;

ob_start();
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-background"></div>
    <div class="container">
        <div class="row align-items-center min-vh-100 py-5">
            <div class="col-lg-6 animate-slide-in-left">
                <div class="hero-content">
                    <h1 class="display-3 fw-bold mb-4">
                        Turn Your <span class="text-primary">Dreams</span> Into Reality
                    </h1>
                    <p class="lead mb-4 text-muted">
                        Join the world's most innovative crowdfunding platform where creators and backers
                        come together to bring extraordinary ideas to life.
                    </p>
                    <div class="hero-stats mb-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="fw-bold text-primary mb-0">1,234</h3>
                                    <small class="text-muted">Projects Funded</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="fw-bold text-success mb-0">$2.5M</h3>
                                    <small class="text-muted">Total Raised</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="fw-bold text-warning mb-0">15K+</h3>
                                    <small class="text-muted">Happy Backers</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <?php if (!isset($_SESSION['user'])): ?>
                            <a href="<?php echo app_url('public/index.php?action=register'); ?>"
                               class="btn btn-primary btn-lg me-3 hover-lift">
                                <i class="fas fa-rocket me-2"></i>Start Your Project
                            </a>
                            <a href="<?php echo app_url('public/index.php?action=list'); ?>"
                               class="btn btn-outline-primary btn-lg hover-lift">
                                <i class="fas fa-compass me-2"></i>Explore Projects
                            </a>
                        <?php else: ?>
                            <a href="<?php echo app_url('public/index.php?action=create'); ?>"
                               class="btn btn-primary btn-lg me-3 hover-lift">
                                <i class="fas fa-plus-circle me-2"></i>Create Project
                            </a>
                            <a href="<?php echo app_url('public/index.php?action=dashboard'); ?>"
                               class="btn btn-outline-primary btn-lg hover-lift">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 animate-slide-in-right">
                <div class="hero-image text-center">
                    <div class="floating-cards">
                        <div class="floating-card card-1">
                            <i class="fas fa-lightbulb text-warning"></i>
                            <span>Innovation</span>
                        </div>
                        <div class="floating-card card-2">
                            <i class="fas fa-users text-primary"></i>
                            <span>Community</span>
                        </div>
                        <div class="floating-card card-3">
                            <i class="fas fa-heart text-danger"></i>
                            <span>Support</span>
                        </div>
                        <div class="floating-card card-4">
                            <i class="fas fa-star text-success"></i>
                            <span>Success</span>
                        </div>
                    </div>
                    <div class="hero-illustration">
                        <i class="fas fa-rocket fa-10x text-primary opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-arrow animate-bounce">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-3">Why Choose CrowdFundPro?</h2>
                <p class="lead text-muted">Everything you need to launch and fund your next big idea</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card modern-card text-center h-100 hover-lift">
                    <div class="feature-icon mb-4">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Secure & Trusted</h4>
                    <p class="text-muted">
                        Advanced security measures and transparent processes ensure your funds and data are always protected.
                    </p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i>SSL Encryption</li>
                        <li><i class="fas fa-check text-success me-2"></i>Secure Payments</li>
                        <li><i class="fas fa-check text-success me-2"></i>Fraud Protection</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-card modern-card text-center h-100 hover-lift">
                    <div class="feature-icon mb-4">
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-chart-line fa-2x text-success"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Analytics & Insights</h4>
                    <p class="text-muted">
                        Comprehensive analytics help you track progress, understand your audience, and optimize your campaign.
                    </p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i>Real-time Tracking</li>
                        <li><i class="fas fa-check text-success me-2"></i>Backer Analytics</li>
                        <li><i class="fas fa-check text-success me-2"></i>Performance Metrics</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-card modern-card text-center h-100 hover-lift">
                    <div class="feature-icon mb-4">
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x text-warning"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Global Community</h4>
                    <p class="text-muted">
                        Connect with backers and creators worldwide. Build relationships that last beyond your campaign.
                    </p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i>Global Reach</li>
                        <li><i class="fas fa-check text-success me-2"></i>Community Support</li>
                        <li><i class="fas fa-check text-success me-2"></i>Networking</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
