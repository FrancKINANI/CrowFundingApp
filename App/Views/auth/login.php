<?php $title = "Login"; ob_start(); ?>
<h2>Login</h2>
<form action="/public/index.php?action=login" method="POST">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">Login</button>
</form>
<?php $content = ob_get_clean(); include 'layout.php'; ?>
