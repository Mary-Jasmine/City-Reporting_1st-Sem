<?php
header('Content-Type: application/json');

require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$db = $database->getConnection();

$response = ['error' => 'Invalid request.', 'incident' => null, 'files' => []];

if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_SESSION['user_id'])) {
    $incident_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];
    
    try {
        $incident_query = "SELECT 
                                i.*, 
                                b.barangay_name
                           FROM 
                                incidents i
                           LEFT JOIN 
                                barangay_stats b ON i.barangay_id = b.barangay_id
                           WHERE 
                                i.incident_id = :incident_id AND i.user_id = :user_id
                           LIMIT 1";
                           
        $incident_stmt = $db->prepare($incident_query);
        $incident_stmt->bindParam(':incident_id', $incident_id);
        $incident_stmt->bindParam(':user_id', $user_id); 
        $incident_stmt->execute();
        $incident = $incident_stmt->fetch(PDO::FETCH_ASSOC);

        if ($incident) {
            $files_query = "SELECT file_name, file_path FROM incident_files WHERE incident_id = :incident_id";
            $files_stmt = $db->prepare($files_query);
            $files_stmt->bindParam(':incident_id', $incident_id);
            $files_stmt->execute();
            $files = $files_stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'incident' => $incident,
                'files' => $files,
                'barangay_name' => $incident['barangay_name']
            ];
            if (isset($response['error'])) {
                unset($response['error']);
            }

        } else {
            $response['error'] = 'Incident not found or unauthorized access.';
        }

    } catch(PDOException $e) {
        $response['error'] = 'Database error: ' . $e->getMessage();
    }
} else if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'User not authenticated.';
}

echo json_encode($response);
?>