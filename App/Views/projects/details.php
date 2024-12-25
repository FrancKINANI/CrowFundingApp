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
    <title>Project Details - IdeaNest</title>
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
        <h1 class="text-center"><?php echo htmlspecialchars($project['title']); ?></h1>
        <p><?php echo htmlspecialchars($project['description']); ?></p>
        <p><strong>Goal: </strong><?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
        <p><strong>Status: </strong><?php echo htmlspecialchars($project['status']); ?></p>
        <a href="/php/PHPCrowFundingApp/public/index.php?action=createDonation&project_id=<?php echo $project['id']; ?>" class="btn btn-success">Donate</a>
    </div>

    <div class="container mt-5">
        <h1><?php echo htmlspecialchars($project['title']); ?></h1>
        <p><strong>Description :</strong> <?php echo htmlspecialchars($project['description']); ?></p>
        <p><strong>Objectif :</strong> <?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
        <h3>Contributions</h3>
        <ul class="list-group">
            <?php if (!empty($donations)): ?>
                <?php foreach ($donations as $donation): ?>
                    <li class="list-group-item">
                        <p><strong>Montant :</strong> <?php echo htmlspecialchars($donation['amount']); ?> €</p>
                        <p><strong>Contributeur :</strong> <?php echo htmlspecialchars($donation['user_id']); ?></p>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item">Aucune contribution pour ce projet.</li>
            <?php endif; ?>
        </ul>
        <a href="/public/index.php?action=createDonation&projectId=<?= $project['id'] ?>" class="btn btn-primary mt-3">Faire un don</a>
        <a href="/public/index.php?action=index" class="btn btn-secondary mt-3">Retour à la liste des projets</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php $content = ob_get_clean(); require '../layout.php'; ?>