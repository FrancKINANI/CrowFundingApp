<?php

require_once __DIR__ . '../Models/Comment.php';

class CommentController {
    public function create($projectId, $userId, $content) {
        $comments = Comment::all();
        $id = count($comments) + 1;

        $comment = new Comment($id, $projectId, $userId, $content);
        $comment->save();
        echo "Comment added successfully!";
    }

    public function list() {
        return Comment::all();
    }

    public function delete($id) {
        FileManager::delete(__DIR__ . '/../data/comments.json', $id);
        echo "Comment deleted successfully!";
    }
}
