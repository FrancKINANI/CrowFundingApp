<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Project {
    private $id;
    private $title;
    private $description;
    private $goal;
    private $createdBy;

    public function __construct($id = null, $title = "", $description = "", $goal = 0, $createdBy = null) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->goal = $goal;
        $this->createdBy = $createdBy;
    }

    public static function create($title, $description, $goal, $createdBy) {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO projects (title, description, goal, created_by) VALUES (:title, :description, :goal, :createdBy)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'goal' => $goal,
            'createdBy' => $createdBy
        ]);
        return $pdo->lastInsertId();
    }

    public static function getAll() {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM projects";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM projects WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
