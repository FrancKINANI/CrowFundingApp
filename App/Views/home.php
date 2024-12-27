<?php
    if(!isset($_SESSION)){
        session_start();
    }
    ob_start();
?>    
<div class="container mt-4">
<h1 class="text-center">List of projects</h1>
<div class="row">
    <?php if (!empty($projects)): ?>
        <?php foreach ($projects as $project): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($project['description']); ?></p>
                        <p class="card-text"><strong>Goal : </strong><?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
                        <a href="/php/PHPCrowFundingApp/public/index.php?action=projectDetails&id=<?php echo $project['id']; ?>" class="btn btn-primary">See the project</a>
                        <a href="/php/PHPCrowFundingApp/public/index.php?action=donate&project_id=<?php echo $project['id']; ?>" class="btn btn-success">Donate</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">No projects found.</p>
    <?php endif; ?>
</div>
</div>
<?php $content = ob_get_clean(); require __DIR__ .'/layout.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

