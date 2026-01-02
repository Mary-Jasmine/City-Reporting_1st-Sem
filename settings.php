<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$updateError = '';
$updateSuccess = '';

$database = new Database();
$conn = $database->getConnection();

function getBarangays($conn) {
    if (!$conn) return [];
    try {
        $sql = "SELECT barangay_id, barangay_name FROM barangay_stats ORDER BY barangay_name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Barangay fetch error: " . $e->getMessage());
        return [];
    }
}

$barangays = $conn ? getBarangays($conn) : [];

function handleProfilePictureUpload($file, $userId) {
    $uploadDir = 'uploads/profile_pictures/';
    
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
        return ['success' => true, 'filepath' => $filepath];
    } else {
        return ['success' => false, 'error' => 'Failed to save uploaded file.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $lang = in_array($_POST['language'] ?? '', ['en','tl']) ? $_POST['language'] : 'en';
    $_SESSION['lang'] = $lang;
    $_SESSION['theme'] = ($_POST['theme'] ?? '') === 'dark' ? 'dark' : 'light';
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=settings&status=success&message=' . urlencode('Settings saved successfully!'));
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $contactNumber = sanitizeInput($_POST['contact_number'] ?? '');
    $barangayId = (int)($_POST['barangay_id'] ?? 0);
    $newPassword = $_POST['new_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $removeProfilePic = isset($_POST['remove_profile_picture']);

    if (empty($fullName) || empty($contactNumber) || $barangayId === 0 || empty($currentPassword)) {
        $updateError = 'All fields (including Current Password) must be filled.';
    } elseif ($conn) {
        try {
            $conn->beginTransaction();

            $userCheckSql = "SELECT password, profile_picture FROM users WHERE user_id = :user_id";
            $userCheckStmt = $conn->prepare($userCheckSql);
            $userCheckStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $userCheckStmt->execute();
            $userRecord = $userCheckStmt->fetch(PDO::FETCH_ASSOC);

            if (!$userRecord || !password_verify($currentPassword, $userRecord['password'])) {
                $conn->rollBack();
                $updateError = 'Invalid current password.';
            } else {
                $updateSql = "UPDATE users SET full_name = :full_name, contact_number = :contact_number, barangay_id = :barangay_id";
                $updateParams = [
                    ':full_name' => $fullName,
                    ':contact_number' => $contactNumber,
                    ':barangay_id' => $barangayId,
                    ':user_id' => $user_id
                ];

                if (!empty($newPassword)) {
                    if (strlen($newPassword) < 8) {
                        $conn->rollBack();
                        $updateError = 'New password must be at least 8 characters long.';
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?status=error&message=' . urlencode($updateError) . '&tab=profile');
                        exit;
                    }
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateSql .= ", password = :password";
                    $updateParams[':password'] = $hashedPassword;
                }

                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadResult = handleProfilePictureUpload($_FILES['profile_picture'], $user_id);
                    
                    if ($uploadResult['success']) {
                        if (!empty($userRecord['profile_picture']) && file_exists($userRecord['profile_picture'])) {
                            unlink($userRecord['profile_picture']);
                        }
                        
                        $updateSql .= ", profile_picture = :profile_picture";
                        $updateParams[':profile_picture'] = $uploadResult['filepath'];
                    } else {
                        $conn->rollBack();
                        $updateError = $uploadResult['error'];
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?status=error&message=' . urlencode($updateError) . '&tab=profile');
                        exit;
                    }
                }

                if ($removeProfilePic) {
                    if (!empty($userRecord['profile_picture']) && file_exists($userRecord['profile_picture'])) {
                        unlink($userRecord['profile_picture']);
                    }
                    $updateSql .= ", profile_picture = NULL";
                }

                $updateSql .= " WHERE user_id = :user_id";

                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->execute($updateParams);

                $conn->commit();
                
                $_SESSION['user_name'] = $fullName;
                
                header('Location: ' . $_SERVER['PHP_SELF'] . '?status=success&message=' . urlencode('Profile updated successfully!') . '&tab=profile');
                exit;
            }

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Profile update error: " . $e->getMessage());
            $updateError = "Database error: Could not update profile. Please try again.";
        }
    }

    if (!empty($updateError)) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?status=error&message=' . urlencode($updateError) . '&tab=profile');
        exit;
    }
}

