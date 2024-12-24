<?php $title = "Register"; ob_start(); ?>
<h2>Register</h2>
<form action="/public/index.php?action=register" method="POST">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">Register</button>
</form>
<?php $content = ob_get_clean(); include 'layout.php'; ?>
