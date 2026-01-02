<?php
require_once "config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database Connection Failed"]);
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
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $announcements]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed. Only GET requests are supported.']);
?>