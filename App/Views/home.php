<?php
session_start();
require_once '../../Config/autoload.php';
require_once '../Models/Project.php';

// Fetch all projects
$projectModel = new Project();
$projects = $projectModel->getAll();

// Start buffering to inject content into layout
ob_start();
?>

<div class="home">
    <h1>Welcome to the Crowdfunding Platform</h1>
    <?php if (isset($_SESSION['user'])): ?>
        <p>Hello, <strong><?= htmlspecialchars($_SESSION['user']['username']); ?></strong>!</p>
        <a href="../../public/index.php?action=userDashboard" class="btn">Go to My Dashboard</a>
    <?php else: ?>
        <p>Welcome! Please <a href="../../public/index.php?action=login">log in</a> or <a href="../../public/index.php?action=register">register</a> to contribute or create projects.</p>
    <?php endif; ?>

    <h2>Available Projects</h2>
    <?php if (!empty($projects)): ?>
        <div class="projects-list">
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <h3><?= htmlspecialchars($project['title']); ?></h3>
                    <p><?= nl2br(htmlspecialchars($project['description'])); ?></p>
                    <p><strong>Goal:</strong> $<?= htmlspecialchars($project['goal']); ?></p>
                    <a href="../../public/index.php?action=projectDetails&id=<?= $project['id']; ?>" class="btn">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No projects available yet. Be the first to <a href="../../public/index.php?action=createProject">create one</a>!</p>
    <?php endif; ?>
</div>

<?php
// Inject content into the layout
$content = ob_get_clean();
require 'layout.php';
