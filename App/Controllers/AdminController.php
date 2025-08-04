<?php

require_once __DIR__ . '/../Services/AnalyticsService.php';
require_once __DIR__ . '/../Services/SubscriptionService.php';
require_once __DIR__ . '/../Services/PaymentService.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Utils/Logger.php';

class AdminController {
    private $db;
    private $analyticsService;
    private $subscriptionService;
    private $paymentService;
    private $userModel;
    private $projectModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->analyticsService = new AnalyticsService($db);
        $this->subscriptionService = new SubscriptionService($db);
        $this->paymentService = new PaymentService($db);
        $this->userModel = new User($db);
        $this->projectModel = new Project($db);
        
        // Check admin access
        $this->checkAdminAccess();
    }
    
    /**
     * Admin dashboard overview
     */
    public function dashboard() {
        try {
            $data = [
                'stats' => $this->getDashboardStats(),
                'recent_activity' => $this->getRecentActivity(),
                'revenue_chart' => $this->analyticsService->getRevenueChart(30),
                'user_growth' => $this->analyticsService->getUserGrowthChart(30),
                'top_projects' => $this->getTopProjects(),
                'alerts' => $this->getSystemAlerts()
            ];
            
            $title = 'Admin Dashboard';
            $content = $this->renderView('admin/dashboard', $data);
            require_once __DIR__ . '/../Views/admin/layout.php';
            
        } catch (Exception $e) {
            Logger::error('Admin dashboard error', ['error' => $e->getMessage()]);
            $this->showError('Dashboard unavailable');
        }
    }
    
    /**
     * User management
     */
    public function users() {
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        try {
            $users = $this->getUsersWithFilters($search, $status, $limit, $offset);
            $totalUsers = $this->getTotalUsersCount($search, $status);
            $totalPages = ceil($totalUsers / $limit);
            
            $data = [
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_users' => $totalUsers
                ],
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ]
            ];
            
            $title = 'User Management';
            $content = $this->renderView('admin/users', $data);
            require_once __DIR__ . '/../Views/admin/layout.php';
            
        } catch (Exception $e) {
            Logger::error('Admin users page error', ['error' => $e->getMessage()]);
            $this->showError('User management unavailable');
        }
    }
    
    /**
     * Project management
     */
    public function projects() {
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        try {
            $projects = $this->getProjectsWithFilters($search, $status, $category, $limit, $offset);
            $totalProjects = $this->getTotalProjectsCount($search, $status, $category);
            $totalPages = ceil($totalProjects / $limit);
            
            $data = [
                'projects' => $projects,
                'categories' => $this->getCategories(),
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_projects' => $totalProjects
                ],
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'category' => $category
                ]
            ];
            
            $title = 'Project Management';
            $content = $this->renderView('admin/projects', $data);
            require_once __DIR__ . '/../Views/admin/layout.php';
            
        } catch (Exception $e) {
            Logger::error('Admin projects page error', ['error' => $e->getMessage()]);
            $this->showError('Project management unavailable');
        }
    }
    
    /**
     * Financial overview
     */
    public function finances() {
        try {
            $period = $_GET['period'] ?? '30';
            $data = [
                'revenue_stats' => $this->analyticsService->getRevenueStats($period),
                'commission_stats' => $this->analyticsService->getCommissionStats($period),
                'payment_methods' => $this->analyticsService->getPaymentMethodStats($period),
                'refund_stats' => $this->analyticsService->getRefundStats($period),
                'subscription_revenue' => $this->analyticsService->getSubscriptionRevenue($period),
                'top_earners' => $this->getTopEarningProjects($period),
                'pending_payouts' => $this->getPendingPayouts()
            ];
            
            $title = 'Financial Overview';
            $content = $this->renderView('admin/finances', $data);
            require_once __DIR__ . '/../Views/admin/layout.php';
            
        } catch (Exception $e) {
            Logger::error('Admin finances page error', ['error' => $e->getMessage()]);
            $this->showError('Financial overview unavailable');
        }
    }
    
    /**
     * Analytics and reports
     */
    public function analytics() {
        try {
            $period = $_GET['period'] ?? '30';
            $data = [
                'overview_stats' => $this->analyticsService->getOverviewStats($period),
                'user_analytics' => $this->analyticsService->getUserAnalytics($period),
                'project_analytics' => $this->analyticsService->getProjectAnalytics($period),
                'conversion_funnel' => $this->analyticsService->getConversionFunnel($period),
                'geographic_data' => $this->analyticsService->getGeographicData($period),
                'device_stats' => $this->analyticsService->getDeviceStats($period)
            ];
            
            $title = 'Analytics & Reports';
            $content = $this->renderView('admin/analytics', $data);
            require_once __DIR__ . '/../Views/admin/layout.php';
            
        } catch (Exception $e) {
            Logger::error('Admin analytics page error', ['error' => $e->getMessage()]);
            $this->showError('Analytics unavailable');
        }
    }
    
    /**
     * System settings
     */
    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateSettings();
        }
        
        try {
            $data = [
                'settings' => $this->getSystemSettings(),
                'subscription_plans' => $this->subscriptionService->getSubscriptionPlans(),
                'categories' => $this->getCategories(),
                'email_templates' => $this->getEmailTemplates()
            ];
            
            $title = 'System Settings';
            $content = $this->renderView('admin/settings', $data);
            require_once __DIR__ . '/../Views/admin/layout.php';
            
        } catch (Exception $e) {
            Logger::error('Admin settings page error', ['error' => $e->getMessage()]);
            $this->showError('Settings unavailable');
        }
    }
    
    /**
     * User actions
     */
    public function userAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $action = $_POST['action'] ?? '';
        $userId = (int)($_POST['user_id'] ?? 0);
        
        try {
            switch ($action) {
                case 'suspend':
                    $result = $this->suspendUser($userId);
                    break;
                case 'activate':
                    $result = $this->activateUser($userId);
                    break;
                case 'verify':
                    $result = $this->verifyUser($userId);
                    break;
                case 'make_admin':
                    $result = $this->makeAdmin($userId);
                    break;
                case 'remove_admin':
                    $result = $this->removeAdmin($userId);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
            
            header('Content-Type: application/json');
            echo json_encode($result);
            
        } catch (Exception $e) {
            Logger::error('Admin user action error', ['error' => $e->getMessage()]);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Action failed']);
        }
    }
    
    /**
     * Project actions
     */
    public function projectAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $action = $_POST['action'] ?? '';
        $projectId = (int)($_POST['project_id'] ?? 0);
        
        try {
            switch ($action) {
                case 'approve':
                    $result = $this->approveProject($projectId);
                    break;
                case 'reject':
                    $result = $this->rejectProject($projectId);
                    break;
                case 'feature':
                    $result = $this->featureProject($projectId);
                    break;
                case 'unfeature':
                    $result = $this->unfeatureProject($projectId);
                    break;
                case 'suspend':
                    $result = $this->suspendProject($projectId);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
            
            header('Content-Type: application/json');
            echo json_encode($result);
            
        } catch (Exception $e) {
            Logger::error('Admin project action error', ['error' => $e->getMessage()]);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Action failed']);
        }
    }
    
    /**
     * Export data
     */
    public function export() {
        $type = $_GET['type'] ?? '';
        $format = $_GET['format'] ?? 'csv';
        $period = $_GET['period'] ?? '30';
        
        try {
            switch ($type) {
                case 'users':
                    $data = $this->exportUsers($period);
                    $filename = 'users_export_' . date('Y-m-d');
                    break;
                case 'projects':
                    $data = $this->exportProjects($period);
                    $filename = 'projects_export_' . date('Y-m-d');
                    break;
                case 'donations':
                    $data = $this->exportDonations($period);
                    $filename = 'donations_export_' . date('Y-m-d');
                    break;
                case 'revenue':
                    $data = $this->exportRevenue($period);
                    $filename = 'revenue_export_' . date('Y-m-d');
                    break;
                default:
                    throw new Exception('Invalid export type');
            }
            
            if ($format === 'csv') {
                $this->exportCSV($data, $filename);
            } else {
                $this->exportJSON($data, $filename);
            }
            
        } catch (Exception $e) {
            Logger::error('Admin export error', ['error' => $e->getMessage()]);
            $this->showError('Export failed');
        }
    }
    
    // Helper methods
    private function checkAdminAccess() {
        if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
            header('Location: ' . app_url('public/index.php?action=login'));
            exit;
        }
    }
    
    private function getDashboardStats() {
        $stmt = $this->db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_users_30d,
                (SELECT COUNT(*) FROM projects WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_projects_30d,
                (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as revenue_30d,
                (SELECT COUNT(*) FROM donations WHERE payment_status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as donations_30d,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM projects) as total_projects,
                (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = 'completed') as total_revenue,
                (SELECT COUNT(*) FROM user_subscriptions WHERE status = 'active') as active_subscriptions
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getRecentActivity() {
        $stmt = $this->db->prepare("
            (SELECT 'user_registered' as type, u.name as title, u.created_at as timestamp, u.id as entity_id
             FROM users u ORDER BY u.created_at DESC LIMIT 5)
            UNION ALL
            (SELECT 'project_created' as type, p.title as title, p.created_at as timestamp, p.id as entity_id
             FROM projects p ORDER BY p.created_at DESC LIMIT 5)
            UNION ALL
            (SELECT 'donation_made' as type, CONCAT('$', d.amount, ' to ', p.title) as title, d.created_at as timestamp, d.id as entity_id
             FROM donations d JOIN projects p ON d.project_id = p.id 
             WHERE d.payment_status = 'completed' ORDER BY d.created_at DESC LIMIT 5)
            ORDER BY timestamp DESC LIMIT 15
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getTopProjects() {
        $stmt = $this->db->prepare("
            SELECT p.id, p.title, p.current_amount, p.goal_amount, u.name as creator_name,
                   (p.current_amount / p.goal_amount * 100) as funding_percentage
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'active'
            ORDER BY p.current_amount DESC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getSystemAlerts() {
        $alerts = [];
        
        // Check for failed payments
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM user_subscriptions 
            WHERE status = 'past_due' AND updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
        $failedPayments = $stmt->fetchColumn();
        
        if ($failedPayments > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "$failedPayments subscription payments failed in the last 24 hours",
                'action_url' => '?action=admin_finances'
            ];
        }
        
        // Check for pending project approvals
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE status = 'pending_approval'");
        $stmt->execute();
        $pendingProjects = $stmt->fetchColumn();
        
        if ($pendingProjects > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "$pendingProjects projects pending approval",
                'action_url' => '?action=admin_projects&status=pending_approval'
            ];
        }
        
        return $alerts;
    }
    
    private function renderView($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/$view.php";
        return ob_get_clean();
    }
    
    private function showError($message) {
        $title = 'Error';
        $content = "<div class='alert alert-danger'>$message</div>";
        require_once __DIR__ . '/../Views/admin/layout.php';
    }
    
    private function getUsersWithFilters($search, $status, $limit, $offset) {
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(name LIKE :search OR email LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        if ($status) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM projects WHERE user_id = u.id) as project_count,
                   (SELECT COALESCE(SUM(amount), 0) FROM donations d JOIN projects p ON d.project_id = p.id WHERE p.user_id = u.id AND d.payment_status = 'completed') as total_raised
            FROM users u 
            $whereClause
            ORDER BY u.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function suspendUser($userId) {
        $stmt = $this->db->prepare("UPDATE users SET status = 'suspended' WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        
        Logger::logAdminAction('user_suspended', $_SESSION['user']['id'], ['target_user_id' => $userId]);
        
        return ['success' => true, 'message' => 'User suspended successfully'];
    }
    
    private function activateUser($userId) {
        $stmt = $this->db->prepare("UPDATE users SET status = 'active' WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        
        Logger::logAdminAction('user_activated', $_SESSION['user']['id'], ['target_user_id' => $userId]);
        
        return ['success' => true, 'message' => 'User activated successfully'];
    }
    
    private function exportCSV($data, $filename) {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename.csv\"");
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
    }
}
