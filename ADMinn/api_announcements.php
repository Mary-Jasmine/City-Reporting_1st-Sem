<?php
require_once "config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

redirectIfNotLogged();

try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database Connection Failed"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$uploadDir = __DIR__ . '/uploads/announcements/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

    $dbPath = 'uploads/announcements/';
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'error' => 'Could not create upload directory. Check permissions.'];
        }
    }
    
    if (!is_writable($uploadDir)) {
        return ['success' => false, 'error' => 'Upload directory is not writable. Check folder permissions.'];
    }

if ($method === 'GET') {
    try {
        $stmt = $db->prepare("
            SELECT 
                announcements_id as id,
                title,
                content,
                cover_image,
                type,
                priority,
                status,
                published_at,
                expires_at,
                created_at,
                updated_at
            FROM announcements
            ORDER BY published_at DESC, created_at DESC
        ");
        $stmt->execute();
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $announcements]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $type = $_POST['type'] ?? 'announcement';
    $priority = $_POST['priority'] ?? 'medium';
    $status = $_POST['status'] ?? 'draft';
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $remove_image = $_POST['remove_image'] ?? '0';
    
    $coverImagePath = null;
    $existingImagePath = null;
    
    if ($id) {
        $stmt = $db->prepare("SELECT cover_image FROM announcements WHERE announcements_id = ?");
        $stmt->execute([$id]);
        $existingImagePath = $stmt->fetchColumn();
    }
    
    if ($remove_image === '1') {
        if ($existingImagePath) {
            $fullPath = __DIR__ . '/' . ltrim($existingImagePath, '/');
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        $coverImagePath = null;
    } 
    elseif (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']);
            exit;
        }
        
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
            exit;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'announcement_' . time() . '_' . uniqid() . '.' . $extension;
        
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $coverImagePath = 'uploads/announcements/' . $filename;
            
            if ($existingImagePath) {
                $oldFullPath = __DIR__ . '/' . ltrim($existingImagePath, '/');
                if (file_exists($oldFullPath) && $existingImagePath !== $coverImagePath) {
                    unlink($oldFullPath);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image. Check folder permissions.']);
            exit;
        }
    } else {
        $coverImagePath = $existingImagePath;
    }
    
    try {
        if ($id) {
            $sql = "UPDATE announcements SET 
                    title = ?,
                    content = ?,
                    type = ?,
                    priority = ?,
                    status = ?,
                    expires_at = ?,
                    cover_image = ?,
                    updated_at = NOW()
                    WHERE announcements_id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $title,
                $content,
                $type,
                $priority,
                $status,
                $expires_at,
                $coverImagePath,
                $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
        } else {
            $sql = "INSERT INTO announcements 
                    (title, content, type, priority, status, expires_at, cover_image, published_at, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $title,
                $content,
                $type,
                $priority,
                $status,
                $expires_at,
                $coverImagePath
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Announcement created successfully']);
        }
    } catch (PDOException $e) {
        if ($coverImagePath) {
            $fullPath = __DIR__ . '/' . ltrim($coverImagePath, '/');
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing announcement ID']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("SELECT cover_image FROM announcements WHERE announcements_id = ?");
        $stmt->execute([$id]);
        $imagePath = $stmt->fetchColumn();
        
        $stmt = $db->prepare("DELETE FROM announcements WHERE announcements_id = ?");
        $stmt->execute([$id]);
        
        if ($imagePath) {
            $fullPath = __DIR__ . '/' . ltrim($imagePath, '/');
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>