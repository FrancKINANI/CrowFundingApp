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
    <title>Home - IdeaNest</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
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
                <?php endif; ?>
                <?php if (isset($_SESSION['user'])): ?>
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
        <h1 class="text-center">List of projects (Hi)</h1>
        <div class="row">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($project['description']); ?></p>
                                <p class="card-text"><strong>Goal : </strong><?php echo htmlspecialchars($project['goal_amount']); ?> €</p>
                                <a href="/php/PHPCrowFundingApp/public/index.php?action=projectDetails&id=<?php echo $project['id']; ?>" class="btn btn-primary">See the project</a>
                                <a href="php/PHPCrowFundingApp/public/index.php?action=donate&project_id=<?php echo $project['id']; ?>" class="btn btn-primary">Donate</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No projects found.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center mt-4">
        <p>&copy; 2024 IdeaNest. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>