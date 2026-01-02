<?php
require_once 'config.php';
redirectIfNotLogged();

$SCRIPT_CONTEXT = 'admin';

try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && !isset($_GET['api'])) {
        $id = intval($_GET['id']);
        $resource = getResourceById($db, $id);
        
        if ($resource) {
            echo json_encode(['success' => true, 'data' => $resource]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Resource not found']);
        }
        exit;
    }
    
    $filters = [];
    if (!empty($_GET['type'])) {
        $filters['type'] = $_GET['type'];
    }
    if (!empty($_GET['category'])) {
        $filters['category'] = $_GET['category'];
    }
    if (!empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    
    $resources = getAllResources($db, $filters);
    echo json_encode(['success' => true, 'data' => $resources]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }
        
        $data = sanitizeResourceData($input);
        
        $errors = validateResourceData($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }
        
        try {
            $resourceId = createResource($db, $data);
            echo json_encode([
                'success' => true, 
                'message' => 'Resource created successfully',
                'id' => $resourceId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    try {
        $type = sanitizeInput($_POST['resourceType'] ?? 'guide');
        $title = sanitizeInput($_POST['resourceTitle'] ?? '');
        $description = sanitizeInput($_POST['resourceDescription'] ?? '');
        $category = sanitizeInput($_POST['resourceCategory'] ?? 'General');
        $content = sanitizeInput($_POST['resourceContent'] ?? '');
        $videoSource = sanitizeInput($_POST['videoSource'] ?? '');
        
        $source = null;
        
        if ($type === 'guide' && !empty($_FILES['guideDoc']) && $_FILES['guideDoc']['error'] === UPLOAD_ERR_OK) {
            $source = uploadResourceFile($_FILES['guideDoc'], $SCRIPT_CONTEXT);
            $content = null;
        }
        
        if ($type === 'video' && !empty($videoSource)) {
            $source = $videoSource;
            $content = null;
        }
        
        if ($type === 'video' && !empty($_FILES['videoUpload']) && $_FILES['videoUpload']['error'] === UPLOAD_ERR_OK) {
            $source = uploadResourceFile($_FILES['videoUpload'], $SCRIPT_CONTEXT);
        }
        
        $data = [
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'content' => $type === 'guide' && empty($source) ? $content : null,
            'source' => $source
        ];
        
        $errors = validateResourceData($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }
        
        $resourceId = createResource($db, $data);
        echo json_encode([
            'success' => true,
            'message' => 'Resource created successfully',
            'id' => $resourceId
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Resource ID is required']);
        exit;
    }
    
    $id = intval($input['id']);
    $resource = getResourceById($db, $id);
    
    if (!$resource) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Resource not found']);
        exit;
    }
    
    $data = sanitizeResourceData($input);
    
    $errors = validateResourceData($data);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    try {
        $success = updateResource($db, $id, $data);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Resource updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update resource']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid resource ID']);
        exit;
    }
    
    try {
        $success = deleteResource($db, $id, $SCRIPT_CONTEXT);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Resource deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Resource not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>