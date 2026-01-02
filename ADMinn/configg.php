<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

class Database {
    private $host = "localhost";
    private $db_name = "updcollab";
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

/**
 * Defines the core upload and display paths for resources.
 * @param string $context Path of the script: 'admin' (from ADMINn/) or 'user' (from root).
 * @return array Associative array with 'file_path_base' and 'url_path_base'.
 */
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

/**
 * Deletes the resource file from the file system.
 * @param string|null $sourcePath The public URL path stored in the database.
 * @param string $context 'admin' or 'user' to get the correct file system path.
 */
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

/**
 * Handles the resource file upload and path generation logic.
 * @param array $file The $_FILES entry for the resource.
 * @param string $context 'admin' or 'user'.
 * @return string The new public URL path to store in the database.
 * @throws Exception If upload fails or file is invalid.
 */
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
        'image/png', 
        'image/gif',
    ];
    $maxSize = 20 * 1024 * 1024; // 20MB

    if (!in_array($file['type'], $allowedMimeTypes)) {
        throw new Exception('Invalid file type. Only PDF, DOCX, JPG, PNG, and GIF are allowed.');
    }
    if ($file['size'] > $maxSize) {
        throw new Exception('File size must be less than 20MB.');
    }

    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $destinationFileFullPath = $paths['file_path_base'] . $fileName;
    $destinationUrlPath = $paths['url_path_base'] . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destinationFileFullPath)) {
        return $destinationUrlPath;
    } else {
        throw new Exception('Failed to move uploaded file.');
    }
}

// =================================================================
// 📋 RESOURCE DATABASE OPERATIONS (admin_resources table)
// =================================================================

/**
 * Creates a new resource in the database.
 * @param PDO $db Database connection.
 * @param array $data Associative array with resource data.
 * @return int The ID of the newly created resource.
 * @throws Exception If creation fails.
 */
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

/**
 * Retrieves a single resource by ID.
 * @param PDO $db Database connection.
 * @param int $id Resource ID.
 * @return array|null Resource data or null if not found.
 */
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

/**
 * Retrieves all resources with optional filtering.
 * @param PDO $db Database connection.
 * @param array $filters Optional filters: type, category, search.
 * @return array Array of resources.
 */
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

/**
 * Updates an existing resource.
 * @param PDO $db Database connection.
 * @param int $id Resource ID.
 * @param array $data Associative array with updated resource data.
 * @return bool True on success, false on failure.
 */
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

/**
 * Deletes a resource and its associated file.
 * @param PDO $db Database connection.
 * @param int $id Resource ID.
 * @param string $context 'admin' or 'user'.
 * @return bool True on success, false on failure.
 */
function deleteResource($db, $id, $context) {
    try {
        // Fetch the resource to get the file path
        $resource = getResourceById($db, $id);
        
        if (!$resource) {
            return false;
        }
        
        // Delete the file if it exists
        if (!empty($resource['source'])) {
            deleteOldResourceFile($resource['source'], $context);
        }
        
        // Delete the database record
        $sql = "DELETE FROM admin_resources WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log("Error deleting resource: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets unique categories from resources.
 * @param PDO $db Database connection.
 * @return array Array of unique categories.
 */
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

/**
 * Counts resources by type.
 * @param PDO $db Database connection.
 * @param string|null $type Optional type filter.
 * @return int Count of resources.
 */
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

/**
 * Validates resource data before insert/update.
 * @param array $data Resource data to validate.
 * @return array Array of validation errors (empty if valid).
 */
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

/**
 * Sanitizes resource data for safe database operations.
 * @param array $data Raw resource data.
 * @return array Sanitized resource data.
 */
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

?>