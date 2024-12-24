<?php ob_start(); ?>
<h2>Projects</h2>
<ul>
    <?php foreach ($projects as $project): ?>
        <li><a href="/projects/show?id=<?= $project['id'] ?>"><?= $project['title'] ?></a></li>
    <?php endforeach; ?>
</ul>
<a href="/projects/create">Create a new project</a>
<?php $content = ob_get_clean(); include 'layout.php'; ?>
