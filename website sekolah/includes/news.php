<?php
require_once '../config/database.php';

class News {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllNews($limit = 3) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getNewsById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM news WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }

    public function addNews($title, $content, $image_url) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO news (title, content, image_url) VALUES (:title, :content, :image_url)");
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':content', $content);
            $stmt->bindValue(':image_url', $image_url);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updateNews($id, $title, $content, $image_url) {
        try {
            $stmt = $this->pdo->prepare("UPDATE news SET title = :title, content = :content, image_url = :image_url WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':content', $content);
            $stmt->bindValue(':image_url', $image_url);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function deleteNews($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM news WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>
