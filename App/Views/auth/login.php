<?php $title = "Login"; ob_start(); ?>
<h2>Login</h2>
<form action="../public/index.php?action=login" method="POST">
    <label>Email:</label>
    <input type="email" name="email" required>
    <label>Password:</label>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
