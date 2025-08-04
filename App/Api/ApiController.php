<?php

require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Services/PaymentService.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Utils/Logger.php';
require_once __DIR__ . '/../Middleware/SecurityMiddleware.php';

class ApiController {
    private $db;
    private $authService;
    private $paymentService;
    private $projectModel;
    private $userModel;
    private $apiKey;
    private $userId;
    
    public function __construct($db) {
        $this->db = $db;
        $this->authService = new AuthService($db);
        $this->paymentService = new PaymentService($db);
        $this->projectModel = new Project($db);
        $this->userModel = new User($db);
        
        // Set CORS headers
        $this->setCorsHeaders();
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Authenticate API request
        $this->authenticateRequest();
    }
    
    /**
     * Route API requests
     */
    public function route() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        
        // Remove 'api' and 'v1' from path
        array_shift($pathParts); // Remove 'api'
        $version = array_shift($pathParts); // Remove version
        
        if ($version !== 'v1') {
            return $this->errorResponse('Unsupported API version', 400);
        }
        
        $resource = $pathParts[0] ?? '';
        $id = $pathParts[1] ?? null;
        $action = $pathParts[2] ?? null;
        
        try {
            switch ($resource) {
                case 'projects':
                    return $this->handleProjectsEndpoint($method, $id, $action);
                case 'donations':
                    return $this->handleDonationsEndpoint($method, $id, $action);
                case 'users':
                    return $this->handleUsersEndpoint($method, $id, $action);
                case 'categories':
                    return $this->handleCategoriesEndpoint($method, $id);
                case 'analytics':
                    return $this->handleAnalyticsEndpoint($method, $id);
                case 'webhooks':
                    return $this->handleWebhooksEndpoint($method, $id);
                default:
                    return $this->errorResponse('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            Logger::error('API error', ['error' => $e->getMessage(), 'endpoint' => $resource]);
            return $this->errorResponse('Internal server error', 500);
        }
    }
    
    /**
     * Handle projects endpoint
     */
    private function handleProjectsEndpoint($method, $id, $action) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    if ($action === 'donations') {
                        return $this->getProjectDonations($id);
                    }
                    return $this->getProject($id);
                }
                return $this->getProjects();
                
            case 'POST':
                if ($id && $action === 'donate') {
                    return $this->createDonation($id);
                }
                return $this->createProject();
                
            case 'PUT':
                if ($id) {
                    return $this->updateProject($id);
                }
                return $this->errorResponse('Project ID required', 400);
                
            case 'DELETE':
                if ($id) {
                    return $this->deleteProject($id);
                }
                return $this->errorResponse('Project ID required', 400);
                
