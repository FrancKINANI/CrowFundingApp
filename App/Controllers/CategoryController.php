<?php

require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Utils/Logger.php';

/**
 * Category Controller
 * 
 * Handles category-related operations and project browsing by category
 */
class CategoryController {
    private $categoryModel;
    private $projectModel;

    public function __construct($db) {
        $this->categoryModel = new Category($db);
        $this->projectModel = new Project($db);
    }

    /**
     * Display all categories
     */
    public function index() {
        $categories = $this->categoryModel->getCategoriesWithProjectCounts();
        require_once __DIR__ . '/../Views/categories/index.php';
    }

    /**
     * Display projects in a specific category
     */
    public function show() {
        if (!isset($_GET['id'])) {
            header('Location: ' . app_url('public/index.php?action=categories'));
            exit;
        }

        $categoryId = (int)$_GET['id'];
        $category = $this->categoryModel->getCategoryById($categoryId);
        
        if (!$category) {
            header('Location: ' . app_url('public/index.php?action=categories'));
            exit;
        }

        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = PROJECTS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        // Get projects in this category
        $projects = $this->projectModel->getProjectsByCategory($categoryId, $limit, $offset);
        
        // Get total count for pagination
        $totalProjects = $this->getTotalProjectsInCategory($categoryId);
        $totalPages = ceil($totalProjects / $limit);

        require_once __DIR__ . '/../Views/categories/show.php';
    }

    /**
     * Get popular categories for homepage
     */
    public function getPopular() {
        return $this->categoryModel->getPopularCategories(6);
    }

    /**
     * Get total projects count in category
     * 
     * @param int $categoryId
     * @return int
     */
    private function getTotalProjectsInCategory($categoryId) {
        $filters = ['category_id' => $categoryId];
        $projects = $this->projectModel->getAllProjects($filters);
        return count($projects);
    }

