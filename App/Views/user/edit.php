<?php $title = "Edit User"; ob_start(); ?>
<h2>Edit User</h2>

<form method="POST" action="/index.php?action=updateUser&id=<?= $user['id'] ?>">
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>
    
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>
    
    <label for="role">Role:</label><br>
    <select id="role" name="role">
        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
    </select><br><br>
    
    <button type="submit">Update User</button>
</form>

<p><a href="/index.php?action=listUsers">Back to User List</a></p>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