if (isset($_GET['status']) && isset($_GET['message'])) {
    $statusType = sanitizeInput($_GET['status']);
    $statusMessage = sanitizeInput($_GET['message']);
    if ($statusType === 'success') {
        $updateSuccess = $statusMessage;
    } else {
        $updateError = $statusMessage;
    }
}

$userData = null;
if ($conn) {
    try {
        $sql = "SELECT 
                    u.user_id,
                    u.full_name, 
                    u.email, 
                    u.contact_number,
                    u.profile_picture,
                    u.barangay_id,
                    u.created_at,
                    b.barangay_name
                FROM 
                    users u
                LEFT JOIN 
                    barangay_stats b ON u.barangay_id = b.barangay_id
                WHERE 
                    u.user_id = :user_id 
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Profile fetch error: " . $e->getMessage());
        $userData = null;
    }
}

if (!$userData) {
    $userData = [
        'full_name' => 'Guest User',
        'email' => 'N/A',
        'contact_number' => 'N/A',
        'barangay_name' => 'N/A',
        'barangay_id' => 0,
        'user_id' => $user_id ?? 0,
        'profile_picture' => null,
    ];
    $userData['avatar_initial'] = 'U';
} else {
    $nameParts = explode(' ', trim($userData['full_name']));
    $initials = '';
    if (!empty($nameParts)) {
        $initials .= strtoupper(substr($nameParts[0], 0, 1));
        if (count($nameParts) > 1) {
            $initials .= strtoupper(substr(end($nameParts), 0, 1));
        }
    }
    $userData['avatar_initial'] = $initials ?: 'U';
    
    if (isset($userData['created_at'])) {
        $userData['member_since'] = date('F j, Y', strtotime($userData['created_at']));
    } else {
        $userData['member_since'] = 'N/A';
    }
}

$currentLang = $_SESSION['lang'] ?? 'en';
$currentTheme = $_SESSION['theme'] ?? 'light';
$activeTab = $_GET['tab'] ?? 'profile';

