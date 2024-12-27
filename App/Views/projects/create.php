<?php $title = "New Project"; ob_start(); ?>
<div class="container mt-5">
    <h1 class="text-center">Create a project</h1>
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <form action="/php/PHPCrowFundingApp/public/index.php?action=create" method="POST" class="mt-4">
        <div class="form-group">
            <label for="title">Title :</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="goalAmount">Goal amount :</label>
            <input type="number" name="goalAmount" id="goalAmount" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
<?php 
    $content = ob_get_clean(); 
    require __DIR__ . '/../layout.php';