<?php

/**
 * Enhanced Project Model
 *
 * Handles project operations with advanced features like categories,
 * images, deadlines, and status management
 */
class Project {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new project with enhanced features
     *
     * @param array $data Project data
     * @return int|false Project ID or false on failure
     */
    public function addProject($data) {
        try {
            $this->db->beginTransaction();

            // Generate unique slug
            $slug = $this->generateSlug($data['title']);

            $query = "INSERT INTO projects (
                title, slug, description, short_description, goal_amount,
                user_id, category_id, featured_image, video_url,
                start_date, end_date, min_donation, status
            ) VALUES (
                :title, :slug, :description, :short_description, :goal_amount,
                :user_id, :category_id, :featured_image, :video_url,
                :start_date, :end_date, :min_donation, :status
            )";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':short_description', $data['short_description'] ?? '');
            $stmt->bindParam(':goal_amount', $data['goal_amount']);
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':category_id', $data['category_id'] ?? null);
            $stmt->bindParam(':featured_image', $data['featured_image'] ?? null);
            $stmt->bindParam(':video_url', $data['video_url'] ?? null);
            $stmt->bindParam(':start_date', $data['start_date'] ?? date('Y-m-d'));
            $stmt->bindParam(':end_date', $data['end_date'] ?? null);
            $stmt->bindParam(':min_donation', $data['min_donation'] ?? 1.00);
            $stmt->bindParam(':status', $data['status'] ?? 'draft');

            $stmt->execute();
            $projectId = $this->db->lastInsertId();

            // Add tags if provided
            if (!empty($data['tags'])) {
                $this->addProjectTags($projectId, $data['tags']);
            }

            $this->db->commit();
            return $projectId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating project: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Legacy method for backward compatibility
     */
    public function addProjectLegacy($title, $description, $goalAmount, $userId) {
        $data = [
            'title' => $title,
            'description' => $description,
            'goal_amount' => $goalAmount,
            'user_id' => $userId,
            'status' => 'active'
        ];
        return $this->addProject($data);
    }

    /**
     * Get all active projects with enhanced data
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllProjects($filters = [], $limit = null, $offset = 0) {
        $whereConditions = ["p.status IN ('active', 'funded')"];
        $params = [];

        // Apply filters
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(p.title LIKE :search OR p.description LIKE :search OR p.short_description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $whereConditions = ["p.status = :status"]; // Override default status filter
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['featured'])) {
            $whereConditions[] = "p.is_featured = 1";
        }

        $whereClause = implode(' AND ', $whereConditions);

        $query = "SELECT p.*, u.name as creator_name, c.name as category_name, c.color as category_color,
                         (SELECT COUNT(*) FROM donations d WHERE d.project_id = p.id AND d.payment_status = 'completed') as backer_count,
                         COALESCE(p.current_amount, 0) as current_amount,
                         CASE
                             WHEN p.end_date IS NOT NULL AND p.end_date < CURDATE() THEN 'expired'
                             WHEN p.current_amount >= p.goal_amount THEN 'funded'
                             ELSE p.status
                         END as computed_status,
                         CASE
                             WHEN p.end_date IS NOT NULL THEN DATEDIFF(p.end_date, CURDATE())
                             ELSE NULL
                         END as days_remaining
                  FROM projects p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE $whereClause
                  ORDER BY p.is_featured DESC, p.created_at DESC";

        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

        $stmt = $this->db->prepare($query);

        // Bind parameters with proper types
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project by ID with full details
     *
     * @param int $projectId
     * @return array|false
     */
    public function getProjectById($projectId) {
        $query = "SELECT p.*, u.name as creator_name, u.bio as creator_bio, u.avatar as creator_avatar,
                         c.name as category_name, c.color as category_color, c.icon as category_icon,
                         (SELECT COUNT(*) FROM donations d WHERE d.project_id = p.id AND d.payment_status = 'completed') as backer_count,
                         COALESCE(p.current_amount, 0) as current_amount,
                         CASE
                             WHEN p.end_date IS NOT NULL AND p.end_date < CURDATE() THEN 'expired'
                             WHEN p.current_amount >= p.goal_amount THEN 'funded'
                             ELSE p.status
                         END as computed_status,
                         CASE
                             WHEN p.end_date IS NOT NULL THEN DATEDIFF(p.end_date, CURDATE())
                             ELSE NULL
                         END as days_remaining,
                         CASE
                             WHEN p.goal_amount > 0 THEN ROUND((p.current_amount / p.goal_amount) * 100, 2)
                             ELSE 0
                         END as funding_percentage
                  FROM projects p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            // Get project tags
            $project['tags'] = $this->getProjectTags($projectId);

            // Get project images
            $project['images'] = $this->getProjectImages($projectId);
        }

