<?php

/**
 * Category Model
 * 
 * Handles project categories and related operations
 */
class Category {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all active categories
     * 
     * @return array
     */
    public function getAllCategories() {
        $query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get category by ID
     * 
     * @param int $categoryId
     * @return array|false
     */
    public function getCategoryById($categoryId) {
        $query = "SELECT * FROM categories WHERE id = :id AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get category by name
     * 
     * @param string $name
     * @return array|false
     */
    public function getCategoryByName($name) {
        $query = "SELECT * FROM categories WHERE name = :name AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get categories with project counts
     * 
     * @return array
     */
    public function getCategoriesWithProjectCounts() {
        $query = "SELECT c.*, COUNT(p.id) as project_count 
                  FROM categories c 
                  LEFT JOIN projects p ON c.id = p.category_id AND p.status = 'active'
                  WHERE c.is_active = 1 
                  GROUP BY c.id 
                  ORDER BY c.name ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add new category (admin only)
     * 
     * @param string $name
     * @param string $description
     * @param string $icon
     * @param string $color
     * @return bool
     */
    public function addCategory($name, $description = '', $icon = 'fas fa-folder', $color = '#007bff') {
        $query = "INSERT INTO categories (name, description, icon, color) VALUES (:name, :description, :icon, :color)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':icon', $icon);
        $stmt->bindParam(':color', $color);
        return $stmt->execute();
    }

    /**
     * Update category (admin only)
     * 
     * @param int $categoryId
     * @param string $name
     * @param string $description
     * @param string $icon
     * @param string $color
     * @return bool
     */
    public function updateCategory($categoryId, $name, $description, $icon, $color) {
        $query = "UPDATE categories SET name = :name, description = :description, icon = :icon, color = :color WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':icon', $icon);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Toggle category active status (admin only)
     * 
     * @param int $categoryId
     * @return bool
     */
    public function toggleCategoryStatus($categoryId) {
        $query = "UPDATE categories SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Delete category (admin only)
     * Note: This will set category_id to NULL for associated projects
     * 
     * @param int $categoryId
     * @return bool
     */
    public function deleteCategory($categoryId) {
        try {
            $this->db->beginTransaction();
            
            // Update projects to remove category reference
            $updateQuery = "UPDATE projects SET category_id = NULL WHERE category_id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
            $updateStmt->execute();
            
            // Delete the category
            $deleteQuery = "DELETE FROM categories WHERE id = :id";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
            $result = $deleteStmt->execute();
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get popular categories based on project count
     * 
     * @param int $limit
     * @return array
     */
    public function getPopularCategories($limit = 6) {
        $query = "SELECT c.*, COUNT(p.id) as project_count 
                  FROM categories c 
                  LEFT JOIN projects p ON c.id = p.category_id AND p.status = 'active'
                  WHERE c.is_active = 1 
                  GROUP BY c.id 
                  HAVING project_count > 0
                  ORDER BY project_count DESC, c.name ASC 
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
