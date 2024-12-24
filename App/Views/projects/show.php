<?php $title = "Projects"; ob_start(); ?>
<h2>Projects</h2>
<?php if (!empty($projects)): ?>
    <ul>
        <?php foreach ($projects as $project): ?>
            <li>
                <h3><?= htmlspecialchars($project['title']) ?></h3>
                <p><?= htmlspecialchars($project['description']) ?></p>
                <p>Goal: $<?= htmlspecialchars($project['goal']) ?></p>
                <a href="/public/index.php?action=projectDetails&id=<?= $project['id'] ?>">View Details</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No projects available.</p>
<?php endif; ?>
<?php $content = ob_get_clean(); include 'layout.php'; ?>
