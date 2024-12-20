<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdfunding Platform</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Crowdfunding Platform</h1>
            <nav>
                <ul>
                    <li><a href="../../public/index.php?action=home">Home</a></li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="../../public/index.php?action=userDashboard">My Dashboard</a></li>
                        <li><a href="../../public/index.php?action=logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="../../public/index.php?action=login">Login</a></li>
                        <li><a href="../../public/index.php?action=register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash-message">
                    <?= htmlspecialchars($_SESSION['flash']); ?>
                    <?php unset($_SESSION['flash']); ?>
                </div>
            <?php endif; ?>

            <?= $content ?? ''; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y'); ?> Crowdfunding Platform. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
