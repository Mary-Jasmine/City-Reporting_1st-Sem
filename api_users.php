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

function getUploadDir() {
    $currentDir = basename(dirname(__FILE__));
    
    if ($currentDir === 'ADMinn') {
        $uploadDir = '../uploads/profile_pictures/';
    } else {
        $uploadDir = 'uploads/profile_pictures/';
    }
    
    return $uploadDir;
}

$uploadBaseDir = getUploadDir();

function handleProfilePictureUpload($file, $userId) {
    global $uploadBaseDir;
    
    $uploadDir = $uploadBaseDir;
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error occurred.'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filepath' => returnRelativePath($filepath)];
    } else {
        return ['success' => false, 'error' => 'Failed to save uploaded file.'];
    }
}

function returnRelativePath($filepath) {
    $currentDir = basename(dirname(__FILE__));
    
    if ($currentDir === 'ADMinn') {
        return '../' . $filepath;
    } else {
        return $filepath;
    }
}

function getAbsolutePath($relativePath) {
    $currentDir = basename(dirname(__FILE__));
    
    if ($currentDir === 'ADMinn') {
        $cleanPath = str_replace('../', '', $relativePath);
        return $cleanPath;
    } else {
        return $relativePath;
    }
}

function deleteOldProfilePicture($oldPath) {
    if (empty($oldPath)) return true;
    
    $absolutePath = getAbsolutePath($oldPath);
    
    if (file_exists($absolutePath)) {
        return unlink($absolutePath);
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

        if (!empty($search)) {
            $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
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
                'profile_picture' => $user['profile_picture'] ?? null
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
        
        if ($isFileUpload) {
            $userId = isset($_POST['id']) ? intval($_POST['id']) : null;
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $contactNumber = $_POST['contact_number'] ?? '';
            $barangay = $_POST['barangay'] ?? '';
            $sex = $_POST['sex'] ?? 'Male';
            $ageGroup = $_POST['age_group'] ?? '18-25';
            
            $barangayId = null;
            if (!empty($barangay)) {
                $stmt = $conn->prepare("SELECT barangay_id FROM barangay_stats WHERE barangay_name = ?");
                $stmt->execute([$barangay]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $barangayId = $result ? $result['barangay_id'] : null;
            }
            
            $profilePicturePath = null;
            if ($isFileUpload) {
                if ($userId) {
                    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $oldUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $uploadResult = handleProfilePictureUpload($_FILES['profile_picture'], $userId);
                    
                    if ($uploadResult['success']) {
                        deleteOldProfilePicture($oldUser['profile_picture'] ?? null);
                        $profilePicturePath = $uploadResult['filepath'];
                    } else {
                        echo json_encode(["success" => false, "message" => $uploadResult['error']]);
                        exit;
                    }
                } else {
                    $tempId = 'temp_' . uniqid();
                    $uploadResult = handleProfilePictureUpload($_FILES['profile_picture'], $tempId);
                    if ($uploadResult['success']) {
                        $profilePicturePath = $uploadResult['filepath'];
                    } else {
                        echo json_encode(["success" => false, "message" => $uploadResult['error']]);
                        exit;
                    }
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
                
                echo json_encode([
                    "success" => true, 
                    "message" => "User updated successfully",
                    "profile_picture" => $profilePicturePath
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
                
                if ($profilePicturePath && strpos($profilePicturePath, 'temp_') !== false) {
                    $extension = pathinfo($profilePicturePath, PATHINFO_EXTENSION);
                    $newPath = getUploadDir() . 'profile_' . $newUserId . '_' . uniqid() . '.' . $extension;
                    
                    $absoluteOldPath = getAbsolutePath($profilePicturePath);
                    if (rename($absoluteOldPath, $newPath)) {
                        $profilePicturePath = returnRelativePath($newPath);
                        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
                        $stmt->execute([$profilePicturePath, $newUserId]);
                    }
                }
                
                echo json_encode([
                    "success" => true, 
                    "message" => "User added successfully",
                    "profile_picture" => $profilePicturePath
                ]);
                exit;
            }
            
        } else {
            $input = json_decode(file_get_contents("php://input"), true);

            if (isset($input['bulk_action']) && isset($input['ids']) && is_array($input['ids'])) {
                $ids = array_map('intval', $input['ids']);
                
                if (empty($ids)) {
                    echo json_encode(["success" => false, "message" => "No users selected"]);
                    exit;
                }

                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                switch ($input['bulk_action']) {
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

            $barangayId = null;
            if (!empty($input["barangay"])) {
                $stmt = $conn->prepare("SELECT barangay_id FROM barangay_stats WHERE barangay_name = ?");
                $stmt->execute([$input["barangay"]]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $barangayId = $result ? $result['barangay_id'] : null;
            }

            if (isset($input['id'])) {
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
                    $input["name"],
                    $input["email"],
                    $input["contact_number"] ?? "",
                    $barangayId,
                    $input["sex"] ?? "Male",
                    $input["age_group"] ?? "18-25",
                    $input["id"]
                ]);

                echo json_encode(["success" => true, "message" => "User updated successfully"]);
                exit;
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO users (
                        full_name, 
                        email, 
                        password, 
                        contact_number, 
                        barangay_id, 
                        sex, 
                        age_group,
                        created_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $password = password_hash("default123", PASSWORD_DEFAULT);
                
                $stmt->execute([
                    $input["name"],
                    $input["email"],
                    $password,
                    $input["contact_number"] ?? "",
                    $barangayId,
                    $input["sex"] ?? "Male",
                    $input["age_group"] ?? "18-25"
                ]);

                echo json_encode(["success" => true, "message" => "User added successfully"]);
                exit;
            }
        }

    } catch (Exception $e) {
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