    /**
     * Admin: Create new category
     */
    public function create() {
        if (!$this->isAdmin()) {
            header('Location: ' . app_url('public/index.php?action=home'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF validation
            if (!SecurityMiddleware::validateCSRF($_POST)) {
                $error = "Invalid security token. Please try again.";
                require_once __DIR__ . '/../Views/admin/categories/create.php';
                return;
            }

            // Input validation
            $validator = Validator::make($_POST)
                ->required('name', 'Category name is required')
                ->min('name', 2, 'Name must be at least 2 characters')
                ->max('name', 100, 'Name must not exceed 100 characters')
                ->required('description', 'Description is required')
                ->required('icon', 'Icon is required')
                ->pattern('color', '/^#[0-9A-Fa-f]{6}$/', 'Color must be a valid hex color');

            if ($validator->fails()) {
                $errors = $validator->getAllErrors();
                $error = implode('<br>', $errors);
                require_once __DIR__ . '/../Views/admin/categories/create.php';
                return;
            }

            $name = SecurityMiddleware::sanitizeInput($_POST['name']);
            $description = SecurityMiddleware::sanitizeInput($_POST['description']);
            $icon = SecurityMiddleware::sanitizeInput($_POST['icon']);
            $color = $_POST['color'];

            if ($this->categoryModel->addCategory($name, $description, $icon, $color)) {
                Logger::logUserActivity('category_created', $_SESSION['user']['id'], ['name' => $name]);
                header('Location: ' . app_url('public/index.php?action=admin_categories'));
                exit;
            } else {
                $error = "Failed to create category. Please try again.";
            }
        }

        require_once __DIR__ . '/../Views/admin/categories/create.php';
    }

    /**
     * Admin: List all categories
     */
    public function adminIndex() {
        if (!$this->isAdmin()) {
            header('Location: ' . app_url('public/index.php?action=home'));
            exit;
        }

        $categories = $this->categoryModel->getCategoriesWithProjectCounts();
        require_once __DIR__ . '/../Views/admin/categories/index.php';
    }

    /**
     * Admin: Edit category
     */
    public function edit() {
        if (!$this->isAdmin()) {
            header('Location: ' . app_url('public/index.php?action=home'));
            exit;
        }

        if (!isset($_GET['id'])) {
            header('Location: ' . app_url('public/index.php?action=admin_categories'));
            exit;
        }

        $categoryId = (int)$_GET['id'];
        $category = $this->categoryModel->getCategoryById($categoryId);
        
        if (!$category) {
            header('Location: ' . app_url('public/index.php?action=admin_categories'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF validation
            if (!SecurityMiddleware::validateCSRF($_POST)) {
                $error = "Invalid security token. Please try again.";
                require_once __DIR__ . '/../Views/admin/categories/edit.php';
                return;
            }

            // Input validation
            $validator = Validator::make($_POST)
                ->required('name', 'Category name is required')
                ->min('name', 2, 'Name must be at least 2 characters')
                ->max('name', 100, 'Name must not exceed 100 characters')
                ->required('description', 'Description is required')
                ->required('icon', 'Icon is required')
                ->pattern('color', '/^#[0-9A-Fa-f]{6}$/', 'Color must be a valid hex color');

            if ($validator->fails()) {
                $errors = $validator->getAllErrors();
                $error = implode('<br>', $errors);
                require_once __DIR__ . '/../Views/admin/categories/edit.php';
                return;
            }

            $name = SecurityMiddleware::sanitizeInput($_POST['name']);
            $description = SecurityMiddleware::sanitizeInput($_POST['description']);
            $icon = SecurityMiddleware::sanitizeInput($_POST['icon']);
            $color = $_POST['color'];

            if ($this->categoryModel->updateCategory($categoryId, $name, $description, $icon, $color)) {
                Logger::logUserActivity('category_updated', $_SESSION['user']['id'], ['category_id' => $categoryId]);
                header('Location: ' . app_url('public/index.php?action=admin_categories'));
                exit;
            } else {
                $error = "Failed to update category. Please try again.";
            }
        }

        require_once __DIR__ . '/../Views/admin/categories/edit.php';
    }

    /**
     * Admin: Toggle category status
     */
    public function toggleStatus() {
        if (!$this->isAdmin()) {
            header('Location: ' . app_url('public/index.php?action=home'));
            exit;
        }

        if (!isset($_GET['id'])) {
            header('Location: ' . app_url('public/index.php?action=admin_categories'));
            exit;
        }

        $categoryId = (int)$_GET['id'];
        
        if ($this->categoryModel->toggleCategoryStatus($categoryId)) {
            Logger::logUserActivity('category_status_toggled', $_SESSION['user']['id'], ['category_id' => $categoryId]);
        }

        header('Location: ' . app_url('public/index.php?action=admin_categories'));
        exit;
    }

    /**
     * Admin: Delete category
     */
    public function delete() {
        if (!$this->isAdmin()) {
            header('Location: ' . app_url('public/index.php?action=home'));
            exit;
        }

        if (!isset($_GET['id'])) {
            header('Location: ' . app_url('public/index.php?action=admin_categories'));
            exit;
        }

        $categoryId = (int)$_GET['id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF validation
            if (!SecurityMiddleware::validateCSRF($_POST)) {
                header('Location: ' . app_url('public/index.php?action=admin_categories'));
                exit;
            }

            if ($this->categoryModel->deleteCategory($categoryId)) {
                Logger::logUserActivity('category_deleted', $_SESSION['user']['id'], ['category_id' => $categoryId]);
            }

            header('Location: ' . app_url('public/index.php?action=admin_categories'));
            exit;
        }

        $category = $this->categoryModel->getCategoryById($categoryId);
        require_once __DIR__ . '/../Views/admin/categories/delete.php';
    }

    /**
     * Check if current user is admin
     * 
     * @return bool
     */
    private function isAdmin() {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['is_admin']);
    }
}
