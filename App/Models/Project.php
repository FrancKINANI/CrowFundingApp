<?php

class Project {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ajouter un projet
    public function addProject($title, $description, $goalAmount, $userId) {
        $query = "INSERT INTO projects (title, description, goal_amount, user_id) VALUES (:title, :description, :goal_amount, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':goal_amount', $goalAmount);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    // Récupérer tous les projets
    public function getAllProjects() {
        $query = "SELECT * FROM projects";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un projet par ID
    public function getProjectById($projectId) {
        $query = "SELECT * FROM projects WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $projectId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
}
