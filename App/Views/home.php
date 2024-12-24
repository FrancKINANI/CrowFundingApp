<?php $title = "Home"; ob_start(); ?>
<h1>Welcome to Crowdfunding App</h1>
<p>Join us to create or support amazing projects!</p>
<?php $content = ob_get_clean(); include 'layout.php'; ?>
