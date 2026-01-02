<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

class Database {
    private $host = "localhost";
    private $db_name = "updatcollab";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLogged() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// =================================================================
// 📂 RESOURCE FILE PATH AND UPLOAD HELPERS
// =================================================================

function getResourcePaths($context) {
    $url_path_base = 'uploads/resources/';

    if ($context === 'admin') {
        $file_path_base = '../' . $url_path_base;
    } else {
        $file_path_base = $url_path_base;
    }

    $full_file_path = __DIR__ . '/' . $file_path_base;

    if (!is_dir($full_file_path)) {
        if (!@mkdir($full_file_path, 0755, true)) {
            error_log("Failed to create resource upload directory: " . $full_file_path);
        }
    }

    return [
        'file_path_base' => $file_path_base,
        'url_path_base' => $url_path_base
    ];
}

function deleteOldResourceFile($sourcePath, $context) {
    if (empty($sourcePath) || !is_string($sourcePath)) return;

    if (!str_contains($sourcePath, 'uploads/resources/')) return;

    $paths = getResourcePaths($context);
    $filename = basename($sourcePath);
    $currentFileFullPath = $paths['file_path_base'] . $filename;
    
    if (file_exists($currentFileFullPath)) {
        @unlink($currentFileFullPath);
    }
}

function uploadResourceFile($file, $context) {
    $paths = getResourcePaths($context);
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error occurred.');
    }

    $allowedMimeTypes = [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg', 
        'image/jpg',
        'image/png', 
        'image/gif',
        'image/webp',
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime'
    ];
    
    $maxSize = 50 * 1024 * 1024; // 50MB for videos

    // Get actual mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Also check file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mov'];
    
    if (!in_array($mimeType, $allowedMimeTypes) && !in_array($ext, $allowedExts)) {
        throw new Exception('Invalid file type. Allowed: PDF, DOCX, Images (JPG, PNG, GIF), Videos (MP4, WEBM)');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File size must be less than 50MB.');
    }

    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destinationFileFullPath = $paths['file_path_base'] . $fileName;
    $destinationUrlPath = $paths['url_path_base'] . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destinationFileFullPath)) {
        return $destinationUrlPath;
    } else {
        throw new Exception('Failed to move uploaded file.');
    }
}

// =================================================================
// 📋 RESOURCE DATABASE OPERATIONS
// =================================================================

function createResource($db, $data) {
    $sql = "INSERT INTO admin_resources 
            (`type`, `title`, `description`, `category`, `content`, `source`, `created_at`) 
            VALUES (:type, :title, :description, :category, :content, :source, NOW())";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':type' => $data['type'] ?? 'guide',
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':category' => $data['category'] ?? 'General',
            ':content' => $data['content'] ?? null,
            ':source' => $data['source'] ?? null
        ]);
        
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating resource: " . $e->getMessage());
        throw new Exception("Failed to create resource: " . $e->getMessage());
    }
}

function getResourceById($db, $id) {
    $sql = "SELECT * FROM admin_resources WHERE id = :id LIMIT 1";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching resource: " . $e->getMessage());
        return null;
    }
}

function getAllResources($db, $filters = []) {
    $sql = "SELECT * FROM admin_resources WHERE 1=1";
    $params = [];
    
    if (!empty($filters['type'])) {
        $sql .= " AND `type` = :type";
        $params[':type'] = $filters['type'];
    }
    
    if (!empty($filters['category'])) {
        $sql .= " AND category = :category";
        $params[':category'] = $filters['category'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (title LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching resources: " . $e->getMessage());
        return [];
    }
}

function updateResource($db, $id, $data) {
    $sql = "UPDATE admin_resources 
            SET `type` = :type,
                title = :title,
                description = :description,
                category = :category,
                content = :content,
                source = :source
            WHERE id = :id";
    
    try {
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':type' => $data['type'] ?? 'guide',
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':category' => $data['category'] ?? 'General',
            ':content' => $data['content'] ?? null,
            ':source' => $data['source'] ?? null
        ]);
    } catch (PDOException $e) {
        error_log("Error updating resource: " . $e->getMessage());
        return false;
    }
}

function deleteResource($db, $id, $context) {
    try {
        $resource = getResourceById($db, $id);
        
        if (!$resource) {
            return false;
        }
        
        if (!empty($resource['source'])) {
            deleteOldResourceFile($resource['source'], $context);
        }
        
        $sql = "DELETE FROM admin_resources WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log("Error deleting resource: " . $e->getMessage());
        return false;
    }
}

function getResourceCategories($db) {
    $sql = "SELECT DISTINCT category FROM admin_resources WHERE category IS NOT NULL ORDER BY category ASC";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

function countResources($db, $type = null) {
    $sql = "SELECT COUNT(*) FROM admin_resources";
    $params = [];
    
    if ($type) {
        $sql .= " WHERE `type` = :type";
        $params[':type'] = $type;
    }
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error counting resources: " . $e->getMessage());
        return 0;
    }
}

function validateResourceData($data) {
    $errors = [];
    
    if (empty($data['title']) || strlen(trim($data['title'])) === 0) {
        $errors[] = "Title is required.";
    }
    
    if (!empty($data['title']) && strlen($data['title']) > 255) {
        $errors[] = "Title must be less than 255 characters.";
    }
    
    if (!in_array($data['type'] ?? '', ['guide', 'video'])) {
        $errors[] = "Type must be either 'guide' or 'video'.";
    }
    
    if ($data['type'] === 'video' && empty($data['source'])) {
        $errors[] = "Video source URL is required for video resources.";
    }
    
    if ($data['type'] === 'guide' && empty($data['content']) && empty($data['source'])) {
        $errors[] = "Guide must have either content or a file source.";
    }
    
    return $errors;
}

function sanitizeResourceData($data) {
    return [
        'type' => sanitizeInput($data['type'] ?? 'guide'),
        'title' => sanitizeInput($data['title'] ?? ''),
        'description' => sanitizeInput($data['description'] ?? ''),
        'category' => sanitizeInput($data['category'] ?? 'General'),
        'content' => isset($data['content']) ? sanitizeInput($data['content']) : null,
        'source' => isset($data['source']) ? sanitizeInput($data['source']) : null
    ];
}

function getResourcesByCategory($db, $category) {
    $sql = "SELECT * FROM admin_resources WHERE category = :category ORDER BY created_at DESC";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching resources by category: " . $e->getMessage());
        return [];
    }
}

function getResourcesByType($db, $type) {
    $sql = "SELECT * FROM admin_resources WHERE `type` = :type ORDER BY created_at DESC";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([':type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching resources by type: " . $e->getMessage());
        return [];
    }
}

function searchResources($db, $searchTerm) {
    $sql = "SELECT * FROM admin_resources 
            WHERE title LIKE :search 
            OR description LIKE :search 
            OR content LIKE :search 
            ORDER BY created_at DESC";
    
    try {
        $stmt = $db->prepare($sql);
        $search = '%' . $searchTerm . '%';
        $stmt->execute([':search' => $search]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error searching resources: " . $e->getMessage());
        return [];
    }
}

?>