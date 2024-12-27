<?php
$title = "Inscription";

if(!isset($_SESSION)){
    session_start();
}
ob_start();
?>
<div class="container mt-5">
    <h1 class="text-center">Inscription</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form action="/php/PHPCrowFundingApp/public/index.php?action=register" method="POST">
                <div class="form-group">
                    <label for="name">Name :</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password :</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password :</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            <p class="text-center mt-3">Already have an account? <a href="/php/PHPCrowFundingApp/public/index.php?action=login">Log in</a></p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';
