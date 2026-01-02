<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'pending';
    $type = $_GET['type'] ?? '';
    $priority = $_GET['priority'] ?? '';
    $location = $_GET['location'] ?? '';
    $date = $_GET['date'] ?? '';

    $sql = "SELECT id, incident_code, subject, submitted_by, submission_date, priority, status 
            FROM incidents 
            WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($status) && $status !== 'All Statuses') {
        $sql .= " AND status = ?";
        $types .= "s";
        $params[] = $status;
    }

    if (!empty($search)) {
        $searchTerm = "%" . $search . "%";
        $sql .= " AND (incident_code LIKE ? OR subject LIKE ?)";
        $types .= "ss";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($type) && $type !== 'All Types') {
        $sql .= " AND type = ?";
        $types .= "s";
        $params[] = $type;
    }
    
    if (!empty($priority) && $priority !== 'All Priorities') {
        $sql .= " AND priority = ?";
        $types .= "s";
        $params[] = $priority;
    }

    if (!empty($location) && $location !== 'All Locations') {
        $sql .= " AND location = ?";
        $types .= "s";
        $params[] = $location;
    }
    
    if (!empty($date)) {
        $sql .= " AND DATE(submission_date) = ?";
        $types .= "s";
        $params[] = $date;
    }

    $sql .= " ORDER BY submission_date DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $incidents = [];

    while ($row = $result->fetch_assoc()) {
        $incidents[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $incidents]);
    $stmt->close();
    
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? null;
    $new_status = $data['status'] ?? null;

    if ($id === null || $new_status === null) {
        echo json_encode(['success' => false, 'message' => 'Missing incident ID or status']);
        exit;
    }

    $valid_statuses = ['approved', 'rejected'];
    $new_status_lower = strtolower($new_status);

    if (!in_array($new_status_lower, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit;
    }

    $sql = "UPDATE incidents SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status_lower, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
} 
else {
    http_response_code(405); 
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>