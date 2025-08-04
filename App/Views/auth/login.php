<?php
$title = "Login - " . APP_NAME;

ob_start();
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Login</h1>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo app_url('public/index.php?action=login'); ?>" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required
                                   autocomplete="email">
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required
                                   autocomplete="current-password">
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Don't have an account?</p>
                        <a href="<?php echo app_url('public/index.php?action=register'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
