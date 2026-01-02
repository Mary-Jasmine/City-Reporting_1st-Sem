<?php
require_once "config.php"; 

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database Connection Failed"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

function handleProfilePictureUpload($file, $userId) {
    $uploadDir = __DIR__ . '/../uploads/profile_pictures/';
    
    $dbPath = 'uploads/profile_pictures/';
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'error' => 'Could not create upload directory. Check permissions.'];
        }
    }
    
    if (!is_writable($uploadDir)) {
        return ['success' => false, 'error' => 'Upload directory is not writable. Check folder permissions.'];
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = '';
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMsg = 'File exceeds upload_max_filesize ini directive.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'File exceeds MAX_FILE_SIZE directive.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = 'File was only partially uploaded.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'No file was uploaded.';
                break;
            default:
                $errorMsg = 'Unknown upload error.';
        }
        return ['success' => false, 'error' => 'File upload error: ' . $errorMsg];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed. Received: ' . $file['type']];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit. Size: ' . ($file['size'] / 1024 / 1024) . 'MB'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'profile_' . $userId . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    $dbStorePath = $dbPath . $filename;
    
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Invalid uploaded file.'];
    }
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file. Check directory permissions.'];
    }
    
    if (!file_exists($filepath)) {
        return ['success' => false, 'error' => 'File was uploaded but cannot be found.'];
    }
    
    chmod($filepath, 0644);
    
    return ['success' => true, 'filepath' => $dbStorePath];
}

function deleteOldProfilePicture($oldPath) {
    if (empty($oldPath)) return true;
    
    $absolutePath = __DIR__ . '/../' . $oldPath;
    
    if (file_exists($absolutePath)) {
        return @unlink($absolutePath);
    }
    return true;
}

if ($method === "GET") {
    try {
        $search = isset($_GET['search']) ? trim($_GET['search']) : "";
        $barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : "";
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        $sql = "
            SELECT 
                u.user_id, 
                u.full_name, 
                u.email, 
                u.contact_number, 
                u.barangay_id,
                b.barangay_name,
                u.sex, 
                u.age_group, 
                u.created_at,
                u.id_photo,
                u.profile_picture
            FROM users u
            LEFT JOIN barangay_stats b ON u.barangay_id = b.barangay_id
            WHERE 1=1
        ";

        $params = [];

        if ($id) {
            $sql .= " AND u.user_id = ?";
            $params[] = $id;
        }

        // Improved search - searches in name, email, and contact number
        if (!empty($search)) {
            $sql .= " AND (
                LOWER(u.full_name) LIKE LOWER(?) OR 
                LOWER(u.email) LIKE LOWER(?) OR 
                u.contact_number LIKE ?
            )";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($barangay)) {
            $sql .= " AND b.barangay_name = ?";
            $params[] = $barangay;
        }

        $sql .= " ORDER BY u.user_id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mappedUsers = array_map(function($user) {
            $profilePicture = null;
            if (!empty($user['profile_picture'])) {
                $profilePicture = '../' . $user['profile_picture'];
            }
            
            return [
                'id' => $user['user_id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'contact_number' => $user['contact_number'],
                'barangay' => $user['barangay_name'],
                'barangay_id' => $user['barangay_id'],
                'sex' => $user['sex'],
                'age_group' => $user['age_group'],
                'created_at' => $user['created_at'],
                'id_photo' => $user['id_photo'] ?? null,
                'profile_picture' => $profilePicture
            ];
        }, $users);

        echo json_encode([
            "success" => true, 
            "data" => $mappedUsers,
            "count" => count($mappedUsers)
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode([
            "success" => false, 
            "message" => "Error fetching users: " . $e->getMessage(),
            "data" => []
        ]);
        exit;
    }
}


