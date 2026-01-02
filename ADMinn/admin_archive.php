<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$type = $_GET['type'] ?? 'incidents';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore'])) {
    $id = intval($_POST['id']);
    
    if ($type === 'incidents') {
        $stmt = $db->prepare("UPDATE incidents SET status = 'resolved' WHERE incident_id = ?");
    } else {
        $stmt = $db->prepare("UPDATE announcements SET status = 'published' WHERE announcements_id = ?");
    }
    
    if ($stmt->execute([$id])) {
        $logStmt = $db->prepare("INSERT INTO system_logs (user_id, action, details) VALUES (?, ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], "restore_{$type}", "Restored item #{$id}"]);
        $message = "Item restored successfully";
        $messageType = "success";
    } else {
        $message = "Failed to restore item";
        $messageType = "error";
    }
}

if ($type === 'incidents') {
    $stmt = $db->prepare("SELECT i.*, u.full_name, b.barangay_name 
                         FROM incidents i 
                         LEFT JOIN users u ON i.user_id = u.user_id 
                         LEFT JOIN barangay_stats b ON i.barangay_id = b.barangay_id 
                         WHERE i.status = 'archived' 
                         ORDER BY i.submitted_at DESC");
} else {
    $stmt = $db->prepare("SELECT a.*, u.full_name 
                         FROM announcements a 
                         LEFT JOIN users u ON a.author_id = u.user_id 
                         WHERE a.status = 'archived' 
                         ORDER BY a.created_at DESC");
}

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive - <?php echo ucfirst($type); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --red-1: #b72a22;
            --red-2: #c7463f;
            --card: #ffffff;
            --bg: #f5f6f8;
            --muted: #6b7280;
            --shadow: 0 8px 24px rgba(16,24,40,0.06);
        }
        
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, sans-serif;
            background: var(--bg);
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .title {
            font-size: 28px;
            font-weight: 800;
        }
        
        .back-btn {
            padding: 10px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            font-weight: 600;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #f1f3f5;
        }
        
        th {
            background: #f9fafb;
            font-weight: 700;
            font-size: 14px;
            color: #6b7280;
        }
        
        .restore-btn {
            padding: 6px 12px;
            background: linear-gradient(180deg, #34b76f, #1db954);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .restore-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #fee2e2;
            color: #991b1b;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }
        
        .notification.show { display: block; }
        .notification.success { border-left: 4px solid #1db954; }
        .notification.error { border-left: 4px solid #e74c3c; }
    </style>
</head>
<body>
    <?php if (isset($message)): ?>
    <div class="notification <?php echo $messageType; ?> show" id="notification">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="header">
            <div class="title">📦 Archived <?php echo ucfirst($type); ?></div>
            <a href="admin_settings.php" class="back-btn">← Back to Settings</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $type === 'incidents' ? 'Type' : 'Title'; ?></th>
                        <th><?php echo $type === 'incidents' ? 'Location' : 'Type'; ?></th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>#<?php echo $type === 'incidents' ? $item['incident_id'] : $item['announcements_id']; ?></td>
                            <td><?php echo htmlspecialchars($type === 'incidents' ? $item['incident_type'] : $item['title']); ?></td>
                            <td><?php echo htmlspecialchars($type === 'incidents' ? $item['barangay_name'] : $item['type']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($type === 'incidents' ? $item['submitted_at'] : $item['created_at'])); ?></td>
                            <td><span class="status-badge">Archived</span></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="id" value="<?php echo $type === 'incidents' ? $item['incident_id'] : $item['announcements_id']; ?>">
                                    <button type="submit" name="restore" class="restore-btn">Restore</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af">No archived items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        setTimeout(() => {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.classList.remove('show');
            }
        }, 3000);
    </script>
</body>
</html>