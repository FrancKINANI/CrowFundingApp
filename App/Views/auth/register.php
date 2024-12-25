<?php
$title = "Inscription";

ob_start();
?>
<div class="container mt-5">
    <h1 class="text-center">Inscription</h1>

    <form action="/php/PHPCrowFundingApp/public/index.php?action=register" method="POST" class="mt-4">
        <div class="form-group">
            <label for="name">Nom :</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Password :</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm password :</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Sign up</button>
    </form>

    <p class="mt-3">Already subscribed ? <a href="/php/PHPCrowFundingApp/App/Views/auth/login.php">Connect</a></p>
</div>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';