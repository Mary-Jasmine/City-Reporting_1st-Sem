<?php
require_once "config.php";

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Profile Picture Paths</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 700px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-box {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status-box.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .status-box.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .status-box.info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #0c5aa0;
        }
        .status-box strong {
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
        }
        .result-item {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 13px;
        }
        .result-item.fixed {
            border-left-color: #28a745;
        }
        .result-item.skipped {
            border-left-color: #ffc107;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .next-steps {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 25px;
            font-size: 13px;
            color: #856404;
            line-height: 1.6;
        }
        .next-steps strong {
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Profile Picture Paths</h1>
        <p class="subtitle">Updating database paths to use unified location</p>

        <?php
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_paths'])) {
            
            $sql = "SELECT user_id, profile_picture FROM users WHERE profile_picture IS NOT NULL AND profile_picture != ''";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $totalUsers = count($users);
            $fixedCount = 0;
            $skippedCount = 0;
            $results = [];
            
            foreach ($users as $user) {
                $oldPath = $user['profile_picture'];
                $userId = $user['user_id'];
                
                $newPath = trim($oldPath, '/');
                $newPath = str_replace('../', '', $newPath);
                $newPath = str_replace('\\', '/', $newPath);
                
                if (strpos($newPath, 'uploads/') === false) {
                    $newPath = 'uploads/' . $newPath;
                }
                
                if ($newPath !== $oldPath) {
                    $updateSql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    
                    if ($updateStmt->execute([$newPath, $userId])) {
                        $fixedCount++;
                        $results[] = [
                            'user_id' => $userId,
                            'old_path' => $oldPath,
                            'new_path' => $newPath,
                            'status' => 'fixed'
                        ];
                    }
                } else {
                    $skippedCount++;
                    $results[] = [
                        'user_id' => $userId,
                        'path' => $oldPath,
                        'status' => 'skipped'
                    ];
                }
            }
            
            echo "<div class='status-box success'>";
            echo "<strong>✓ Path Fix Complete!</strong>";
            echo "Total users processed: <strong>$totalUsers</strong><br>";
            echo "Paths fixed: <strong>$fixedCount</strong><br>";
            echo "Paths already correct: <strong>$skippedCount</strong>";
            echo "</div>";
            
            if (!empty($results)) {
                echo "<div style='margin-top: 20px;'>";
                echo "<strong style='display: block; margin-bottom: 10px;'>📝 Details:</strong>";
                
                foreach ($results as $result) {
                    $resultClass = $result['status'] === 'fixed' ? 'fixed' : 'skipped';
                    echo "<div class='result-item $resultClass'>";
                    echo "<strong>User ID: {$result['user_id']}</strong>";
                    
                    if ($result['status'] === 'fixed') {
                        echo "Old: <code style='background:#f5f5f5; padding:2px 5px; border-radius:3px;'>{$result['old_path']}</code><br>";
                        echo "New: <code style='background:#e8f5e9; padding:2px 5px; border-radius:3px;'>{$result['new_path']}</code>";
                    } else {
                        echo "Path: <code style='background:#f5f5f5; padding:2px 5px; border-radius:3px;'>{$result['path']}</code> (Already correct)";
                    }
                    
                    echo "</div>";
                }
                
                echo "</div>";
            }
            
            echo "<div class='next-steps'>";
            echo "<strong>✅ Next Steps:</strong>";
            echo "1. Make sure both api_users.php files are uploaded (root & /ADMinn)<br>";
            echo "2. Test profile picture upload from <strong>Admin Panel</strong><br>";
            echo "3. Check if it appears in <strong>User Profile (settings.php)</strong><br>";
            echo "4. Upload a new picture from <strong>User Profile</strong><br>";
            echo "5. Check if it appears in <strong>Admin Panel</strong><br>";
            echo "6. Delete this file (fix_paths.php) when done";
            echo "</div>";
            
        } else {
            
            echo "<div class='status-box info'>";
            echo "<strong>⚠️ This will fix all profile picture paths in your database</strong>";
            echo "Converting all paths to use the unified uploads folder location (uploads/profile_pictures/)<br>";
            echo "This ensures profile pictures work from both Admin and User panels.";
            echo "</div>";
            
            echo "<form method='POST'>";
            echo "<input type='hidden' name='fix_paths' value='1'>";
            echo "<button type='submit' class='btn'>🔧 Start Fixing Paths</button>";
            echo "</form>";
            
        }
        
        ?>
    </div>
</body>
</html>