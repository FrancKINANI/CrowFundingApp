<?php $title = "Contribute"; ob_start(); ?>
<h2>Contribute to Project</h2>
<form action="/index.php?action=contribute" method="POST">
    <label>Project ID:</label>
    <input type="number" name="projectId" required>
    <label>User ID:</label>
    <input type="number" name="userId" required>
    <label>Amount:</label>
    <input type="number" name="amount" required>
    <button type="submit">Contribute</button>
</form>
<?php $content = ob_get_clean(); include '../layout.php'; ?>
