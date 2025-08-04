<?php

require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Utils/Logger.php';

/**
 * Search Controller
 * 
 * Handles project search and filtering functionality
 */
class SearchController {
    private $projectModel;
    private $categoryModel;

    public function __construct($db) {
        $this->projectModel = new Project($db);
        $this->categoryModel = new Category($db);
    }

    /**
     * Main search functionality
     */
    public function search() {
        $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        $limit = PROJECTS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        // Build filters
        $filters = [];
        
        if (!empty($searchTerm)) {
            $filters['search'] = $searchTerm;
        }
        
        if ($categoryId) {
            $filters['category_id'] = $categoryId;
        }

        // Get projects
        $projects = $this->projectModel->getAllProjects($filters, $limit, $offset);
        
        // Apply sorting
        $projects = $this->sortProjects($projects, $sortBy);
        
        // Get total count for pagination
        $totalProjects = count($this->projectModel->getAllProjects($filters));
        $totalPages = ceil($totalProjects / $limit);

        // Get categories for filter dropdown
        $categories = $this->categoryModel->getAllCategories();

        // Log search if there's a search term
        if (!empty($searchTerm)) {
            Logger::info('Search performed', [
                'term' => $searchTerm,
                'category' => $categoryId,
                'results' => count($projects),
                'user_id' => $_SESSION['user']['id'] ?? null
            ]);
        }

        require_once __DIR__ . '/../Views/search/results.php';
    }

    /**
     * Advanced search page
     */
    public function advanced() {
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../Views/search/advanced.php';
    }

    /**
     * AJAX search suggestions
     */
    public function suggestions() {
        if (!isset($_GET['q']) || strlen($_GET['q']) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $searchTerm = trim($_GET['q']);
        $limit = 10;

        // Search in project titles and descriptions
        $query = "SELECT id, title, short_description, featured_image 
                  FROM projects 
                  WHERE status = 'active' 
                  AND (title LIKE :search OR short_description LIKE :search)
                  ORDER BY 
                    CASE WHEN title LIKE :exact_search THEN 1 ELSE 2 END,
                    created_at DESC
                  LIMIT :limit";

        $stmt = $this->projectModel->db->prepare($query);
        $searchPattern = '%' . $searchTerm . '%';
        $exactSearchPattern = $searchTerm . '%';
        
        $stmt->bindParam(':search', $searchPattern);
        $stmt->bindParam(':exact_search', $exactSearchPattern);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format suggestions for frontend
        $formattedSuggestions = array_map(function($project) {
            return [
                'id' => $project['id'],
                'title' => $project['title'],
                'description' => substr($project['short_description'], 0, 100) . '...',
                'image' => $project['featured_image'] ? app_url('uploads/projects/' . $project['featured_image']) : null,
                'url' => app_url('public/index.php?action=projectDetails&id=' . $project['id'])
            ];
        }, $suggestions);

        header('Content-Type: application/json');
        echo json_encode($formattedSuggestions);
        exit;
    }

    /**
     * Filter projects by various criteria
     */
    public function filter() {
        $filters = [];
        
        // Category filter
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $filters['category_id'] = (int)$_GET['category'];
        }
        
        // Status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $validStatuses = ['active', 'funded', 'expired'];
            if (in_array($_GET['status'], $validStatuses)) {
                $filters['status'] = $_GET['status'];
            }
        }
        
        // Featured filter
        if (isset($_GET['featured']) && $_GET['featured'] === '1') {
            $filters['featured'] = true;
        }

        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = PROJECTS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        // Get projects
        $projects = $this->projectModel->getAllProjects($filters, $limit, $offset);
        
        // Sort projects
        $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        $projects = $this->sortProjects($projects, $sortBy);
        
        // Get total count
        $totalProjects = count($this->projectModel->getAllProjects($filters));
        $totalPages = ceil($totalProjects / $limit);

        // Get categories for filter
        $categories = $this->categoryModel->getAllCategories();

        require_once __DIR__ . '/../Views/search/filter.php';
    }

    /**
     * Sort projects by different criteria
     * 
     * @param array $projects
     * @param string $sortBy
     * @return array
     */
    private function sortProjects($projects, $sortBy) {
        switch ($sortBy) {
            case 'oldest':
                usort($projects, function($a, $b) {
                    return strtotime($a['created_at']) - strtotime($b['created_at']);
                });
                break;
                
            case 'goal_high':
                usort($projects, function($a, $b) {
                    return $b['goal_amount'] - $a['goal_amount'];
                });
                break;
                
            case 'goal_low':
                usort($projects, function($a, $b) {
                    return $a['goal_amount'] - $b['goal_amount'];
                });
                break;
                
            case 'funded_high':
                usort($projects, function($a, $b) {
                    $aPercent = $a['goal_amount'] > 0 ? ($a['current_amount'] / $a['goal_amount']) * 100 : 0;
                    $bPercent = $b['goal_amount'] > 0 ? ($b['current_amount'] / $b['goal_amount']) * 100 : 0;
                    return $bPercent - $aPercent;
                });
                break;
                
            case 'funded_low':
                usort($projects, function($a, $b) {
                    $aPercent = $a['goal_amount'] > 0 ? ($a['current_amount'] / $a['goal_amount']) * 100 : 0;
                    $bPercent = $b['goal_amount'] > 0 ? ($b['current_amount'] / $b['goal_amount']) * 100 : 0;
                    return $aPercent - $bPercent;
                });
                break;
                
            case 'backers':
                usort($projects, function($a, $b) {
                    return $b['backer_count'] - $a['backer_count'];
                });
                break;
                
            case 'ending_soon':
                usort($projects, function($a, $b) {
                    if (!$a['days_remaining'] && !$b['days_remaining']) return 0;
                    if (!$a['days_remaining']) return 1;
                    if (!$b['days_remaining']) return -1;
                    return $a['days_remaining'] - $b['days_remaining'];
                });
                break;
                
            case 'newest':
            default:
                usort($projects, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                break;
        }
        
        return $projects;
    }

    /**
     * Get trending/popular projects
     */
    public function trending() {
        // Get projects with high activity in the last 30 days
        $query = "SELECT p.*, u.name as creator_name, c.name as category_name, c.color as category_color,
                         COUNT(d.id) as recent_donations,
                         SUM(d.amount) as recent_amount,
                         (SELECT COUNT(*) FROM donations d2 WHERE d2.project_id = p.id AND d2.payment_status = 'completed') as total_backers
                  FROM projects p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN donations d ON p.id = d.project_id 
                      AND d.payment_status = 'completed' 
                      AND d.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  WHERE p.status = 'active'
                  GROUP BY p.id
                  HAVING recent_donations > 0
                  ORDER BY recent_donations DESC, recent_amount DESC
                  LIMIT 20";

        $stmt = $this->projectModel->db->query($query);
        $trendingProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/search/trending.php';
    }

    /**
     * Get projects ending soon
     */
    public function endingSoon() {
        $query = "SELECT p.*, u.name as creator_name, c.name as category_name, c.color as category_color,
                         (SELECT COUNT(*) FROM donations d WHERE d.project_id = p.id AND d.payment_status = 'completed') as backer_count,
                         DATEDIFF(p.end_date, CURDATE()) as days_remaining
                  FROM projects p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.status = 'active' 
                  AND p.end_date IS NOT NULL 
                  AND p.end_date > CURDATE()
                  AND DATEDIFF(p.end_date, CURDATE()) <= 7
                  ORDER BY days_remaining ASC
                  LIMIT 20";

        $stmt = $this->projectModel->db->query($query);
        $endingSoonProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/search/ending_soon.php';
    }
}