        return $project;
    }

    public function updateProject($projectId, $title, $description, $goalAmount) {
        $query = "UPDATE projects SET title = :title, description = :description, goal_amount = :goal_amount WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':goal_amount', $goalAmount);
        $stmt->bindParam(':id', $projectId);
        return $stmt->execute();
    }

    public function deleteProject($projectId) {
        $stmt = $this->db->prepare('DELETE FROM projects WHERE id = :id');
        $stmt->bindParam(':id', $projectId);
        return $stmt->execute();
    }

    public function getProjectsByUserId($userId) {
        $query = "SELECT p.*, c.name as category_name, c.color as category_color,
                         (SELECT COUNT(*) FROM donations d WHERE d.project_id = p.id AND d.payment_status = 'completed') as backer_count,
                         COALESCE(p.current_amount, 0) as current_amount,
                         CASE
                             WHEN p.end_date IS NOT NULL AND p.end_date < CURDATE() THEN 'expired'
                             WHEN p.current_amount >= p.goal_amount THEN 'funded'
                             ELSE p.status
                         END as computed_status,
                         CASE
                             WHEN p.end_date IS NOT NULL THEN DATEDIFF(p.end_date, CURDATE())
                             ELSE NULL
                         END as days_remaining
                  FROM projects p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.user_id = :user_id
                  ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Generate unique slug for project
     *
     * @param string $title
     * @return string
     */
    private function generateSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');

        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     *
     * @param string $slug
     * @return bool
     */
    private function slugExists($slug) {
        $query = "SELECT COUNT(*) FROM projects WHERE slug = :slug";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Add tags to project
     *
     * @param int $projectId
     * @param array $tags
     * @return bool
     */
    public function addProjectTags($projectId, $tags) {
        if (empty($tags)) return true;

        $query = "INSERT IGNORE INTO project_tags (project_id, tag) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);

        foreach ($tags as $tag) {
            $tag = trim(strtolower($tag));
            if (!empty($tag)) {
                $stmt->execute([$projectId, $tag]);
            }
        }

        return true;
    }

    /**
     * Get project tags
     *
     * @param int $projectId
     * @return array
     */
    public function getProjectTags($projectId) {
        $query = "SELECT tag FROM project_tags WHERE project_id = :project_id ORDER BY tag";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'tag');
    }

    /**
     * Get project images
     *
     * @param int $projectId
     * @return array
     */
    public function getProjectImages($projectId) {
        $query = "SELECT * FROM project_images WHERE project_id = :project_id ORDER BY is_primary DESC, sort_order ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add project image
     *
     * @param int $projectId
     * @param string $imagePath
     * @param string $altText
     * @param bool $isPrimary
     * @return bool
     */
    public function addProjectImage($projectId, $imagePath, $altText = '', $isPrimary = false) {
        if ($isPrimary) {
            // Remove primary flag from other images
            $updateQuery = "UPDATE project_images SET is_primary = 0 WHERE project_id = :project_id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $updateStmt->execute();
        }

        $query = "INSERT INTO project_images (project_id, image_path, alt_text, is_primary) VALUES (:project_id, :image_path, :alt_text, :is_primary)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->bindParam(':image_path', $imagePath);
        $stmt->bindParam(':alt_text', $altText);
        $stmt->bindParam(':is_primary', $isPrimary, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    /**
     * Update project current amount (called when donation is made)
     *
     * @param int $projectId
     * @return bool
     */
    public function updateCurrentAmount($projectId) {
        $query = "UPDATE projects SET current_amount = (
                      SELECT COALESCE(SUM(amount), 0)
                      FROM donations
                      WHERE project_id = :project_id AND payment_status = 'completed'
                  ) WHERE id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get featured projects
     *
     * @param int $limit
     * @return array
     */
    public function getFeaturedProjects($limit = 6) {
        $query = "SELECT p.*, u.name as creator_name, c.name as category_name, c.color as category_color,
                         (SELECT COUNT(*) FROM donations d WHERE d.project_id = p.id AND d.payment_status = 'completed') as backer_count,
                         COALESCE(p.current_amount, 0) as current_amount,
                         CASE
                             WHEN p.goal_amount > 0 THEN ROUND((p.current_amount / p.goal_amount) * 100, 2)
                             ELSE 0
                         END as funding_percentage
                  FROM projects p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_featured = 1 AND p.status = 'active'
                  ORDER BY p.created_at DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search projects
     *
     * @param string $searchTerm
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchProjects($searchTerm, $filters = [], $limit = 20, $offset = 0) {
        $filters['search'] = $searchTerm;
        return $this->getAllProjects($filters, $limit, $offset);
    }

    /**
     * Get projects by category
     *
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getProjectsByCategory($categoryId, $limit = 20, $offset = 0) {
        $filters = ['category_id' => $categoryId];
        return $this->getAllProjects($filters, $limit, $offset);
    }
}
