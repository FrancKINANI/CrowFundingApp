<?php
$title = "Create New Project - " . APP_NAME;
ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Project</h2>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo app_url('public/index.php?action=create'); ?>"
                          method="POST"
                          enctype="multipart/form-data"
                          class="needs-validation"
                          novalidate>

                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">Basic Information</h4>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label">Project Title *</label>
                                <input type="text"
                                       class="form-control"
                                       id="title"
                                       name="title"
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                       required
                                       minlength="3"
                                       maxlength="255">
                                <div class="invalid-feedback">
                                    Please provide a project title (3-255 characters).
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php if (isset($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"
                                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description</label>
                            <textarea class="form-control"
                                      id="short_description"
                                      name="short_description"
                                      rows="2"
                                      maxlength="500"
                                      placeholder="Brief summary of your project (max 500 characters)"><?php echo isset($_POST['short_description']) ? htmlspecialchars($_POST['short_description']) : ''; ?></textarea>
                            <div class="form-text">This will be shown in project listings and search results.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Full Description *</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="8"
                                      required
                                      minlength="50"
                                      placeholder="Detailed description of your project, goals, and what backers can expect..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <div class="invalid-feedback">
                                Please provide a detailed description (minimum 50 characters).
                            </div>
                        </div>

                        <!-- Funding Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">Funding Information</h4>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="goal_amount" class="form-label">Funding Goal ($) *</label>
                                <input type="number"
                                       class="form-control"
                                       id="goal_amount"
                                       name="goal_amount"
                                       value="<?php echo isset($_POST['goal_amount']) ? htmlspecialchars($_POST['goal_amount']) : ''; ?>"
                                       required
                                       min="1"
                                       step="0.01">
                                <div class="invalid-feedback">
                                    Please enter a valid funding goal (minimum $1).
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="min_donation" class="form-label">Minimum Donation ($)</label>
                                <input type="number"
                                       class="form-control"
                                       id="min_donation"
                                       name="min_donation"
                                       value="<?php echo isset($_POST['min_donation']) ? htmlspecialchars($_POST['min_donation']) : '1.00'; ?>"
                                       min="0.01"
                                       step="0.01">
                                <div class="form-text">Minimum amount backers can contribute.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label">Campaign End Date</label>
                            <input type="date"
                                   class="form-control"
                                   id="end_date"
                                   name="end_date"
                                   value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            <div class="form-text">Leave empty for no deadline. Recommended: 30-60 days.</div>
                        </div>

                        <!-- Media -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">Media & Content</h4>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <input type="file"
                                   class="form-control"
                                   id="featured_image"
                                   name="featured_image"
                                   accept="image/*">
                            <div class="form-text">Upload a compelling image for your project (JPG, PNG, GIF, WebP - max 5MB).</div>
                        </div>

                        <div class="mb-3">
                            <label for="video_url" class="form-label">Video URL</label>
                            <input type="url"
                                   class="form-control"
                                   id="video_url"
                                   name="video_url"
                                   value="<?php echo isset($_POST['video_url']) ? htmlspecialchars($_POST['video_url']) : ''; ?>"
                                   placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">
                            <div class="form-text">Optional: Add a YouTube or Vimeo video to showcase your project.</div>
                        </div>

                        <div class="mb-4">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text"
                                   class="form-control"
                                   id="tags"
                                   name="tags"
                                   value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>"
                                   placeholder="technology, innovation, startup">
                            <div class="form-text">Separate tags with commas. Help people discover your project!</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo app_url('public/index.php?action=dashboard'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-rocket me-2"></i>Launch Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation and enhancements
(function() {
    'use strict';

    // Bootstrap form validation
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);

    // Character counter for short description
    const shortDesc = document.getElementById('short_description');
    if (shortDesc) {
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.id = 'short-desc-counter';
        shortDesc.parentNode.appendChild(counter);

        function updateCounter() {
            const remaining = 500 - shortDesc.value.length;
            counter.textContent = remaining + ' characters remaining';
            counter.className = remaining < 50 ? 'form-text text-end text-warning' : 'form-text text-end';
        }

        shortDesc.addEventListener('input', updateCounter);
        updateCounter();
    }

    // Auto-suggest end date (30 days from now)
    const endDateInput = document.getElementById('end_date');
    if (endDateInput && !endDateInput.value) {
        const suggestedDate = new Date();
        suggestedDate.setDate(suggestedDate.getDate() + 30);
        endDateInput.placeholder = 'Suggested: ' + suggestedDate.toISOString().split('T')[0];
    }
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>