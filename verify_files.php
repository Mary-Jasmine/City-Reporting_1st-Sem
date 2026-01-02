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
    <title>Verify Profile Picture Files</title>
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
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .file-item {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .file-item.exists {
            border-left-color: #28a745;
        }
        .file-item.missing {
            border-left-color: #dc3545;
        }
        .file-item strong {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .file-item small {
            color: #666;
            font-size: 12px;
            line-height: 1.6;
        }
        .status-icon {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
        .status-icon.exists {
            background: #d4edda;
            color: #155724;
        }
        .status-icon.missing {
            background: #f8d7da;
            color: #721c24;
        }
        .summary {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary strong {
            display: block;
            margin-bottom: 8px;
            color: #0c5aa0;
        }
        .code-block {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #333;
            margin-top: 15px;
            word-break: break-all;
            overflow-x: auto;
        }
        .solution {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 25px;
            color: #856404;
            line-height: 1.6;
            font-size: 13px;
        }
        .solution strong {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .solution ol {
            margin-left: 20px;
            margin-top: 10px;
        }
        .solution li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📁 Verify Profile Picture Files</h1>

        <?php
        
        $sql = "SELECT user_id, full_name, profile_picture FROM users WHERE profile_picture IS NOT NULL AND profile_picture != '' ORDER BY user_id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $existCount = 0;
        $missingCount = 0;
        $results = [];
        
        foreach ($users as $user) {
            $filePath = $user['profile_picture'];
            $fileExists = file_exists($filePath);
            
            $results[] = [
                'user_id' => $user['user_id'],
                'name' => $user['full_name'],
                'path' => $filePath,
                'exists' => $fileExists
            ];
            
            if ($fileExists) {
                $existCount++;
            } else {
                $missingCount++;
            }
        }
        
        echo "<div class='summary'>";
        echo "<strong>📊 Summary</strong>";
        echo "Total users with profile pictures: <strong>" . count($results) . "</strong><br>";
        echo "Files found: <strong style='color: #28a745;'>$existCount ✓</strong><br>";
        echo "Files missing: <strong style='color: #dc3545;'>$missingCount ✗</strong>";
        echo "</div>";
        
        if (!empty($results)) {
            echo "<div>";
            foreach ($results as $result) {
                $class = $result['exists'] ? 'exists' : 'missing';
                $status = $result['exists'] ? '✓ EXISTS' : '✗ MISSING';
                
                echo "<div class='file-item $class'>";
                echo "<strong>User ID: {$result['user_id']} - {$result['name']}</strong>";
                echo "<span class='status-icon $class'>$status</span>";
                echo "<small>";
                echo "Path: <code style='background:#fff; padding:2px 5px; border-radius:2px;'>{$result['path']}</code><br>";
                
                if (!$result['exists']) {
                    echo "❌ File not found at this location";
                }
                
                echo "</small>";
                echo "</div>";
            }
            echo "</div>";
        }
        
        if ($missingCount > 0) {
            echo "<div class='solution'>";
            echo "<strong>🔧 Solution:</strong>";
            echo "Files are missing from the server. Here are the possible causes and solutions:<br><br>";
            
            echo "<strong>Cause 1: Profile pictures were in old location</strong><br>";
            echo "If you had a different uploads folder structure before (like /ADMinn/uploads/), the files might still be there.<br>";
            echo "Solution: Check if your profile pictures exist in these locations:<br>";
            echo "<div class='code-block'>";
            echo "Municipal_report/ADMinn/uploads/profile_pictures/<br>";
            echo "Municipal_report/ADMinn/uploads/<br>";
            echo "Any other uploads folder";
            echo "</div><br>";
            
            echo "<strong>Cause 2: Files were never uploaded</strong><br>";
            echo "The paths are in the database but the actual files don't exist.<br>";
            echo "Solution: Upload new profile pictures from the User Profile or Admin Panel.<br><br>";
            
            echo "<strong>✅ Quick Fix:</strong><br>";
            echo "<ol>";
            echo "<li>Go to <strong>Admin Panel → User Management</strong></li>";
            echo "<li>Click <strong>Edit</strong> on User ID 21 (Brgy. Santo Tomas)</li>";
            echo "<li>Click <strong>'Choose Photo'</strong> to upload a new profile picture</li>";
            echo "<li>Save the changes</li>";
            echo "<li>Refresh this page to verify the file exists</li>";
            echo "<li>Check if the picture appears in <strong>User Profile (settings.php)</strong></li>";
            echo "</ol>";
            echo "</div>";
        } else {
            echo "<div class='solution' style='background: #d4edda; border-left-color: #28a745; color: #155724;'>";
            echo "<strong>✓ All Files Found!</strong>";
            echo "All profile pictures are correctly stored and accessible.";
            echo "</div>";
        }
        
        ?>
    </div>
</body>
</html>