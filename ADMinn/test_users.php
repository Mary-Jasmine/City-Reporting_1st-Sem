<?php
require_once "../config.php";

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo json_encode([
        "status" => "Database connected successfully",
        "timestamp" => date('Y-m-d H:i:s')
    ]) . "\n\n";
    
    echo "=== TEST 1: Users Table Structure ===\n";
    $stmt = $conn->query("DESCRIBE users");
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($structure, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== TEST 2: Total Users ===\n";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($count, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== TEST 3: Sample Users (Raw) ===\n";
    $stmt = $conn->query("SELECT * FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== TEST 4: Barangay Stats Table ===\n";
    $stmt = $conn->query("SELECT barangay_id, barangay_name FROM barangay_stats LIMIT 5");
    $barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($barangays, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== TEST 5: Users with Barangay Names (JOIN) ===\n";
    $stmt = $conn->query("
        SELECT 
            u.user_id,
            u.full_name,
            u.email,
            u.contact_number,
            u.barangay_id,
            b.barangay_name,
            u.sex,
            u.age_group,
            u.created_at
        FROM users u
        LEFT JOIN barangay_stats b ON u.barangay_id = b.barangay_id
        LIMIT 5
    ");
    $usersWithBarangay = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usersWithBarangay, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== TEST 6: API Response Format ===\n";
    echo json_encode([
        "success" => true,
        "data" => $usersWithBarangay,
        "count" => count($usersWithBarangay)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>