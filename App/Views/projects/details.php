<?php $title = "Project Details"; ob_start(); ?>
<h2>Project Details</h2>

<p><strong>Title:</strong> <?= htmlspecialchars($project['title']) ?></p>
<p><strong>Description:</strong> <?= htmlspecialchars($project['description']) ?></p>
<p><strong>Goal Amount:</strong> <?= htmlspecialchars($project['goalAmount']) ?> €</p>

<h3>Contributions</h3>
<?php if (!empty($contributions)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Contributed By</th>
                <?php if ($currentUser): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contributions as $contribution): ?>
                <tr>
                    <td><?= htmlspecialchars($contribution['amount']) ?> €</td>
                    <td><?= htmlspecialchars($contribution['userName']) ?></td>
                    <?php if ($contribution['userId'] == $currentUser['id']): ?>
                        <td>
                            <a href="/index.php?action=editContribution&id=<?= $contribution['id'] ?>">Edit</a> | 
                            <a href="/index.php?action=deleteContribution&id=<?= $contribution['id'] ?>" 
                            onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No contributions for this project yet.</p>
<?php endif; ?>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
