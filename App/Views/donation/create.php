<?php
    $title = "Make a Donation";
    if(!isset($_SESSION)){
        session_start();
    }
?>
<div class="container mt-4">
    <h1 class="text-center">Make a Donation</h1>
    <form action="/php/PHPCrowFundingApp/public/index.php?action=submitDonation&project_id=<?= htmlspecialchars($_GET['project_id']) ?>" method="POST">
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
        <div class="form-group">
            <h1>Title: <?php echo htmlspecialchars($project['title']); ?></h1>
            <p><strong>Description :</strong> <?php echo htmlspecialchars($project['description']); ?></p>
            <p><strong>Goal :</strong> <?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
            <p><strong>Current Amount of Donations:</strong> <?php echo htmlspecialchars($totalDonations) ?> €</p>
        </div>
        <div class="form-group">
            <label for="amount">Donation Amount (€)</label>
            <input type="number" class="form-control" id="amount" name="amount" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary">Donate</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php
    $content = ob_get_clean();
    include __DIR__ . '/../layout.php';