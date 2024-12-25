<?php
$title = "Connexion";

ob_start();
?>
<div class="container mt-5">
    <h1 class="text-center">Connexion</h1>

    <form action="/php/PHPCrowFundingApp/public/index.php?action=login" method="POST" class="mt-4">
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Password :</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Sign up</button>
    </form>

    <p class="mt-3">Not yet subscribed? <a href="/php/PHPCrowFundingApp/App/Views/auth/register.php">Create an account</a></p>
</div>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';