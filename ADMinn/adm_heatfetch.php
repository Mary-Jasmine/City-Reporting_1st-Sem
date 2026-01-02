<?php
require_once "config.php";
redirectIfNotLogged();

$db = (new Database())->getConnection();

if (!isset($_GET['barangay'])) {
    echo "<p style='color: #ef4444; padding: 20px;'>Invalid barangay name provided.</p>";
    exit;
}

$barangay = $_GET['barangay'];

$stmt = $db->prepare("
    SELECT 
        i.incident_id,
        i.incident_type,
        i.description,
        i.location,
        i.status,
        DATE_FORMAT(i.submitted_at, '%b %d, %Y %h:%i %p') AS submitted_date,
        DATE_FORMAT(i.resolved_at, '%b %d, %Y %h:%i %p') AS resolved_date,
        u.full_name AS reporter_name,
        u.contact_number,
        GROUP_CONCAT(
            CONCAT(f.file_name, '|', f.file_path) 
            SEPARATOR ';;'
        ) AS files
    FROM incidents i
    JOIN barangay_stats b ON i.barangay_id = b.barangay_id
    LEFT JOIN users u ON i.user_id = u.user_id
    LEFT JOIN incident_files f ON i.incident_id = f.incident_id
    WHERE b.barangay_name = ?
    GROUP BY i.incident_id
    ORDER BY i.submitted_at DESC
");
$stmt->execute([$barangay]);
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($incidents) === 0) {
    echo "<div style='text-align: center; padding: 40px; color: #6b7280;'>
            <svg style='width: 64px; height: 64px; margin: 0 auto 16px; opacity: 0.3;' fill='currentColor' viewBox='0 0 20 20'>
                <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'/>
            </svg>
            <p style='font-size: 18px; font-weight: 600; margin-bottom: 8px;'>No Incidents Found</p>
            <p style='font-size: 14px;'>No incidents have been reported in <strong>{$barangay}</strong>.</p>
          </div>";
    exit;
}
function getStatusColor($status) {
    switch ($status) {
        case 'resolved': return ['bg' => '#d1fae5', 'text' => '#065f46'];
        case 'in-progress': return ['bg' => '#fef3c7', 'text' => '#92400e'];
        case 'pending': return ['bg' => '#fee2e2', 'text' => '#991b1b'];
        case 'rejected': return ['bg' => '#f3f4f6', 'text' => '#374151'];
        default: return ['bg' => '#e5e7eb', 'text' => '#1f2937'];
    }
}

function getTypeIcon($type) {

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Details - <?= htmlspecialchars($barangay) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: transparent;
            padding: 0;
        }

        .header {
            background: linear-gradient(135deg, #b72a22 0%, #991f19 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(183, 42, 34, 0.2);
        }

        .header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .incidents-container {
            display: grid;
            gap: 16px;
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 4px;
        }

        .incidents-container::-webkit-scrollbar {
            width: 8px;
        }

        .incidents-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .incidents-container::-webkit-scrollbar-thumb {
            background: #b72a22;
            border-radius: 4px;
        }

        .incidents-container::-webkit-scrollbar-thumb:hover {
            background: #991f19;
        }

        .incident-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .incident-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .incident-header {
            background: #f9fafb;
            padding: 16px 20px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .incident-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .type-icon {
            font-size: 28px;
        }

        .type-text {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            text-transform: capitalize;
        }

        .incident-id {
            font-size: 12px;
            color: #6b7280;
            background: white;
            padding: 4px 12px;
            border-radius: 12px;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .incident-body {
            padding: 24px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            color: #111827;
            font-weight: 500;
        }

        .description-section {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .description-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }

        .description-text {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
        }

        .attachments-section {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        .attachments-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .attachments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }

        .attachment-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }

        .attachment-card:hover {
            border-color: #b72a22;
            background: #fef2f2;
        }

        .attachment-icon {
            font-size: 36px;
            margin-bottom: 8px;
        }

        .attachment-name {
            font-size: 12px;
            color: #4b5563;
            word-break: break-word;
            margin-bottom: 8px;
        }

        .attachment-button {
            background: #b72a22;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .attachment-button:hover {
            background: #991f19;
        }

        .no-attachments {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
    </style>
</head>
<body>

    <div class="incidents-container">
        <?php foreach ($incidents as $incident): 
            $statusColors = getStatusColor($incident['status']);
            $files = $incident['files'] ? explode(';;', $incident['files']) : [];
        ?>
        <div class="incident-card">
            <div class="incident-header">
                <div class="incident-title">
                    <span class="type-icon"><?= getTypeIcon($incident['incident_type']) ?></span>
                    <div>
                        <div class="type-text"><?= htmlspecialchars($incident['incident_type']) ?></div>
                        <span class="incident-id">ID: #<?= $incident['incident_id'] ?></span>
                    </div>
                </div>
                <span class="status-badge" style="background: <?= $statusColors['bg'] ?>; color: <?= $statusColors['text'] ?>;">
                    <?= htmlspecialchars($incident['status']) ?>
                </span>
            </div>

            <div class="incident-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"> Submitted</span>
                        <span class="info-value"><?= htmlspecialchars($incident['submitted_date']) ?></span>
                    </div>

                    <?php if (!empty($incident['resolved_at']) && !empty($incident['resolved_date'])): ?>
                    <div class="info-item">
                        <span class="info-label">Resolved</span>
                        <span class="info-value"><?= htmlspecialchars($incident['resolved_date']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="info-item">
                        <span class="info-label"> Location</span>
                        <span class="info-value"><?= htmlspecialchars($incident['location']) ?></span>
                    </div>

                    <?php if ($incident['reporter_name']): ?>
                    <div class="info-item">
                        <span class="info-label">Reporter</span>
                        <span class="info-value"><?= htmlspecialchars($incident['reporter_name']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($incident['contact_number']): ?>
                    <div class="info-item">
                        <span class="info-label"> Contact</span>
                        <span class="info-value"><?= htmlspecialchars($incident['contact_number']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($incident['description']): ?>
                <div class="description-section">
                    <h3> Description</h3>
                    <p class="description-text"><?= nl2br(htmlspecialchars($incident['description'])) ?></p>
                </div>
                <?php endif; ?>

                <div class="attachments-section">
                    <h3 class="attachments-title">
                        📎 Attachments (<?= count($files) ?>)
                    </h3>
                    
                    <?php if (count($files) > 0): ?>
                    <div class="attachments-grid">
                        <?php foreach ($files as $file): 
                            if (empty($file)) continue;
                            list($fileName, $filePath) = explode('|', $file);
                            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $fileIcon = $isImage ? : '📄';
                        ?>
                        <div class="attachment-card">
                            <div class="attachment-icon"><?= $fileIcon ?></div>
                            <div class="attachment-name" title="<?= htmlspecialchars($fileName) ?>">
                                <?= htmlspecialchars(strlen($fileName) > 20 ? substr($fileName, 0, 17) . '...' : $fileName) ?>
                            </div>
                            <button class="attachment-button" onclick="window.open('<?= htmlspecialchars($filePath) ?>', '_blank')">
                                View
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="no-attachments">No attachments available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>