if ($method === "POST") {
    try {
        $isFileUpload = isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE;

        $data = [];
        
        if (!empty($_POST)) {
            $data = $_POST;
        } else {
            $input = json_decode(file_get_contents("php://input"), true);
            if ($input) {
                $data = $input;
            }
        }
        
        if (isset($data['bulk_action']) && isset($data['ids']) && is_array($data['ids'])) {
            $ids = array_map('intval', $data['ids']);
            
            if (empty($ids)) {
                echo json_encode(["success" => false, "message" => "No users selected"]);
                exit;
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            switch ($data['bulk_action']) {
                case 'delete':
                    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id IN ($placeholders)");
                    $stmt->execute($ids);
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $stmt = $conn->prepare("DELETE FROM users WHERE user_id IN ($placeholders)");
                    $stmt->execute($ids);
                    
                    foreach ($users as $user) {
                        deleteOldProfilePicture($user['profile_picture'] ?? null);
                    }
                    
                    echo json_encode(["success" => true, "message" => "Users deleted successfully"]);
                    exit;
                    
                default:
                    echo json_encode(["success" => false, "message" => "Invalid bulk action"]);
                    exit;
            }
        }
        
        $userId = $data['id'] ?? null;
        $name = $data['full_name'] ?? $data['name'] ?? ''; 
        $email = $data['email'] ?? '';
        $contactNumber = $data['contact_number'] ?? '';
        $barangay = $data['barangay'] ?? '';
        $sex = $data['sex'] ?? 'Male';
        $ageGroup = $data['age_group'] ?? '18-25';

        $barangayId = null;
        if (!empty($barangay)) {
            $stmt = $conn->prepare("SELECT barangay_id FROM barangay_stats WHERE barangay_name = ?");
            $stmt->execute([$barangay]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $barangayId = $result ? $result['barangay_id'] : null;
        }

        $profilePicturePath = null;
        
        if ($isFileUpload) {
            $tempId = $userId ? $userId : 'temp_' . uniqid();
            $oldUser = null;

            if ($userId) {
                $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $oldUser = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            $uploadResult = handleProfilePictureUpload($_FILES['profile_picture'], $tempId);
            
            if ($uploadResult['success']) {
                if ($userId) {
                    deleteOldProfilePicture($oldUser['profile_picture'] ?? null);
                }
                $profilePicturePath = $uploadResult['filepath'];
            } else {
                echo json_encode(["success" => false, "message" => $uploadResult['error']]);
                exit;
            }
        }
        
        if ($userId) {
            
            $sql = "UPDATE users SET 
                full_name = ?,
                email = ?,
                contact_number = ?,
                barangay_id = ?,
                sex = ?,
                age_group = ?";
            
            $params = [$name, $email, $contactNumber, $barangayId, $sex, $ageGroup];
            
            if ($profilePicturePath) {
                $sql .= ", profile_picture = ?";
                $params[] = $profilePicturePath;
            }
            
            $sql .= " WHERE user_id = ?";
            $params[] = $userId;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            $finalProfilePicture = null;
            if ($profilePicturePath) {
                $finalProfilePicture = '../' . $profilePicturePath;
            } else {
                $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $finalProfilePicture = !empty($user['profile_picture']) ? '../' . $user['profile_picture'] : null;
            }


            echo json_encode([
                "success" => true, 
                "message" => "User updated successfully",
                "profile_picture" => $finalProfilePicture
            ]);
            exit;
        } else {
            $password = password_hash("default123", PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO users (
                    full_name, 
                    email, 
                    password, 
                    contact_number, 
                    barangay_id, 
                    sex, 
                    age_group,
                    profile_picture,
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $name,
                $email,
                $password,
                $contactNumber,
                $barangayId,
                $sex,
                $ageGroup,
                $profilePicturePath
            ]);
            
            $newUserId = $conn->lastInsertId();
            
            echo json_encode([
                "success" => true, 
                "message" => "User added successfully",
                "profile_picture" => ($profilePicturePath ? '../' . $profilePicturePath : null),
                "id" => $newUserId
            ]);
            exit;
        }


    } catch (Exception $e) {
        if (isset($profilePicturePath) && !$userId) {
            deleteOldProfilePicture($profilePicturePath);
        }
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit;
    }
}


if ($method === "DELETE") {
    try {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['id'])) {
            echo json_encode(["success" => false, "message" => "User ID is required"]);
            exit;
        }

        $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $stmt->execute([$input["id"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$input["id"]]);
        
        deleteOldProfilePicture($user['profile_picture'] ?? null);
        
        echo json_encode(["success" => true, "message" => "User deleted successfully"]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        exit;
    }
}
?>