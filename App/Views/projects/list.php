<?php ob_start(); ?>
<div class="container mt-5">
    <h1 class="text-center">Projects</h1>
    <hr class="my-4">
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($projects)): ?>
        <ul class="list-group">
            <?php foreach ($projects as $project): ?>
                <li class="list-group-item">
                    <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                    <p><strong>Goal : </strong><?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
                    <a href="/php/PHPCrowFundingApp/public/index.php?action=projectDetails&id=<?= $project['id'] ?>" class="btn btn-info">See details</a>
                </li>
            <?php endforeach; ?>
    </ul>
    <?php else: ?>
            <p class="text-center">No projects found.</p>
        <?php endif; ?>
    <a href="/php/PHPCrowFundingApp/public/index.php?action=create" class="btn btn-success mt-3">Create a new project</a>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>