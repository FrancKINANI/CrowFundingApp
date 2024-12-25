<?php
// Set the title of the page
$title = "Inscription";

// Capture the content of the view
ob_start();
?>
<h1>Inscription</h1>

<form action="/auth/register.php" method="POST">
    <label for="name">Name :</label>
    <input type="text" id="name" name="name" required>

    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Password :</label>
    <input type="password" id="password" name="password" required>

    <label for="confirm_password">Confirm password :</label>
    <input type="password" id="confirm_password" name="confirm_password" required>

    <button type="submit">Register</button>
</form>

<p>Already subscribed? <a href="/auth/login.php">Log in</a></p>
<?php
$content = ob_get_clean();

// Include the main layout
require __DIR__ . '/../layout.php';
