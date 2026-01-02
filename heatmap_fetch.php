<?php
require_once "config.php";

$db = (new Database())->getConnection();

if (!isset($_GET['barangay'])) {
    echo "<p>Invalid barangay name provided.</p>";
    exit;
}

$barangay = $_GET['barangay'];

$stmt = $db->prepare("
    SELECT 
        i.incident_id AS id, 
        i.incident_type, 
        LEFT(i.description, 50) AS description_snippet, 
        i.status, 
        DATE_FORMAT(i.submitted_at, '%Y-%m-%d') AS submitted_date
    FROM incidents i
    JOIN barangay_stats b ON i.barangay_id = b.barangay_id
    WHERE b.barangay_name = ?
    ORDER BY i.submitted_at DESC
");
$stmt->execute([$barangay]);

$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($incidents) === 0) {
    echo "<p>No incidents found for **{$barangay}**.</p>";
    exit;
}

echo "<table class='incident-table'>
        <thead>
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Snippet</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>";

foreach ($incidents as $row) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['incident_type']}</td>
            <td>" . htmlspecialchars($row['description_snippet']) . "...</td>
            <td>{$row['status']}</td>
            <td>{$row['submitted_date']}</td>
          </tr>";
}

echo "</tbody></table>";

?>