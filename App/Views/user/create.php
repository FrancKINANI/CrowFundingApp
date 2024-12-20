<?php $title = "Create User"; ob_start(); ?>
<h2>Create New User</h2>

<form method="POST" action="/index.php?action=storeUser">
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" required><br><br>
    
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>
    
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    
    <label for="role">Role:</label><br>
    <select id="role" name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br><br>
    
    <button type="submit">Create User</button>
</form>

<p><a href="/index.php?action=listUsers">Back to User List</a></p>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
