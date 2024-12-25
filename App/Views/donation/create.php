<?php
    if(!isset($_SESSION)){
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Donation - IdeaNest</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/php/PHPCrowFundingApp/public/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/php/PHPCrowFundingApp/public/index.php">IdeaNest</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/php/PHPCrowFundingApp/public/index.php?action=home">Home</a>
                </li>
                <?php if (!isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/php/PHPCrowFundingApp/public/index.php?action=login">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/php/PHPCrowFundingApp/public/index.php?action=register">Inscription</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/php/PHPCrowFundingApp/public/index.php?action=create">New project</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/php/PHPCrowFundingApp/public/index.php?action=dashboard">Dashboard</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center">Make a Donation</h1>
        <form action="/php/PHPCrowFundingApp/public/index.php?action=submitDonation" method="POST">
            <div class="form-group">
                <label for="project">Project</label>
                <select class="form-control" id="project" name="project_id" required>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['title']); ?></option>
                    <?php endforeach; ?>
                </select>
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
</body>
</html>