<?php
$title = "Register";
ob_start();
?>
<h2>Register</h2>
<form action="../public/index.php?action=register" method="POST">
    <label>Name:</label>
    <input type="text" name="name" required>
    <label>Email:</label>
    <input type="email" name="email" required>
    <label>Password:</label>
    <input type="password" name="password" required>
    <button type="submit">Register</button>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';