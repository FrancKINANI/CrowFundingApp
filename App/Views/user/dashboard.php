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
    <title>Dashboard - IdeaNest</title>
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
                <li class="nav-item">
                    <a class="nav-link" href="/php/PHPCrowFundingApp/public/index.php?action=create">New project</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/php/PHPCrowFundingApp/public/index.php?action=dashboard">Dashboard</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center">Dashboard</h1>
        <div class="row">
            <div class="col-md-12">
                <p>Welcome to your dashboard!</p>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Description</th>
                            <th>Goal Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userProjects)): ?>
                            <?php foreach ($userProjects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                                    <td><?php echo htmlspecialchars($project['description']); ?></td>
                                    <td><?php echo htmlspecialchars($project['goal_amount']); ?> €</td>
                                    <td>
                                        <a href="/php/PHPCrowFundingApp/public/index.php?action=editProject&id=<?php echo $project['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="/php/PHPCrowFundingApp/public/index.php?action=deleteProject&id=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No projects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>