<?php $title = "My Contributions"; ob_start(); ?>
<h2>My Contributions</h2>

<?php if (!empty($contributions)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Project Title</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contributions as $contribution): ?>
                <tr>
                    <td><?= htmlspecialchars($contribution['projectTitle']) ?></td>
                    <td><?= htmlspecialchars($contribution['amount']) ?> €</td>
                    <td>
                        <a href="/index.php?action=editContribution&id=<?= $contribution['id'] ?>">Edit</a> | 
                        <a href="/index.php?action=deleteContribution&id=<?= $contribution['id'] ?>" 
                        onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You haven't made any contributions yet.</p>
<?php endif; ?>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
