<?php
$title = "Register - " . APP_NAME;

if(!isset($_SESSION)){
    session_start();
}
ob_start();
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Create Account</h1>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo app_url('public/index.php?action=register'); ?>" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                   required
                                   minlength="2"
                                   maxlength="100"
                                   autocomplete="name">
                            <div class="invalid-feedback">
                                Please enter your full name (2-100 characters).
                            </div>
                        </div>

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
                                   minlength="8"
                                   autocomplete="new-password">
                            <div class="invalid-feedback">
                                Password must be at least 8 characters long.
                            </div>
                            <div class="form-text">
                                Password must contain at least 8 characters with uppercase, lowercase, number, and special character.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password">
                            <div class="invalid-feedback">
                                Please confirm your password.
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Already have an account?</p>
                        <a href="<?php echo app_url('public/index.php?action=login'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side form validation and password matching
(function() {
    'use strict';

    // Password confirmation validation
    function validatePasswordConfirmation() {
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');

        if (password.value !== passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('Passwords do not match');
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }

    window.addEventListener('load', function() {
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');

        password.addEventListener('input', validatePasswordConfirmation);
        passwordConfirmation.addEventListener('input', validatePasswordConfirmation);

        // Form validation
        const forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                validatePasswordConfirmation();
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
