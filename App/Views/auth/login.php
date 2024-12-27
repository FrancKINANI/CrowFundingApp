<?php
$title = "Connexion";

ob_start();
?>
<div class="container mt-5">
    <h1 class="text-center">Connexion</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="/php/PHPCrowFundingApp/public/index.php?action=login" method="POST">
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password :</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Log in</button>
            </form>
            <p class="text-center mt-3">Not yet a subscriber? <a href="/php/PHPCrowFundingApp/public/index.php?action=register">Create an account</a></p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
