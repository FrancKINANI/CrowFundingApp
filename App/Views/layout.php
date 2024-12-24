<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Crowdfunding App" ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/public/index.php?action=home">Home</a></li>
                <li><a href="/public/index.php?action=projects">Projects</a></li>
                <li><a href="/public/index.php?action=login">Login</a></li>
                <li><a href="/public/index.php?action=register">Register</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?= $content ?>
    </main>
    <footer>
        <p>&copy; 2024 Crowdfunding App</p>
    </footer>
    <script src="/assets/js/main.js"></script>
</body>
</html>