            default:
                return $this->errorResponse('Method not allowed', 405);
        }
    }
    
    /**
     * Handle donations endpoint
     */
    private function handleDonationsEndpoint($method, $id, $action) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->getDonation($id);
                }
                return $this->getDonations();
                
            case 'POST':
                return $this->createDonation();
                
            case 'PUT':
                if ($id && $action === 'refund') {
                    return $this->refundDonation($id);
                }
                return $this->errorResponse('Invalid donation action', 400);
                
            default:
                return $this->errorResponse('Method not allowed', 405);
        }
    }
    
    /**
     * Handle users endpoint
     */
    private function handleUsersEndpoint($method, $id, $action) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    if ($action === 'projects') {
                        return $this->getUserProjects($id);
                    }
                    if ($action === 'donations') {
                        return $this->getUserDonations($id);
                    }
                    return $this->getUser($id);
                }
                return $this->getUsers();
                
            case 'PUT':
                if ($id) {
                    return $this->updateUser($id);
                }
                return $this->errorResponse('User ID required', 400);
                
            default:
                return $this->errorResponse('Method not allowed', 405);
        }
    }
    
    /**
     * Get projects with pagination and filters
     */
    private function getProjects() {
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        $offset = ($page - 1) * $limit;
        
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? 'active';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';
        
        $projects = $this->projectModel->getProjectsWithFilters([
            'category' => $category,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $total = $this->projectModel->getProjectsCount([
            'category' => $category,
            'status' => $status,
            'search' => $search
        ]);
        
        return $this->successResponse([
            'projects' => $projects,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    /**
     * Get single project
     */
    private function getProject($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }
        
        // Add additional project data
        $project['donations_count'] = $this->projectModel->getDonationsCount($id);
        $project['recent_donations'] = $this->projectModel->getRecentDonations($id, 5);
        
        return $this->successResponse(['project' => $project]);
    }
    
    /**
     * Create new project
     */
    private function createProject() {
        $input = $this->getJsonInput();
        
        // Validate required fields
        $required = ['title', 'description', 'goal_amount'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return $this->errorResponse("Field '$field' is required", 400);
            }
        }
        
        // Check if user can create projects
        $subscriptionService = new SubscriptionService($this->db);
        if (!$subscriptionService->canCreateProject($this->userId)) {
            return $this->errorResponse('Project limit reached for your subscription plan', 403);
        }
        
        $projectData = [
            'title' => SecurityMiddleware::sanitizeInput($input['title']),
            'description' => SecurityMiddleware::sanitizeInput($input['description']),
            'short_description' => SecurityMiddleware::sanitizeInput($input['short_description'] ?? ''),
            'goal_amount' => (float)$input['goal_amount'],
            'user_id' => $this->userId,
            'category_id' => (int)($input['category_id'] ?? null),
            'end_date' => $input['end_date'] ?? null,
            'min_donation' => (float)($input['min_donation'] ?? 1.00),
            'status' => 'active'
        ];
        
        $projectId = $this->projectModel->addProject($projectData);
        
        if ($projectId) {
            Logger::info('Project created via API', ['project_id' => $projectId, 'user_id' => $this->userId]);
            return $this->successResponse(['project_id' => $projectId], 201);
        }
        
        return $this->errorResponse('Failed to create project', 500);
    }
    
    /**
     * Create donation
     */
    private function createDonation($projectId = null) {
        $input = $this->getJsonInput();
        
        $projectId = $projectId ?? $input['project_id'] ?? null;
        $amount = $input['amount'] ?? null;
        
        if (!$projectId || !$amount) {
            return $this->errorResponse('Project ID and amount are required', 400);
        }
        
        if ($amount < 1) {
            return $this->errorResponse('Minimum donation amount is $1', 400);
        }
        
        // Get project details
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }
        
        $donationData = [
            'amount' => (float)$amount,
            'project_id' => (int)$projectId,
            'user_id' => $this->userId,
            'project_title' => $project['title'],
            'currency' => $input['currency'] ?? 'usd',
            'anonymous' => $input['anonymous'] ?? false,
            'message' => SecurityMiddleware::sanitizeInput($input['message'] ?? '')
        ];
        
        $result = $this->paymentService->processDonation($donationData);
        
        if ($result['success']) {
            Logger::info('Donation created via API', [
                'donation_id' => $result['donation_id'],
                'amount' => $amount,
                'project_id' => $projectId,
                'user_id' => $this->userId
            ]);
            
            return $this->successResponse([
                'donation_id' => $result['donation_id'],
                'client_secret' => $result['client_secret'],
                'payment_intent_id' => $result['payment_intent_id']
            ], 201);
        }
        
        return $this->errorResponse($result['errors'][0] ?? 'Donation failed', 400);
    }
    
    /**
     * Get user's projects
     */
    private function getUserProjects($userId) {
        // Check if user can access this data
        if ($this->userId != $userId && !$this->isAdmin()) {
            return $this->errorResponse('Access denied', 403);
        }
        
        $projects = $this->projectModel->getProjectsByUser($userId);
        return $this->successResponse(['projects' => $projects]);
    }
    
    /**
     * Handle webhooks
     */
    private function handleWebhooksEndpoint($method, $provider) {
        if ($method !== 'POST') {
            return $this->errorResponse('Method not allowed', 405);
        }
        
        switch ($provider) {
            case 'stripe':
                return $this->handleStripeWebhook();
            default:
                return $this->errorResponse('Unsupported webhook provider', 400);
        }
    }
    
    /**
     * Handle Stripe webhook
     */
    private function handleStripeWebhook() {
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        
        $result = $this->paymentService->handleWebhook($payload, $signature);
        
        if ($result['success']) {
            return $this->successResponse(['received' => true]);
        }
        
        return $this->errorResponse('Webhook processing failed', 400);
    }
    
    /**
     * Authenticate API request
     */
    private function authenticateRequest() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            $apiKey = $this->validateApiKey($token);
            
            if ($apiKey) {
                $this->apiKey = $apiKey;
                $this->userId = $apiKey['user_id'];
                return;
            }
        }
        
        // Check for API key in query parameter (less secure, for testing)
        if (isset($_GET['api_key'])) {
            $apiKey = $this->validateApiKey($_GET['api_key']);
            if ($apiKey) {
                $this->apiKey = $apiKey;
                $this->userId = $apiKey['user_id'];
                return;
            }
        }
        
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'message' => 'Valid API key required']);
        exit;
    }
    
    /**
     * Validate API key
     */
    private function validateApiKey($key) {
        $stmt = $this->db->prepare("
            SELECT ak.*, u.status as user_status, u.is_admin
            FROM api_keys ak
            JOIN users u ON ak.user_id = u.id
            WHERE ak.key_hash = :key_hash 
            AND ak.is_active = 1 
            AND (ak.expires_at IS NULL OR ak.expires_at > NOW())
            AND u.status = 'active'
        ");
        
        $stmt->execute(['key_hash' => hash('sha256', $key)]);
        $apiKey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($apiKey) {
            // Update last used timestamp
            $this->updateApiKeyUsage($apiKey['id']);
            return $apiKey;
        }
        
        return false;
    }
    
    /**
     * Update API key usage
     */
    private function updateApiKeyUsage($apiKeyId) {
        $stmt = $this->db->prepare("
            UPDATE api_keys 
            SET last_used_at = NOW(), usage_count = usage_count + 1 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $apiKeyId]);
    }
    
    /**
     * Set CORS headers
     */
    private function setCorsHeaders() {
        $allowedOrigins = explode(',', $_ENV['API_ALLOWED_ORIGINS'] ?? '*');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    
    /**
     * Get JSON input
     */
    private function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Success response
     */
    private function successResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    /**
     * Error response
     */
    private function errorResponse($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    /**
     * Check if current user is admin
     */
    private function isAdmin() {
        return $this->apiKey['is_admin'] ?? false;
    }
}
