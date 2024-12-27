<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<div class="container mt-5">
    <h1>Title: <?php echo htmlspecialchars($project['title']); ?></h1>
    <p><strong>Description :</strong> <?php echo htmlspecialchars($project['description']); ?></p>
    <p><strong>Goal :</strong> <?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
    <p><strong>Total Donations :</strong> <?php echo htmlspecialchars($totalDonations); ?> €</p>
    <p><strong>Percentage Remaining :</strong> <?php echo htmlspecialchars(floor($percentageRemaining)); ?> %</p>
    <h3>Donations</h3>
    <ul class="list-group">
        <?php if (!empty($donations)): ?>
            <?php foreach ($donations as $donation): ?>
                <li class="list-group-item">
                    <p><strong>Amount :</strong> <?php echo htmlspecialchars($donation['amount']); ?> €</p>
                    <p><strong>Contributor :</strong> <?php echo htmlspecialchars($donors[$donation['id']]['name']); ?></p>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="list-group-item">No donation for this project.</li>
        <?php endif; ?>
    </ul>
    <a href="/php/PHPCrowFundingApp/public/index.php?action=donate&project_id=<?= $project['id'] ?>" class="btn btn-primary mt-3">Donate</a>
    <a href="/php/PHPCrowFundingApp/public/index.php?action=list" class="btn btn-secondary mt-3">Return to list of projects</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php $content = ob_get_clean(); require __DIR__ .'/../layout.php'; ?>