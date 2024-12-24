<?php

// require_once __DIR__ . '/Project.php';

class ProjectController {
    private $projectModel;
    private $fileManager;
    public function __construct($projectModel, $fileManager){
        $this->projectModel = $projectModel;
        $this->fileManager = $fileManager;
    }

    public function create($title = "", $description = "", $goalAmount = "") {
        $projects = Project::getAll();
        $id = count($projects) + 1;

        $project = new Project($id, $title, $description, $goalAmount);
        $project->save();
        echo "Project created successfully!";
    }

    public function list() {
        return Project::getAll();
    }

    public function delete($id) {
        FileManager::delete(__DIR__ . '/../data/projects.json', $id);
        echo "Project deleted successfully!";
    }

    public function edit($id, $title = "", $description = "", $goalAmount = "") {
        $project = Project::getById($id);
        
        if ($project) {
            $project->setTitle($title);
            $project->setDescription($description);
            $project->setGoalAmount($goalAmount);
            
            $project->save();
            
            echo "Project updated successfully!";
        } else {
            echo "Project not found!";
        }
    }

    public function details($id){
        $project = Project::getById($id);
        if ($project) {
            return $project;
        } else {
            return "Project not found!";
        }
    }
}
