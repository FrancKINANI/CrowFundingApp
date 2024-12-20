<?php

class Comment {
    private $id;
    private $projectId;
    private $userId;
    private $content;
    private static $file = __DIR__ . '../Data/comments.json';

    public function __construct($id = 0, $projectId = 0, $userId = 0, $content = "") {
        $this->id = $id;
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->content = $content;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'projectId' => $this->projectId,
            'userId' => $this->userId,
            'content' => $this->content
        ];
    }

    public function save() {
        $comments = self::all();
        $comments[] = $this->toArray();
        file_put_contents(self::$file, json_encode($comments, JSON_PRETTY_PRINT));
    }

    public static function all() {
        return file_exists(self::$file) ? json_decode(file_get_contents(self::$file), true) : [];
    }
}
