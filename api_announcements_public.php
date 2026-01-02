<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt'); 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Configuration file not found",
        "debug" => "config.php is missing from " . __DIR__
    ]);
    exit;
}

require_once __DIR__ . '/config.php';

try {
    if (!class_exists('Database')) {
        throw new Exception('Database class not found. Check config.php');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Database Connection Failed",
        "debug" => $e->getMessage()
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        
        $categoryMap = [
            'Disaster Warnings'      => ['emergency'],
            'Public Works'           => ['announcement'], 
            'Road Closures'          => ['maintenance'],
            'Health Advisories'      => ['announcement', 'alert'], 
            'Emergency Evacuation'   => ['emergency'],
            'Water Interruption'     => ['maintenance'],
        ];
        
        $sql = "
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
            WHERE status = 'published'
        ";
        
        $params = [];
        
        if ($category && isset($categoryMap[$category])) {
            $types = $categoryMap[$category];
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $sql .= " AND type IN ($placeholders)";
            $params = array_merge($params, $types);
        }
        elseif ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " AND (expires_at IS NULL OR expires_at > NOW())";
        
        $sql .= " ORDER BY published_at DESC, created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $db->prepare($sql);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($announcements as &$announcement) {
            if (!empty($announcement['cover_image'])) {
                $imagePath = $announcement['cover_image'];
                
                $imagePath = ltrim($imagePath, '/');
                
                if ($currentDir === 'ADMinn') {
                    $uploadDir = '../uploads/profile_pictures/';
                } else {
                    $uploadDir = 'uploads/profile_pictures/';
                
                return $uploadDir;
            }
                            
                $announcement['cover_image'] = $imagePath;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'data' => $announcements,
            'count' => count($announcements)
        ]);
        
    } catch (PDOException $e) {
        error_log("Query error in api_announcements_public.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Database query failed',
            'debug' => $e->getMessage()
        ]);
    } catch (Exception $e) {
        error_log("General error in api_announcements_public.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred',
            'debug' => $e->getMessage()
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode([
    'success' => false, 
    'message' => 'Method not allowed. Only GET requests are supported.'
]);
?>