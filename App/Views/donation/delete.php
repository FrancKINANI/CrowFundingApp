<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<div class="container mt-4">
    <h1 class="text-center">Delete Donation</h1>
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <p>Are you sure you want to delete the donation of <strong><?php echo htmlspecialchars($donation['amount']); ?> €</strong> for the project <strong><?php echo htmlspecialchars($project['title']); ?></strong>?</p>
    <form action="/php/PHPCrowFundingApp/public/index.php?action=confirmDeleteDonation&id=<?= htmlspecialchars($donation['id']) ?>" method="POST">
        <button type="submit" class="btn btn-danger">Yes, delete it</button>
        <a href="/php/PHPCrowFundingApp/public/index.php?action=dashboard" class="btn btn-secondary">No, go back</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';