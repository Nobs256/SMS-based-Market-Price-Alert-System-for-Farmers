<?php
namespace App;

use PDO;

class PostService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function addPost($title, $content, $category, $adminId) {
        $stmt = $this->db->prepare("INSERT INTO posts (title, content, category, author_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$title, $content, $category, $adminId]);
    }

    public function getAllPosts($category = null) {
        if ($category) {
            $stmt = $this->db->prepare("SELECT p.*, a.username as author FROM posts p JOIN admins a ON p.author_id = a.id WHERE category = ? ORDER BY created_at DESC");
            $stmt->execute([$category]);
        } else {
            $stmt = $this->db->query("SELECT p.*, a.username as author FROM posts p JOIN admins a ON p.author_id = a.id ORDER BY created_at DESC");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostById($id) {
        $stmt = $this->db->prepare("SELECT p.*, a.username as author FROM posts p JOIN admins a ON p.author_id = a.id WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deletePost($id) {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}