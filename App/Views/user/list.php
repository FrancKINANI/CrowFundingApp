<?php $title = "User List"; ob_start(); ?>
<h2>List of Users</h2>

<?php if (!empty($users)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="/index.php?action=editUser&id=<?= $user['id'] ?>">Edit</a> | 
                        <a href="/index.php?action=deleteUser&id=<?= $user['id'] ?>" 
                        onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>

<p><a href="/index.php?action=createUser">Create New User</a></p>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
