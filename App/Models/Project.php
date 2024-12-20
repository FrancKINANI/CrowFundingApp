<?php

class Project {
    private $id;
    private $title;
    private $description;
    private $goalAmount;
    private $collectedAmount;
    private static $file = __DIR__ . '../Data/projects.json';

    public function __construct($id = 0, $title = "", $description = "", $goalAmount = "", $collectedAmount = 0) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->goalAmount = $goalAmount;
        $this->collectedAmount = $collectedAmount;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'goalAmount' => $this->goalAmount,
            'collectedAmount' => $this->collectedAmount
        ];
    }

    public function save() {
        $projects = self::getAll();
        $projects[] = $this->toArray();
        file_put_contents(self::$file, json_encode($projects, JSON_PRETTY_PRINT));
    }

    public static function getAll() {
        return file_exists(self::$file) ? json_decode(file_get_contents(self::$file), true) : [];
    }

    public static function getById($id) {
        $projects = self::getAll();
        foreach ($projects as $project) {
            if ($project->getId() == $id) {
                return $project;
            }
        }
        return null;
    }
}
