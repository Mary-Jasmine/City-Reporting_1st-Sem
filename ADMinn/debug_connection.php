<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Diagnostic Tool</h1>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5} .box{background:white;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #4CAF50} .error{border-left-color:#f44336} pre{background:#f9f9f9;padding:10px;overflow:auto}</style>";

echo "<div class='box'>";
echo "<h2>Test 1: Check config.php</h2>";
if (file_exists('config.php')) {
    echo "✅ config.php EXISTS in current directory<br>";
    echo "Path: " . realpath('config.php') . "<br>";
} elseif (file_exists('../config.php')) {
    echo "✅ config.php EXISTS in parent directory<br>";
    echo "Path: " . realpath('../config.php') . "<br>";
} else {
    echo "❌ config.php NOT FOUND<br>";
    echo "Current directory: " . getcwd() . "<br>";
    echo "Files in current directory: " . implode(', ', scandir('.')) . "<br>";
}
echo "</div>";

echo "<div class='box'>";
echo "<h2>Test 2: Load config.php</h2>";
try {
    if (file_exists('config.php')) {
        require_once 'config.php';
    } elseif (file_exists('../config.php')) {
        require_once '../config.php';
    }
    echo "✅ config.php loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading config.php: " . $e->getMessage() . "<br>";
}
echo "</div>";

echo "<div class='box'>";
echo "<h2>Test 3: Check Database Class</h2>";
if (class_exists('Database')) {
    echo "✅ Database class EXISTS<br>";
    try {
        $db = new Database();
        echo "✅ Database object created<br>";
        $conn = $db->getConnection();
        echo "✅ Database connection established<br>";
        echo "Connection type: " . get_class($conn) . "<br>";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Database class NOT FOUND<br>";
}
echo "</div>";

if (isset($conn)) {
    echo "<div class='box'>";
    echo "<h2>Test 4: Check Users Table</h2>";
    try {
        $stmt = $conn->query("SHOW TABLES LIKE 'users'");
        $result = $stmt->fetch();
        if ($result) {
            echo "✅ 'users' table EXISTS<br>";
            $stmt = $conn->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Table Structure:</h3><pre>";
            foreach ($columns as $col) {
                echo $col['Field'] . " (" . $col['Type'] . ")\n";
            }
            echo "</pre>";
        } else {
            echo "❌ 'users' table NOT FOUND<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>Test 5: Count Users</h2>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Total users in database: <strong>" . $count['total'] . "</strong><br>";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>Test 6: Sample User Data</h2>";
    try {
        $stmt = $conn->query("SELECT * FROM users LIMIT 3");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "✅ Retrieved " . count($users) . " users<br>";
            echo "<h3>Sample Data:</h3>";
            echo "<pre>" . print_r($users, true) . "</pre>";
        } else {
            echo "⚠️ No users found in database<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>Test 7: Check Barangay Stats Table</h2>";
    try {
        $stmt = $conn->query("SHOW TABLES LIKE 'barangay_stats'");
        $result = $stmt->fetch();
        if ($result) {
            echo "✅ 'barangay_stats' table EXISTS<br>";
            $stmt = $conn->query("SELECT COUNT(*) as total FROM barangay_stats");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Total barangays: " . $count['total'] . "<br>";
        } else {
            echo "⚠️ 'barangay_stats' table NOT FOUND<br>";
            echo "Note: This means barangay names won't show (only IDs)<br>";
        }
    } catch (Exception $e) {
        echo "⚠️ Error: " . $e->getMessage() . "<br>";
    }
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>Test 8: Test JOIN Query (Users + Barangays)</h2>";
    try {
        $sql = "
            SELECT 
                u.user_id AS id, 
                u.full_name AS name, 
                u.email, 
                u.contact_number, 
                u.barangay_id,
                u.sex, 
                u.age_group, 
                u.created_at
            FROM users u
            LIMIT 3
        ";
        
        $stmt = $conn->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "✅ Query successful!<br>";
            echo "<h3>Result (what API should return):</h3>";
            echo "<pre>" . json_encode(['success' => true, 'data' => $users, 'count' => count($users)], JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "⚠️ Query returned no results<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
    echo "</div>";
}

echo "<div class='box'>";
echo "<h2>Test 9: Check API File</h2>";
$apiPaths = [
    'api/api_users.php',
    '../api/api_users.php',
    'api_users.php'
];

$foundApiPath = null;
foreach ($apiPaths as $path) {
    if (file_exists($path)) {
        echo "✅ Found API file at: <strong>$path</strong><br>";
        echo "Full path: " . realpath($path) . "<br>";
        $foundApiPath = $path;
        break;
    }
}

if (!$foundApiPath) {
    echo "❌ api_users.php NOT FOUND in any expected location<br>";
    echo "Searched: " . implode(', ', $apiPaths) . "<br>";
}
echo "</div>";

if ($foundApiPath) {
    echo "<div class='box'>";
    echo "<h2>Test 10: Test API Endpoint</h2>";
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $currentPath = dirname($_SERVER['PHP_SELF']);
    $apiUrl = $protocol . "://" . $host . $currentPath . "/" . $foundApiPath;
    
    echo "API URL: <a href='$apiUrl' target='_blank'>$apiUrl</a><br>";
    echo "<button onclick=\"testAPI('$apiUrl')\">🔍 Test API Now</button>";
    echo "<div id='apiResult'></div>";
    
    echo "<script>
    function testAPI(url) {
        document.getElementById('apiResult').innerHTML = '<p>Loading...</p>';
        fetch(url)
            .then(response => {
                console.log('Status:', response.status);
                return response.json();
            })
            .then(data => {
                document.getElementById('apiResult').innerHTML = '<h3>API Response:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('apiResult').innerHTML = '<p style=\"color:red\">Error: ' + error.message + '</p>';
            });
    }
    </script>";
    echo "</div>";
}

echo "<div class='box'>";
echo "<h2>📋 Summary & Next Steps</h2>";
echo "<ol>";
echo "<li>Review all tests above to identify any ❌ errors</li>";
echo "<li>If Test 10 works, copy the JSON response and share it</li>";
echo "<li>Make sure the API file path matches what ad_users.php expects</li>";
echo "<li>Check browser console (F12) when loading ad_users.php for errors</li>";
echo "</ol>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='ad_users.php' target='_blank'>→ Open ad_users.php</a></p>";
if ($foundApiPath) {
    echo "<p><a href='$foundApiPath' target='_blank'>→ Test API directly</a></p>";
}
echo "</div>";
?>
