<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Crowdfunding'; ?></title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/auth/login.php">Login</a></li>
                <li><a href="/auth/register.php">Registration</a></li>
                <li><a href="/projects/create.php">Create a new project</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        // Include content of specific view
        if (isset($content)) {
            echo $content;
        } else {
            echo '<p>Content not available.</p>';
        }
        ?>
    </main>

    <footer>
        <p>&copy; 2024 Crowdfunding Platform</p>
    </footer>
</body>
</html>
