<?php 
$title = "Search Results - " . APP_NAME; 
ob_start(); 
?>

<div class="container mt-4">
    <!-- Search Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="mb-1">
                        <?php if (!empty($searchTerm)): ?>
                            Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"
                        <?php else: ?>
                            Browse Projects
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted mb-0">
                        Found <?php echo $totalProjects; ?> 
                        <?php echo $totalProjects == 1 ? 'project' : 'projects'; ?>
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="<?php echo app_url('public/index.php?action=search_advanced'); ?>" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sliders-h me-1"></i>Advanced Search
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" action="<?php echo app_url('public/index.php'); ?>" class="row g-3 align-items-end">
                        <input type="hidden" name="action" value="search">
                        
                        <!-- Search Input -->
                        <div class="col-md-4">
                            <label for="search-input" class="form-label small">Search Projects</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search-input"
                                   name="q" 
                                   value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>"
                                   placeholder="Enter keywords...">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="col-md-3">
                            <label for="category-filter" class="form-label small">Category</label>
                            <select class="form-select" id="category-filter" name="category">
                                <option value="">All Categories</option>
                                <?php if (isset($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($categoryId) && $categoryId == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Sort By -->
                        <div class="col-md-3">
                            <label for="sort-filter" class="form-label small">Sort By</label>
                            <select class="form-select" id="sort-filter" name="sort">
                                <option value="newest" <?php echo ($sortBy ?? '') == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo ($sortBy ?? '') == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="funded_high" <?php echo ($sortBy ?? '') == 'funded_high' ? 'selected' : ''; ?>>Most Funded</option>
                                <option value="funded_low" <?php echo ($sortBy ?? '') == 'funded_low' ? 'selected' : ''; ?>>Least Funded</option>
                                <option value="goal_high" <?php echo ($sortBy ?? '') == 'goal_high' ? 'selected' : ''; ?>>Highest Goal</option>
                                <option value="goal_low" <?php echo ($sortBy ?? '') == 'goal_low' ? 'selected' : ''; ?>>Lowest Goal</option>
                                <option value="ending_soon" <?php echo ($sortBy ?? '') == 'ending_soon' ? 'selected' : ''; ?>>Ending Soon</option>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="row">
        <?php if (isset($projects) && !empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card project-card h-100 shadow-sm">
                        <!-- Project Image -->
                        <div class="position-relative">
                            <?php if (!empty($project['featured_image'])): ?>
                                <img src="<?php echo app_url('uploads/projects/' . $project['featured_image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($project['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Category Badge -->
                            <?php if (!empty($project['category_name'])): ?>
                                <span class="badge position-absolute top-0 start-0 m-2" 
                                      style="background-color: <?php echo $project['category_color'] ?? '#007bff'; ?>">
                                    <?php echo htmlspecialchars($project['category_name']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <span class="badge position-absolute top-0 end-0 m-2 
                                         <?php echo ($project['computed_status'] ?? $project['status']) == 'funded' ? 'bg-success' : 
                                                   (($project['computed_status'] ?? $project['status']) == 'expired' ? 'bg-danger' : 'bg-primary'); ?>">
                                <?php echo ucfirst($project['computed_status'] ?? $project['status']); ?>
                            </span>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <!-- Project Title -->
                            <h5 class="card-title">
                                <a href="<?php echo app_url('public/index.php?action=projectDetails&id=' . $project['id']); ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </a>
                            </h5>
                            
                            <!-- Creator -->
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user me-1"></i>
                                by <?php echo htmlspecialchars($project['creator_name']); ?>
                            </p>
                            
                            <!-- Short Description -->
                            <p class="card-text text-muted small flex-grow-1">
                                <?php 
                                $description = $project['short_description'] ?? $project['description'];
                                echo htmlspecialchars(substr($description, 0, 120)) . (strlen($description) > 120 ? '...' : ''); 
                                ?>
                            </p>
                            
                            <!-- Progress Bar -->
                            <?php 
                            $percentage = $project['funding_percentage'] ?? 
                                         ($project['goal_amount'] > 0 ? ($project['current_amount'] / $project['goal_amount']) * 100 : 0);
                            $percentage = min($percentage, 100);
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>$<?php echo number_format($project['current_amount'], 0); ?> raised</span>
                                    <span><?php echo number_format($percentage, 1); ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%"
                                         aria-valuenow="<?php echo $percentage; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <span>Goal: $<?php echo number_format($project['goal_amount'], 0); ?></span>
                                    <span><?php echo $project['backer_count'] ?? 0; ?> backers</span>
                                </div>
                            </div>
                            
                            <!-- Days Remaining -->
                            <?php if (isset($project['days_remaining']) && $project['days_remaining'] !== null): ?>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php if ($project['days_remaining'] > 0): ?>
                                            <?php echo $project['days_remaining']; ?> days remaining
                                        <?php else: ?>
                                            Campaign ended
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Action Button -->
                            <a href="<?php echo app_url('public/index.php?action=projectDetails&id=' . $project['id']); ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- No Results -->
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3>No Projects Found</h3>
                    <p class="text-muted mb-4">
                        <?php if (!empty($searchTerm)): ?>
                            We couldn't find any projects matching "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No projects are currently available.
                        <?php endif; ?>
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="<?php echo app_url('public/index.php?action=list'); ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>Browse All Projects
                        </a>
                        <a href="<?php echo app_url('public/index.php?action=categories'); ?>" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-th-large me-2"></i>Browse Categories
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="row mt-4">
            <div class="col-12">
                <nav aria-label="Search results pagination">
                    <ul class="pagination justify-content-center">
                        <?php
                        $currentPage = $page ?? 1;
                        $queryParams = $_GET;
                        
                        // Previous page
                        if ($currentPage > 1):
                            $queryParams['page'] = $currentPage - 1;
                            $prevUrl = app_url('public/index.php?' . http_build_query($queryParams));
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $prevUrl; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        // Page numbers
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                            $queryParams['page'] = $i;
                            $pageUrl = app_url('public/index.php?' . http_build_query($queryParams));
                        ?>
                            <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        // Next page
                        if ($currentPage < $totalPages):
                            $queryParams['page'] = $currentPage + 1;
                            $nextUrl = app_url('public/index.php?' . http_build_query($queryParams));
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $nextUrl; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.project-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
}

.project-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.gap-3 {
    gap: 1rem !important;
}
</style>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php';
?>
