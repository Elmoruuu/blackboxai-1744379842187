<?php
require_once '../config/database.php';

class Gallery {
    private $pdo;
    private $uploadDir = '../assets/images/gallery/';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllImages($category = null, $limit = null) {
        try {
            $query = "SELECT * FROM gallery";
            if ($category) {
                $query .= " WHERE category = :category";
            }
            $query .= " ORDER BY created_at DESC";
            if ($limit) {
                $query .= " LIMIT :limit";
            }

            $stmt = $this->pdo->prepare($query);
            if ($category) {
                $stmt->bindValue(':category', $category);
            }
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Gallery Fetch Error: " . $e->getMessage());
            return [];
        }
    }

    public function addImage($title, $image, $description, $category) {
        try {
            // Handle file upload
            $fileName = $this->uploadImage($image);
            if (!$fileName) {
                return false;
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO gallery (title, image_url, description, category) 
                VALUES (:title, :image_url, :description, :category)
            ");

            return $stmt->execute([
                ':title' => $title,
                ':image_url' => 'assets/images/gallery/' . $fileName,
                ':description' => $description,
                ':category' => $category
            ]);
        } catch(PDOException $e) {
            error_log("Gallery Insert Error: " . $e->getMessage());
            return false;
        }
    }

    private function uploadImage($image) {
        // Check if directory exists, if not create it
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        // Generate unique filename
        $fileName = uniqid() . '_' . basename($image['name']);
        $targetPath = $this->uploadDir . $fileName;

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowedTypes)) {
            error_log("Invalid file type: " . $image['type']);
            return false;
        }

        // Validate file size (max 5MB)
        if ($image['size'] > 5000000) {
            error_log("File too large: " . $image['size']);
            return false;
        }

        // Move uploaded file
        if (move_uploaded_file($image['tmp_name'], $targetPath)) {
            return $fileName;
        }

        error_log("Failed to move uploaded file");
        return false;
    }

    public function deleteImage($id) {
        try {
            // Get image URL before deletion
            $stmt = $this->pdo->prepare("SELECT image_url FROM gallery WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $image = $stmt->fetch();

            if ($image) {
                // Delete file from server
                $filePath = '../' . $image['image_url'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Delete database record
                $stmt = $this->pdo->prepare("DELETE FROM gallery WHERE id = :id");
                return $stmt->execute([':id' => $id]);
            }
            return false;
        } catch(PDOException $e) {
            error_log("Gallery Delete Error: " . $e->getMessage());
            return false;
        }
    }

    public function getCategories() {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT category FROM gallery ORDER BY category");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            error_log("Category Fetch Error: " . $e->getMessage());
            return [];
        }
    }
}

// Handle gallery image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $gallery = new Gallery($pdo);
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = $gallery->addImage(
            $_POST['title'],
            $_FILES['image'],
            $_POST['description'],
            $_POST['category']
        );

        if ($result) {
            header('Location: ../admin/gallery.php?success=1');
            exit;
        } else {
            $error_message = 'Gagal mengunggah gambar. Silakan coba lagi.';
        }
    }
}
?>
