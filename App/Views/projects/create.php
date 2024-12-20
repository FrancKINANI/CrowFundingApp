<?php $title = "Create Project"; ob_start(); ?>
<h2>Create Project</h2>
<form action="/index.php?action=createProject" method="POST">
    <label>Title:</label>
    <input type="text" name="title" required>
    <label>Description:</label>
    <textarea name="description" required></textarea>
    <label>Goal Amount:</label>
    <input type="number" name="goalAmount" required>
    <button type="submit">Create</button>
</form>
<?php $content = ob_get_clean(); include '../layout.php'; ?>
