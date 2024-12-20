<?php $title = "Edit Project"; ob_start(); ?>
<h2>Edit Project</h2>
<form action="/index.php?action=editProject&id=<?= $id ?>" method="POST">
    <label>Title:</label>
    <input type="text" name="title" value="<?= $project['title'] ?>" required>
    <label>Description:</label>
    <textarea name="description"><?= $project['description'] ?></textarea>
    <label>Goal Amount:</label>
    <input type="number" name="goalAmount" value="<?= $project['goalAmount'] ?>" required>
    <button type="submit">Update</button>
</form>
<?php $content = ob_get_clean(); include '../layout.php'; ?>
