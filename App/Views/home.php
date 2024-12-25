<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Crowdfunding</title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
    <h1>Welcome to the Crowdfunding platform</h1>

    <h2>Available projects</h2>
    <div class="projects">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <div class="project">
                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                    <p>Goal : <?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
                    <a href="/projects/view.php?id=<?php echo $project['id']; ?>">See the project</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No project available at this time.</p>
        <?php endif; ?>
    </div>
</body>
</html>
