<?php
// Set the title of the page
$title = "Connexion";

// Capture the content of the view
ob_start();
?>
<h1>Connexion</h1>

<form action="/auth/login.php" method="POST">
    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Password :</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Log in</button>
</form>

<p>Not yet a subscriber? <a href="/auth/register.php">Create an account</a></p>
<?php
$content = ob_get_clean();

// Include the main layout
require __DIR__ . '/../layout.php';
