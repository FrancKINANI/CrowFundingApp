<?php $title = "Delete User"; ob_start(); ?>
<h2>Delete User</h2>

<p>Are you sure you want to delete the user <strong><?= htmlspecialchars($user['username']) ?></strong>?</p>

<form method="POST" action="/index.php?action=destroyUser&id=<?= $user['id'] ?>">
    <button type="submit">Yes, Delete</button>
    <a href="/index.php?action=listUsers">Cancel</a>
</form>

<?php $content = ob_get_clean(); include '../layout.php'; ?>
