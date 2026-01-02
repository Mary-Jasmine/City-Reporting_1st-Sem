<?php
/**
 * Run this script ONCE to check and fix your database
 * Access it via: http://yourdomain.com/fix_database.php
 * DELETE THIS FILE after running it successfully
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Database Check & Fix Script</h2>";
echo "<pre>";

// Check if 'role' column exists in users table
try {
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    
    if ($stmt->rowCount() == 0) {
        echo "❌ 'role' column NOT found in users table\n";
        echo "Adding 'role' column...\n";
        
        // Add the role column
        $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user' AFTER password");
        
        echo "✅ 'role' column added successfully!\n\n";
    } else {
        echo "✅ 'role' column already exists in users table\n\n";
    }
    
    // Check if there are any admin users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "❌ No admin users found\n";
        echo "Setting first user as admin...\n";
        
        // Set the first user as admin
        $db->exec("UPDATE users SET role = 'admin' LIMIT 1");
        
        echo "✅ First user set as admin\n\n";
    } else {
        echo "✅ Found {$result['count']} admin user(s)\n\n";
    }
    
    // Display all users and their roles
    echo "Current Users:\n";
    echo str_repeat("-", 60) . "\n";
    printf("%-5s %-30s %-20s %-10s\n", "ID", "Name", "Email", "Role");
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->query("SELECT user_id, full_name, email, role FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-5s %-30s %-20s %-10s\n", 
            $row['user_id'], 
            substr($row['full_name'], 0, 30), 
            substr($row['email'], 0, 20),
            $row['role'] ?? 'user'
        );
    }
    
    echo "\n\n✅ Database check completed!\n";
    echo "\n🔥 IMPORTANT: Delete this file (fix_database.php) after running!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Fix</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        h2 { color: #b72a22; }
        pre { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
</body>
</html>