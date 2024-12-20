<?php $title = "User Dashboard"; ob_start(); ?>
<h2>Welcome, <?= htmlspecialchars($user['username']) ?></h2>

<!-- Section pour les Projets -->
<h3>Your Projects</h3>

<?php if (!empty($projects)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Project Title</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['title']) ?></td>
                    <td><?= htmlspecialchars($project['description']) ?></td>
                    <td>
                        <a href="/index.php?action=projectDetails&id=<?= $project['id'] ?>">Details</a> |
                        <a href="/index.php?action=editProject&id=<?= $project['id'] ?>">Edit</a> |
                        <a href="/index.php?action=deleteProject&id=<?= $project['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this project?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have no projects.</p>
<?php endif; ?>

<p><a href="/index.php?action=createProject">Create New Project</a></p>

<!-- Section pour les Contributions -->
<h3>Your Contributions</h3>

<?php if (!empty($contributions)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Project Title</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contributions as $contribution): ?>
                <tr>
                    <td><?= htmlspecialchars($contribution['project_title']) ?></td>
                    <td><?= htmlspecialchars($contribution['amount']) ?> USD</td>
                    <td><?= htmlspecialchars($contribution['date']) ?></td>
                    <td>
                        <a href="/index.php?action=editContribution&id=<?= $contribution['id'] ?>">Edit</a> |
                        <a href="/index.php?action=deleteContribution&id=<?= $contribution['id'] ?>" 
                        onclick="return confirm('Are you sure you want to delete this contribution?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have not made any contributions yet.</p>
<?php endif; ?>

<p><a href="/index.php?action=listProjects">Explore Other Projects</a></p>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
