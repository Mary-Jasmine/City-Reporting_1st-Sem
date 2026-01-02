<?php
session_start();

$host = '127.0.0.1:3306';
$dbname = 'updatcollab';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'get_incidents') {
        $status = $_POST['status'] ?? 'pending';
        $search = $_POST['search'] ?? '';
        $type = $_POST['type'] ?? '';
        $location = $_POST['location'] ?? '';
        $date = $_POST['date'] ?? '';
        
        $sql = "SELECT i.incident_id, i.incident_type, i.description, i.location, i.status, 
                       i.submitted_at, u.full_name, b.barangay_name
                FROM incidents i 
                LEFT JOIN users u ON i.user_id = u.user_id 
                LEFT JOIN barangay_stats b ON i.barangay_id = b.barangay_id 
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($status)) {
            $statuses = explode(',', $status);
            if (count($statuses) > 1) {
                $placeholders = str_repeat('?,', count($statuses) - 1) . '?';
                $sql .= " AND i.status IN ($placeholders)";
                foreach ($statuses as $s) {
                    $params[] = trim($s);
                    $types .= "s";
                }
            } else {
                $sql .= " AND i.status = ?";
                $params[] = $status;
                $types .= "s";
            }
        }
        
        if (!empty($search)) {
            $sql .= " AND (i.incident_id LIKE ? OR i.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }
        
        if (!empty($type)) {
            $sql .= " AND i.incident_type = ?";
            $params[] = $type;
            $types .= "s";
        }
        
        if (!empty($location)) {
            $sql .= " AND b.barangay_name = ?";
            $params[] = $location;
            $types .= "s";
        }
        
        if (!empty($date)) {
            $sql .= " AND DATE(i.submitted_at) = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        $sql .= " ORDER BY i.submitted_at DESC LIMIT 100";
        
        $stmt = $conn->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $stmt->execute();
        $result = $stmt->get_result();
        
        $incidents = [];
        while ($row = $result->fetch_assoc()) {
            $incidents[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $incidents]);
        exit;
    }
    
    if ($_POST['action'] === 'update_status') {
        $incident_id = $_POST['incident_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? '';
        
        $sql = "UPDATE incidents SET status = ? WHERE incident_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $incident_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_details') {
        $incident_id = $_POST['incident_id'] ?? 0;
        
        $sql = "SELECT i.*, u.full_name, u.email, u.contact_number, b.barangay_name,
                       GROUP_CONCAT(f.file_path) as attachments
                FROM incidents i 
                LEFT JOIN users u ON i.user_id = u.user_id 
                LEFT JOIN barangay_stats b ON i.barangay_id = b.barangay_id
                LEFT JOIN incident_files f ON i.incident_id = f.incident_id
                WHERE i.incident_id = ?
                GROUP BY i.incident_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $incident_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

$pending_count = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'pending'")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status IN ('resolved', 'in-progress')")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'rejected'")->fetch_assoc()['count'];

$types_result = $conn->query("SELECT DISTINCT incident_type FROM incidents ORDER BY incident_type");
$locations_result = $conn->query("SELECT DISTINCT barangay_name FROM barangay_stats ORDER BY barangay_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>

      
    body.dashboard-body{
        background: 
            linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)),
            url('chujjrch.jpeg') ;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;

            color: #1a1a1a;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            margin-top: 3%;
            border-radius: 0%;
            background-color: whitesmoke;

        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .under-header{
            margin-top: 2%;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #6b7280;
            font-size: 14px;
        }
        
        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .filter-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            transition: all 0.2s;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.1);
        }
        
        .btn-reset {
            padding: 10px 20px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-reset:hover {
            background: #e5e7eb;
        }
        
        .submissions-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        
        .tabs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .tabs-row {
            display: flex;
            gap: 10px;
        }
        
        .tab {
            padding: 10px 18px;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .tab.active {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }
        
        .tab-count {
            margin-left: 6px;
            opacity: 0.7;
        }
        
        .submissions-info {
            font-size: 13px;
            color: #6b7280;
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead th {
            background: #f9fafb;
            padding: 14px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        tbody td {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
        }
        
        tbody tr:hover {
            background: #fafafa;
        }
        
        .priority-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .priority-critical {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .priority-medium {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .priority-low {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-resolved, .status-in-progress {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-review {
            background: #065701ff;
            color: #ffffffff;
            border: 1px solid #e5e7eb;
        }
        
        .btn-review:hover {
            background: #4a659cff;
        }
        
        .btn-edit {
            background: white;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }
        
        .btn-edit:hover {
            background: #f9fafb;
        }
        
        .detail-panel {
            position: fixed;
            right: 0;
            top: 0;
            width: 450px;
            height: 100vh;
            background: white;
            box-shadow: -4px 0 12px rgba(0,0,0,0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .detail-panel.active {
            transform: translateX(0);
        }
        
        .detail-header {
            padding: 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
        }
        
        .btn-close {
            background: #f3f4f6;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            color: #6b7280;
        }
        
        .detail-content {
            padding: 25px;
        }
        
        .detail-section {
            margin-bottom: 25px;
        }
        
        .detail-section h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        
        .detail-section p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        .reporter-info {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .reporter-avatar {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }
        
        .reporter-details h5 {
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        
        .reporter-details p {
            font-size: 13px;
            color: #6b7280;
        }
        
        .attachments-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .attachment-thumb {
            aspect-ratio: 1;
            border-radius: 8px;
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #9ca3af;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .attachment-thumb:hover {
            border-color: #b91c1c;
            background: #fef2f2;
            color: #b91c1c;
        }
        
        .action-buttons-panel {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }
        
        .btn-approve {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        
        .btn-reject {
            flex: 1;
            padding: 12px;
            background: white;
            color: #b91c1c;
            border: 2px solid #fecaca;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-reject:hover {
            background: #fef2f2;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }
        
        .overlay.active {
            display: block;
        }
        
        @media (max-width: 1200px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .detail-panel {
                width: 100%;
            }
        }
    </style>
</head>
<body class="dashboard-body"></body>
    <?php include 'adm_header.php'; ?>

  <div class="container">
    <div class="under-header">
        <div class="under-header">
            <h1>Incident Management</h1> <br>   
            <p>Review and manage incident submissions from the community</p>
        </div>
        <br><br>
        <div class="filters-section">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" id="searchInput" placeholder="Search by ID, description...">
                </div>
                <div class="filter-group">
                    <label>Type</label>
                    <select id="typeFilter">
                        <option value="">All Types</option>
                        <?php while ($type = $types_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($type['incident_type']) ?>">
                                <?= htmlspecialchars(ucfirst($type['incident_type'])) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Location</label>
                    <select id="locationFilter">
                        <option value="">All Locations</option>
                        <?php while ($loc = $locations_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($loc['barangay_name']) ?>">
                                <?= htmlspecialchars($loc['barangay_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date</label>
                    <input type="date" id="dateFilter">
                </div>
                <button class="btn-reset" style="background-color: #00704cff; color: white; font-weight: %;" onclick="resetFilters()">Reset Filters</button>
            </div>
        </div>
        
        <div class="submissions-card">
            <div class="tabs-header">
                <div class="tabs-row">
                    <div class="tab active" data-status="pending">
                        Pending <span class="tab-count">(<?= $pending_count ?>)</span>
                    </div>
                    <div class="tab" data-status="in-progress,resolved">
                        Approved <span class="tab-count">(<?= $approved_count ?>)</span>
                    </div>
                    <div class="tab" data-status="rejected">
                        Rejected <span class="tab-count">(<?= $rejected_count ?>)</span>
                    </div>
                </div>
                <p class="submissions-info">Manage user-generated content before publication</p>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Incident ID</th>
                            <th>Subject</th>
                            <th>Submitted By</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="incidentsTableBody">
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                                Loading incidents...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="overlay" id="overlay" onclick="closeDetailPanel()"></div>
    
    <div class="detail-panel" id="detailPanel">
        <div class="detail-header">
            <h3>Submission Details</h3>
            <button class="btn-close" onclick="closeDetailPanel()">×</button>
        </div>
        <div class="detail-content" id="detailContent">
            <p style="color:#9ca3af;text-align:center;padding:40px;">Select an incident to view details</p>
        </div>
    </div>
    </div>
    
    <script>
        let currentStatus = 'pending';
        let currentIncidentId = null;
        
        function loadIncidents() {
            const search = document.getElementById('searchInput').value;
            const type = document.getElementById('typeFilter').value;
            const location = document.getElementById('locationFilter').value;
            const date = document.getElementById('dateFilter').value;
            
            fetch('admin_incidents.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_incidents&status=${currentStatus}&search=${search}&type=${type}&location=${location}&date=${date}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderIncidents(data.data);
                }
            });
        }
        
        // Render incidents
        function renderIncidents(incidents) {
            const tbody = document.getElementById('incidentsTableBody');
            
            if (incidents.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">No incidents found</td></tr>';
                return;
            }
            
            tbody.innerHTML = incidents.map(inc => `
                <tr>
                    <td><strong>INC-${String(inc.incident_id).padStart(4, '0')}</strong></td>
                    <td>${escapeHtml(inc.incident_type)}</td>
                    <td>${escapeHtml(inc.full_name || 'Unknown')}</td>
                    <td>${new Date(inc.submitted_at).toLocaleDateString()}</td>
                    <td>${escapeHtml(inc.barangay_name || inc.location || 'N/A')}</td>
                    <td><span class="status-badge status-${inc.status}">${inc.status.charAt(0).toUpperCase() + inc.status.slice(1)}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-review" onclick="viewDetails(${inc.incident_id})">Review</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        function viewDetails(incidentId) {
            currentIncidentId = incidentId;
            
            fetch('admin_incidents.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_details&incident_id=${incidentId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderDetails(data.data);
                    openDetailPanel();
                }
            });
        }
        
        function renderDetails(incident) {
            const initials = (incident.full_name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2);
            const attachments = incident.attachments ? incident.attachments.split(',') : [];
            
            document.getElementById('detailContent').innerHTML = `
                <div class="detail-section">
                    <h4>Type</h4>
                    <p>${escapeHtml(incident.incident_type)}</p>
                </div>
                
                <div class="detail-section">
                    <h4>Description</h4>
                    <p>${escapeHtml(incident.description || 'No description provided')}</p>
                </div>
                
                <div class="detail-section">
                    <h4>Location</h4>
                    <p>${escapeHtml(incident.location || 'No location specified')}</p>
                </div>
                
                <div class="detail-section">
                    <h4>Reporter Information</h4>
                    <div class="reporter-info">
                        <div class="reporter-avatar">${initials}</div>
                        <div class="reporter-details">
                            <h5>${escapeHtml(incident.full_name || 'Unknown User')}</h5>
                            <p>${escapeHtml(incident.email || 'No email')}</p>
                            <p>${escapeHtml(incident.contact_number || 'No contact')}</p>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Incident Details</h4>
                    <p><strong>ID:</strong> INC-${String(incident.incident_id).padStart(4, '0')}</p>
                    <p><strong>Reported:</strong> ${new Date(incident.submitted_at).toLocaleString()}</p>
                    <p><strong>Barangay:</strong> ${escapeHtml(incident.barangay_name || 'N/A')}</p>
                    <p><strong>Status:</strong> <span class="status-badge status-${incident.status}">${incident.status.charAt(0).toUpperCase() + incident.status.slice(1)}</span></p>
                </div>
                
                ${attachments.length > 0 ? `
                <div class="detail-section">
                    <h4>Attachments</h4>
                    <div class="attachments-grid">
                        ${attachments.slice(0, 3).map(() => '<div class="attachment-thumb">View</div>').join('')}
                    </div>
                </div>
                ` : ''}
                
                ${incident.status === 'pending' ? `
                <div class="action-buttons-panel">
                    <button class="btn-reject" onclick="updateStatus('rejected')">Reject</button>
                    <button class="btn-approve" onclick="updateStatus('resolved')">Approve</button>
                </div>
                ` : ''}
            `;
        }
        
        function updateStatus(newStatus) {
            if (!confirm(`Are you sure you want to ${newStatus} this incident?`)) return;
            
            fetch('admin_incidents.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=update_status&incident_id=${currentIncidentId}&new_status=${newStatus}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`Incident ${newStatus} successfully!`);
                    closeDetailPanel();
                    loadIncidents();
                }
            });
        }
        
        function openDetailPanel() {
            document.getElementById('detailPanel').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        }
        
        function closeDetailPanel() {
            document.getElementById('detailPanel').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        }
        
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentStatus = this.getAttribute('data-status');
                loadIncidents();
            });
        });
        
        document.getElementById('searchInput').addEventListener('input', debounce(loadIncidents, 500));
        document.getElementById('typeFilter').addEventListener('change', loadIncidents);
        document.getElementById('locationFilter').addEventListener('change', loadIncidents);
        document.getElementById('dateFilter').addEventListener('change', loadIncidents);
        
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('locationFilter').value = '';
            document.getElementById('dateFilter').value = '';
            loadIncidents();
        }
        
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadIncidents();
        });
    </script>
</body>
</html>
<br><br>
<?php include 'footer.html' ?>