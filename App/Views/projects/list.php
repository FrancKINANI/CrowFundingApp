<?php ob_start(); ?>
<div class="container mt-5">
    <h1 class="text-center">Projects</h1>
    <ul class="list-group">
        <?php foreach ($projects as $project): ?>
            <li class="list-group-item">
                <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                <p><?php echo htmlspecialchars($project['description']); ?></p>
                <p><strong>Goal : </strong><?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
                <a href="/public/index.php?action=projectDetails&id=<?= $project['id'] ?>" class="btn btn-info">See details</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="/public/index.php?action=createProject" class="btn btn-success mt-3">Create a new project</a>
</div>
<?php $content = ob_get_clean(); require '../layout.php'; ?>