<?php
$title = "Dashboard - " . APP_NAME;

ob_start();
?>

<!-- Dashboard Header -->
<div class="dashboard-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-2">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>! 👋
                </h1>
                <p class="lead mb-0">Here's what's happening with your projects and contributions</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex gap-2 justify-content-lg-end justify-content-start mt-3 mt-lg-0">
                    <a href="<?php echo app_url('public/index.php?action=create'); ?>"
                       class="btn btn-light btn-lg hover-lift">
                        <i class="fas fa-plus-circle me-2"></i>New Project
                    </a>
                    <a href="<?php echo app_url('public/index.php?action=list'); ?>"
                       class="btn btn-outline-light btn-lg hover-lift">
                        <i class="fas fa-compass me-2"></i>Explore
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Stats -->
<div class="container mt-n3">
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card modern-card text-center hover-lift">
                <div class="stat-icon bg-primary bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-project-diagram fa-lg text-primary"></i>
                </div>
                <h3 class="fw-bold text-primary mb-1"><?php echo count($userProjects ?? []); ?></h3>
                <p class="text-muted mb-0">Projects Created</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card modern-card text-center hover-lift">
                <div class="stat-icon bg-success bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-dollar-sign fa-lg text-success"></i>
                </div>
                <h3 class="fw-bold text-success mb-1">$0</h3>
                <p class="text-muted mb-0">Total Raised</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card modern-card text-center hover-lift">
                <div class="stat-icon bg-warning bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-heart fa-lg text-warning"></i>
                </div>
                <h3 class="fw-bold text-warning mb-1">0</h3>
                <p class="text-muted mb-0">Donations Made</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card modern-card text-center hover-lift">
                <div class="stat-icon bg-info bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-users fa-lg text-info"></i>
                </div>
                <h3 class="fw-bold text-info mb-1">0</h3>
                <p class="text-muted mb-0">Projects Backed</p>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Content -->
<div class="container">
    <div class="row g-4">
        <!-- My Projects -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-project-diagram me-2 text-primary"></i>My Projects
                    </h4>
                    <a href="<?php echo app_url('public/index.php?action=create'); ?>"
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>New Project
                    </a>
                </div>
                <div class="modern-card-body">
                    <?php if (!empty($userProjects)): ?>
                        <div class="row g-3">
                            <?php foreach ($userProjects as $project): ?>
                                <div class="col-md-6">
                                    <div class="project-card border rounded-3 p-3 hover-lift transition-all">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($project['title']); ?></h6>
                                            <span class="badge badge-primary-modern">Active</span>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            Goal: €<?php echo number_format($project['goal_amount'], 0); ?>
                                        </p>

                                        <!-- Progress Bar -->
                                        <?php
                                        $currentAmount = $project['total_donations'] ?? 0;
                                        $percentage = $project['goal_amount'] > 0 ? ($currentAmount / $project['goal_amount']) * 100 : 0;
                                        $percentage = min($percentage, 100);
                                        ?>
                                        <div class="progress-modern mb-2">
                                            <div class="progress-bar-modern" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between small text-muted mb-3">
                                            <span>€<?php echo number_format($currentAmount, 0); ?> raised</span>
                                            <span><?php echo number_format($percentage, 1); ?>%</span>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="<?php echo app_url('public/index.php?action=projectDetails&id=' . $project['id']); ?>"
                                               class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                            <a href="<?php echo app_url('public/index.php?action=editProject&id=' . $project['id']); ?>"
                                               class="btn btn-outline-secondary btn-sm flex-fill">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-project-diagram fa-4x text-muted mb-3"></i>
                            <h5>No Projects Yet</h5>
                            <p class="text-muted mb-4">Start your crowdfunding journey by creating your first project!</p>
                            <a href="<?php echo app_url('public/index.php?action=create'); ?>"
                               class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Create Your First Project
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Activity -->
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2 text-info"></i>Recent Activity
                    </h5>
                </div>
                <div class="modern-card-body">
                    <?php if (!empty($userDonations)): ?>
                        <div class="activity-list">
                            <?php foreach (array_slice($userDonations, 0, 5) as $donation): ?>
                                <?php $project = $donationProjects[$donation['project_id']] ?? null; ?>
                                <?php if ($project): ?>
                                    <div class="activity-item d-flex align-items-center py-2 border-bottom">
                                        <div class="activity-icon bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-heart text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">Donated €<?php echo number_format($donation['amount'], 0); ?></div>
                                            <small class="text-muted">to <?php echo htmlspecialchars($project['title']); ?></small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h6>No Recent Activity</h6>
                            <p class="text-muted small">Your activity will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo app_url('public/index.php?action=create'); ?>"
                           class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Create New Project
                        </a>
                        <a href="<?php echo app_url('public/index.php?action=categories'); ?>"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-th-large me-2"></i>Browse Categories
                        </a>
                        <a href="<?php echo app_url('public/index.php?action=trending'); ?>"
                           class="btn btn-outline-info">
                            <i class="fas fa-fire me-2"></i>Trending Projects
                        </a>
                        <a href="<?php echo app_url('public/index.php?action=profile'); ?>"
                           class="btn btn-outline-dark">
                            <i class="fas fa-user me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    margin-top: -100px;
    padding-top: 150px;
}

.stat-card {
    padding: 2rem 1.5rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.project-card {
    background: #f8fafc;
    transition: all 0.3s ease;
}

.project-card:hover {
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.activity-item:last-child {
    border-bottom: none !important;
}

@media (max-width: 768px) {
    .dashboard-header {
        margin-top: -80px;
        padding-top: 120px;
    }

    .stat-card {
        padding: 1.5rem 1rem;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
