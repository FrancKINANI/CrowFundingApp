<?php $title = "Projects List"; ob_start(); ?>
<h2>List of Projects</h2>

<?php if (!empty($projects)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Goal Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['title']) ?></td>
                    <td><?= htmlspecialchars($project['description']) ?></td>
                    <td><?= htmlspecialchars($project['goalAmount']) ?> €</td>
                    <td>
                        <a href="/index.php?action=projectDetails&id=<?= $project['id'] ?>">Details</a>
                        
                        <?php if ($project['createdBy'] == $currentUser['id']): ?>
                            | <a href="/index.php?action=editProject&id=<?= $project['id'] ?>">Edit</a>
                            | <a href="/index.php?action=deleteProject&id=<?= $project['id'] ?>" 
                            onclick="return confirm('Are you sure?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No projects available.</p>
<?php endif; ?>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
