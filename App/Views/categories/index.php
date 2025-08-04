<?php 
$title = "Browse Categories - " . APP_NAME; 
ob_start(); 
?>

<div class="container mt-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">Explore Categories</h1>
            <p class="lead text-muted">Discover amazing projects across different categories</p>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="row">
        <?php if (isset($categories) && !empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card category-card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <!-- Category Icon -->
                            <div class="category-icon mb-3" style="color: <?php echo htmlspecialchars($category['color']); ?>">
                                <i class="<?php echo htmlspecialchars($category['icon']); ?> fa-3x"></i>
                            </div>
                            
                            <!-- Category Name -->
                            <h5 class="card-title mb-2">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h5>
                            
                            <!-- Category Description -->
                            <p class="card-text text-muted small mb-3">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </p>
                            
                            <!-- Project Count -->
                            <div class="mb-3">
                                <span class="badge bg-primary rounded-pill">
                                    <?php echo $category['project_count']; ?> 
                                    <?php echo $category['project_count'] == 1 ? 'Project' : 'Projects'; ?>
                                </span>
                            </div>
                            
                            <!-- Browse Button -->
                            <a href="<?php echo app_url('public/index.php?action=category&id=' . $category['id']); ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>Browse Projects
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h4>No Categories Available</h4>
                    <p>Categories will appear here once they are created.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Call to Action -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="bg-light rounded p-5">
                <h3 class="mb-3">Can't Find What You're Looking For?</h3>
                <p class="text-muted mb-4">
                    Explore all projects or use our advanced search to find exactly what interests you.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?php echo app_url('public/index.php?action=list'); ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>View All Projects
                    </a>
                    <a href="<?php echo app_url('public/index.php?action=search'); ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-search me-2"></i>Advanced Search
                    </a>
                    <a href="<?php echo app_url('public/index.php?action=trending'); ?>" 
                       class="btn btn-outline-success">
                        <i class="fas fa-fire me-2"></i>Trending Projects
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.category-icon {
    transition: transform 0.2s ease-in-out;
}

.category-card:hover .category-icon {
    transform: scale(1.1);
}

.gap-3 {
    gap: 1rem !important;
}

@media (max-width: 576px) {
    .gap-3 {
        gap: 0.5rem !important;
    }
    
    .d-flex.flex-wrap > * {
        margin-bottom: 0.5rem;
    }
}
</style>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php';
?>
