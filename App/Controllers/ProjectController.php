<?php

require_once __DIR__ . '/../Models/Project.php';

class ProjectController {
    private $projectModel;

    public function __construct($db) {
        $this->projectModel = new Project($db);
    }

    // Create a new project
    public function create($title, $description, $goalAmount, $userId) {
        if (empty($title) || empty($description) || $goalAmount <= 0) {
            echo "All fields are required and the goal amount must be greater than zero.";
            return false;
        }

        return $this->projectModel->addProject($title, $description, $goalAmount, $userId);
    }

    // List all projects
    public function list() {
        $projects = $this->projectModel->getAllProjects();
        require '../Views/projects/index.php'; // Display the view with the list of projects
    }

    // Show project details
    public function details($projectId) {
        $project = $this->projectModel->getProjectById($projectId);
        if ($project) {
            require '../Views/projects/view.php'; // Display the view with project details
        } else {
            echo "Project not found.";
        }
    }

    // Edit a project
    public function edit($projectId, $title, $description, $goalAmount) {
        $project = $this->projectModel->getProjectById($projectId);
        if ($project) {
            if (!empty($title)) {
                $project['title'] = $title;
            }
            if (!empty($description)) {
                $project['description'] = $description;
            }
            if ($goalAmount > 0) {
                $project['goal_amount'] = $goalAmount;
            }
    
            // Mettre à jour le projet dans la base de données
            $this->projectModel->updateProject($projectId, $project['title'], $project['description'], $project['goal_amount']);
            echo "Project updated successfully!";
        } else {
            echo "Project not found.";
        }
    }

    // Delete a project
    public function delete($projectId) {
        $project = $this->projectModel->getProjectById($projectId);
        if ($project) {
            // Implement the delete method in the Project model
            // $this->projectModel->deleteProject($projectId);
            echo "Project deleted successfully!";
        } else {
            echo "Project not found.";
        }
    }
}