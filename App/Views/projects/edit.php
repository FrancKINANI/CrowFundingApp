<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<div class="container mt-4">
    <h1 class="text-center">Edit Project</h1>
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <form action="/php/PHPCrowFundingApp/public/index.php?action=updateProject&project_id=<?= htmlspecialchars($project['id']) ?>" method="POST">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($project['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($project['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="goalAmount">Goal Amount (€)</label>
            <input type="number" class="form-control" id="goalAmount" name="goalAmount" value="<?= htmlspecialchars($project['goal_amount']) ?>" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Project</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';