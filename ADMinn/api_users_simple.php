<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$configFound = false;
$configPaths = ['../config.php', '../../config.php', 'config.php'];

foreach ($configPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $configFound = true;
        break;
    }
}

if (!$configFound) {
    echo json_encode([
        "success" => false,
        "message" => "config.php not found",
        "searched_paths" => $configPaths,
        "current_dir" => getcwd()
    ]);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    try {
        $sql = "SELECT 
                    user_id AS id,
                    full_name AS name,
                    email,
                    contact_number,
                    barangay_id,
                    sex,
                    age_group,
                    created_at
                FROM users 
                ORDER BY user_id DESC";
        
        $stmt = $conn->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as &$user) {
            if ($user['barangay_id']) {
                $user['barangay'] = "Barangay " . $user['barangay_id'];
            } else {
                $user['barangay'] = "—";
            }
        }
        
        echo json_encode([
            "success" => true,
            "data" => $users,
            "count" => count($users),
            "message" => "Users loaded successfully"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Error fetching users: " . $e->getMessage(),
            "data" => []
        ]);
    }
    exit;
}

if ($method === "POST") {
    try {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (isset($input['bulk_action'])) {
            $action = $input['bulk_action'];
            $ids = $input['ids'] ?? [];
            
            if (empty($ids)) {
                echo json_encode(["success" => false, "message" => "No users selected"]);
                exit;
            }
            
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            if ($action === 'delete') {
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id IN ($placeholders)");
                $stmt->execute($ids);
                echo json_encode(["success" => true, "message" => count($ids) . " user(s) deleted"]);
            } else {
                echo json_encode(["success" => true, "message" => "Action completed"]);
            }
            exit;
        }
        
        $stmt = $conn->prepare("
            INSERT INTO users (full_name, email, password, contact_number, barangay_id, sex, age_group, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $password = password_hash("default123", PASSWORD_DEFAULT);
        
        $stmt->execute([
            $input['name'] ?? '',
            $input['email'] ?? '',
            $password,
            $input['contact_number'] ?? '',
            $input['barangay_id'] ?? null,
            $input['sex'] ?? 'Male',
            $input['age_group'] ?? '18-25'
        ]);
        
        echo json_encode(["success" => true, "message" => "User added successfully"]);
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    exit;
}

if ($method === "PUT") {
    try {
        $input = json_decode(file_get_contents("php://input"), true);
        
        $stmt = $conn->prepare("
            UPDATE users SET
                full_name = ?,
                email = ?,
                contact_number = ?,
                barangay_id = ?,
                sex = ?,
                age_group = ?
            WHERE user_id = ?
        ");
        
        $stmt->execute([
            $input['name'] ?? '',
            $input['email'] ?? '',
            $input['contact_number'] ?? '',
            $input['barangay_id'] ?? null,
            $input['sex'] ?? 'Male',
            $input['age_group'] ?? '18-25',
            $input['id']
        ]);
        
        echo json_encode(["success" => true, "message" => "User updated successfully"]);
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    exit;
}

if ($method === "DELETE") {
    try {
        $input = json_decode(file_get_contents("php://input"), true);
        
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(["success" => true, "message" => "User deleted successfully"]);
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid request method"]);
?>