?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipality Incident Reporting - Settings & Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.5;
        }
        
        body.set-body{
            background: 
                linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)), 
                url('chujjrch.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            padding: 40px 50px;
            border-radius: 3%;
            margin-top: 3%;
            margin-left: 280px;  
            margin-right: 0;
            padding-top: 30px;    
            height: 60%;
            width:70%;
            background-color: whitesmoke
        }
        
        .page-title {
            margin-bottom: 25px;
        }
        
        .page-title h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .page-title p {
            color: #6c757d;
        }

        .card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .section-title {
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .option:last-child {
            margin-bottom: 0;
        }
        
        .option-info h3 {
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .option-info p {
            color: #6c757d;
            font-size: 14px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .3s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #2c80ff;
        }
        
        input:checked + .slider:before {
            transform: translateX(24px);
        }
        
        .select-wrapper {
            position: relative;
            width: 180px;
        }
        
        .select-wrapper select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background-color: white;
            appearance: none;
            font-size: 14px;
        }
        
        .select-wrapper::after {
            content: "▼";
            font-size: 12px;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-primary {
            background-color: #a10e09ff;
            color: white;
            margin-left: 42%;
        }
        
        .btn-primary:hover {
            background-color: #1a6fe0;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #002e8bff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin-right: 20px;
            font-weight: 600;
            overflow: hidden;
            position: relative;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-upload-container {
            position: relative;
            display: inline-block;
        }

        .avatar-upload-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            padding: 5px;
            font-size: 11px;
            cursor: pointer;
            border-radius: 0 0 50% 50%;
        }

        #profile_picture_input {
            display: none;
        }

        .preview-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            overflow: hidden;
        }

        .preview-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-avatar-initial {
            font-size: 48px;
            color: #002e8bff;
            font-weight: 600;
        }
        
        .profile-info h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        
        .profile-info p {
            color: #6c757d;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .field {
            margin-bottom: 15px;
        }
        
        .field label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .field p, .field input, .field select {
            font-size: 16px;
            color: #333;
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .field input:focus, .field select:focus {
            border-color: #2c80ff;
            outline: none;
        }

        .field p {
            padding: 8px 0;
            border: none;
            background-color: transparent;
        }

        .field-full-width {
            grid-column: 1 / -1;
        }
        
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 25px 0;
        }
        
        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .option {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .option-control {
                margin-top: 10px;
                width: 100%;
            }
            
            .select-wrapper {
                width: 100%;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
        
        .tab-container {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .tab.active {
            border-bottom-color: #900303ff;
            color: #900303ff;
        }
        
        .page {
            display: none;
        }
        
        .page.active {
            display: block;
        }

        .message-success {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .message-error {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .hidden {
            display: none !important;
        }

        .checkbox-field {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-field input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
    </style>
</head>
<body class="set-body <?php echo function_exists('body_theme_class') ? body_theme_class() : ($currentTheme === 'dark' ? 'theme-dark' : ''); ?>">
    <?php include 'header.php'; ?>

    <div class="main-content" style="margin-bottom: 3%;">
        <div class="tab-container">
            <div class="tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" data-page="profile">Profile</div>
            <div class="tab <?php echo $activeTab === 'settings' ? 'active' : ''; ?>" data-page="settings">Settings</div>
        </div>

        <div id="profile-page" class="page <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
            <div class="page-title">
                <h1>User Profile</h1>
                <p>View and manage your personal information</p>
            </div>

            <?php if (!empty($updateSuccess)): ?>
                <div class="message-success"><?php echo htmlspecialchars($updateSuccess); ?></div>
            <?php endif; ?>
            <?php if (!empty($updateError)): ?>
                <div class="message-error"><?php echo htmlspecialchars($updateError); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="profile-header">
                    <div class="avatar-upload-container">
                        <div class="avatar">
                            <?php if (!empty($userData['profile_picture']) && file_exists($userData['profile_picture'])): ?>
                                <img src="<?= htmlspecialchars($userData['profile_picture']); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <?= htmlspecialchars($userData['avatar_initial']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($userData['full_name']); ?></h2>
                        <p>Registered User</p>
                    </div>
                </div>

                <div id="profile-view">
                    <div class="profile-grid">
                        <div class="field">
                            <label>Full Barangay Name</label>
                            <p><?= htmlspecialchars($userData['full_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Email (Cannot be changed)</label>
                            <p><?= htmlspecialchars($userData['email']); ?></p>
                        </div>
                        <div class="field">
                            <label>Contact Number</label>
                            <p><?= htmlspecialchars($userData['contact_number']); ?></p>
                        </div>
                        <div class="field">
                            <label>Barangay</label>
                            <p><?= htmlspecialchars($userData['barangay_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Member Since</label>
                            <p><?= htmlspecialchars($userData['member_since']); ?></p>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <button class="btn btn-primary" id="edit-profile-btn">Edit Profile</button>
                </div>

                <div id="profile-edit-form" class="hidden">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <!-- Profile Picture Upload Section -->
                        <div class="field field-full-width">
                            <label>Profile Picture</label>
                            <div style="text-align: center;">
                                <div class="preview-avatar" id="preview-avatar">
                                    <?php if (!empty($userData['profile_picture']) && file_exists($userData['profile_picture'])): ?>
                                        <img src="<?= htmlspecialchars($userData['profile_picture']); ?>" alt="Profile Picture" id="preview-image">
                                    <?php else: ?>
                                        <span class="preview-avatar-initial" id="preview-initial"><?= htmlspecialchars($userData['avatar_initial']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="profile_picture" id="profile_picture_input" accept="image/jpeg,image/jpg,image/png,image/gif">
                                <button type="button" class="btn btn-secondary" id="choose-picture-btn">Choose New Picture</button>
                                <?php if (!empty($userData['profile_picture'])): ?>
                                    <div class="checkbox-field" style="justify-content: center; margin-top: 10px;">
                                        <input type="checkbox" name="remove_profile_picture" id="remove_profile_picture">
                                        <label for="remove_profile_picture" style="margin: 0;">Remove current profile picture</label>
                                    </div>
                                <?php endif; ?>
                                <p style="color: #6c757d; font-size: 12px; margin-top: 10px;">Max file size: 5MB. Allowed formats: JPG, PNG, GIF</p>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="profile-grid">
                            <div class="field">
                                <label for="edit_full_name">Full Name *</label>
                                <input type="text" id="edit_full_name" name="full_name" value="<?= htmlspecialchars($userData['full_name']); ?>" required>
                            </div>
                            <div class="field">
                                <label for="edit_email">Email (Read Only)</label>
                                <input type="email" id="edit_email" value="<?= htmlspecialchars($userData['email']); ?>" disabled>
                            </div>
                            <div class="field">
                                <label for="edit_contact_number">Contact Number *</label>
                                <input type="text" id="edit_contact_number" name="contact_number" value="<?= htmlspecialchars($userData['contact_number']); ?>" required>
                            </div>
                            <div class="field">
                                <label for="edit_barangay_id">Barangay *</label>
                                <select id="edit_barangay_id" name="barangay_id" required>
                                    <?php foreach ($barangays as $barangay): ?>
                                        <option 
                                            value="<?= htmlspecialchars($barangay['barangay_id']); ?>"
                                            <?= (int)$userData['barangay_id'] === (int)$barangay['barangay_id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($barangay['barangay_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label for="new_password">New Password (Optional)</label>
                                <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                            </div>
                            <div class="field">
                                <label for="current_password">Current Password * (Confirm changes)</label>
                                <input type="password" id="current_password" name="current_password" required placeholder="Enter current password">
                            </div>
                        </div>
                        <div class="divider"></div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" id="cancel-edit-btn">Cancel</button>
                    </form>
                </div>


</div>
        </div>

        <div id="settings-page" class="page <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
            <div class="page-title">
                <h1>Settings</h1>
                <p>Customize your application experience</p>
            </div>
            
            <?php if ($activeTab === 'settings' && !empty($updateSuccess)): ?>
                <div class="message-success"><?php echo htmlspecialchars($updateSuccess); ?></div>
            <?php endif; ?>
            <?php if ($activeTab === 'settings' && !empty($updateError)): ?>
                <div class="message-error"><?php echo htmlspecialchars($updateError); ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" id="settings-form">
                    <input type="hidden" name="save_settings" value="1">
                    

                    <div class="section">
                        <h2 class="section-title">Text Size</h2>
                        <div class="option">
                            <div class="option-info">
                                <h3>Display Font Size</h3>
                                <p>Control the size of text elements across the application</p>
                            </div>
                            <div class="option-control">
                                <div class="select-wrapper">
                                    <select id="font-size">
                                        <option value="small">Small</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="large">Large</option>
                                        <option value="x-large">Extra Large</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:18px">
                        <button class="btn btn-primary" type="submit">Save Settings</button>
                        <button class="btn btn-danger" id="logout-btn" type="button">Logout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const initialActiveTab = "<?php echo $activeTab; ?>";
        const tabs = document.querySelectorAll('.tab');
        const pages = document.querySelectorAll('.page');

        function switchTab(pageId) {
            tabs.forEach(tab => tab.classList.remove('active'));
            pages.forEach(page => page.classList.remove('active'));

            const targetTab = document.querySelector(`.tab[data-page="${pageId}"]`);
            const targetPage = document.getElementById(`${pageId}-page`);

            if (targetTab) targetTab.classList.add('active');
            if (targetPage) targetPage.classList.add('active');

            const url = new URL(window.location);
            url.searchParams.set('tab', pageId);
            history.pushState(null, '', url.toString());
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const pageId = this.getAttribute('data-page');
                switchTab(pageId);
            });
        });
        
        switchTab(initialActiveTab);

        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('change', function() {
                document.getElementById('settings-form').submit();
            });
        }

        const fontSizeSelect = document.getElementById('font-size');
        if (fontSizeSelect) {
            function applyFontSize(size) {
                document.body.style.fontSize = size === 'small' ? '14px' : 
                                               size === 'medium' ? '16px' : 
                                               size === 'large' ? '18px' : '20px';
            }

            fontSizeSelect.addEventListener('change', function() {
                applyFontSize(this.value);
                localStorage.setItem('fontSize', this.value);
            });

            const savedFontSize = localStorage.getItem('fontSize');
            if (savedFontSize) {
                fontSizeSelect.value = savedFontSize;
                applyFontSize(savedFontSize);
            }
        }

        const languageSelect = document.getElementById('language');
        if (languageSelect) {
            languageSelect.addEventListener('change', function() {
                alert(`Language changed to ${this.options[this.selectedIndex].text}. Please click 'Save Settings' to apply changes.`);
            });
        }

        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'logout.php';
                }
            });
        }

        const editProfileBtn = document.getElementById('edit-profile-btn');
        const cancelEditBtn = document.getElementById('cancel-edit-btn');
        const profileView = document.getElementById('profile-view');
        const profileEditForm = document.getElementById('profile-edit-form');
        
        if (editProfileBtn && cancelEditBtn && profileView && profileEditForm) {
            const isErrorOrSuccess = document.querySelector('.message-error') || document.querySelector('.message-success');

            if (isErrorOrSuccess && initialActiveTab === 'profile') {
                profileView.classList.add('hidden');
                profileEditForm.classList.remove('hidden');
            }

            editProfileBtn.addEventListener('click', function() {
                profileView.classList.add('hidden');
                profileEditForm.classList.remove('hidden');
            });

            cancelEditBtn.addEventListener('click', function() {
                profileEditForm.classList.add('hidden');
                profileView.classList.remove('hidden');
                
                document.getElementById('new_password').value = '';
                document.getElementById('current_password').value = '';
                
                const fileInput = document.getElementById('profile_picture_input');
                if (fileInput) fileInput.value = '';
                
                const removeCheckbox = document.getElementById('remove_profile_picture');
                if (removeCheckbox) removeCheckbox.checked = false;
                
                resetPreview();
            });
        }

        const choosePictureBtn = document.getElementById('choose-picture-btn');
        const profilePictureInput = document.getElementById('profile_picture_input');
        const previewAvatar = document.getElementById('preview-avatar');
        const previewImage = document.getElementById('preview-image');
        const previewInitial = document.getElementById('preview-initial');
        const removeCheckbox = document.getElementById('remove_profile_picture');

        const originalPreviewHTML = previewAvatar ? previewAvatar.innerHTML : '';

        function resetPreview() {
            if (previewAvatar) {
                previewAvatar.innerHTML = originalPreviewHTML;
            }
        }

        if (choosePictureBtn && profilePictureInput) {
            choosePictureBtn.addEventListener('click', function() {
                profilePictureInput.click();
            });

            profilePictureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                if (file) {
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                        this.value = '';
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB limit.');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewAvatar.innerHTML = `<img src="${event.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
                        
                        if (removeCheckbox) {
                            removeCheckbox.checked = false;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (removeCheckbox) {
            removeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    if (profilePictureInput) {
                        profilePictureInput.value = '';
                    }
                    
                    const userInitial = "<?php echo htmlspecialchars($userData['avatar_initial']); ?>";
                    previewAvatar.innerHTML = `<span class="preview-avatar-initial">${userInitial}</span>`;
                } else {
                    resetPreview();
                }
            });
        }

        setTimeout(function() {
            const messages = document.querySelectorAll('.message-success, .message-error');
            messages.forEach(function(msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(function() {
                    msg.remove();
                }, 500);
            });
        }, 5000);
    </script>

    <?php include "footer.html"; ?>
</body>
</html>