<?php
require_once 'config.php';
redirectIfNotLogged();

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $feedbackId = intval($_POST['feedback_id']);
    $newStatus = $_POST['status'];
    $adminResponse = trim($_POST['admin_response'] ?? '');
    
    $sql = "UPDATE feedback SET status = ?, admin_response = ?, responded_at = NOW(), updated_at = NOW() WHERE feedback_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$newStatus, $adminResponse, $feedbackId]);
    
    header("Location: admin_feedback.php?success=1");
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$sql = "SELECT * FROM feedback WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($categoryFilter) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}

$sql .= " ORDER BY submitted_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    AVG(rating) as avg_rating
    FROM feedback";
$stats = $db->query($statsQuery)->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - Admin</title>
    <style>

        * { box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: #1f2937;
        }
        
        body.dashboard-body {
            background: linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)), url('chujjrch.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .container {
            max-width: 1400px;
            margin: 3% auto 7% auto;
            padding: 40px;
            border-radius: 15px;
            background-color: #f4f4f4;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--red-1);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filters select {
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: var(--radius);
            font-size: 14px;
        }

        .feedback-table {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-top: 1px solid #f3f4f6;
            font-size: 14px;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-reviewed { background: #dbeafe; color: #1e40af; }
        .badge-resolved { background: #d1fae5; color: #065f46; }
        .badge-archived { background: #e5e7eb; color: #4b5563; }

        .category-badge {
            background: #f3f4f6;
            color: #4b5563;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
        }

        .rating-stars {
            color: #ffa500;
            font-size: 14px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-view {
            background: linear-gradient(135deg, #ff5b5b 0%, #c0352e 100%);
            color: white;
        }

        .btn-view:hover {
            background: linear-gradient(135deg, #e74c3c 0%, #a82a24 100%);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            margin: 20px;
            padding: 30px;
            border-radius: var(--radius);
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }

        .modal-close {
            cursor: pointer;
            font-size: 28px;
            color: var(--muted);
            background: none;
            border: none;
            width: 32px;
            height: 32px;
        }

        .modal-close:hover {
            color: #000;
        }

        .detail-group {
            margin-bottom: 20px;
        }

        .detail-label {
            font-weight: 600;
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }

        .btn-cancel {
            background: var(--muted);
            color: white;
        }

        .btn-submit {
            background: linear-gradient(135deg, #ff5b5b 0%, #c0352e 100%);
            color: white;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--muted);
        }

        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="dashboard-body">
    <?php include 'adm_header.php'; ?>
    <br><br>
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            ✓ Feedback status updated successfully!
        </div>
        <?php endif; ?>

        <div class="under-header">
            <h1> Feedback Management</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['pending'] ?? 0 ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['reviewed'] ?? 0 ?></div>
                <div class="stat-label">Reviewed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['resolved'] ?? 0 ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['avg_rating'] ?? 0, 1) ?>⭐</div>
                <div class="stat-label">Avg Rating</div>
            </div>
        </div>

        <div class="filters">
            <select id="statusFilter" onchange="applyFilters()">
                <option value="">All Statuses</option>
                <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="reviewed" <?= $statusFilter == 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                <option value="resolved" <?= $statusFilter == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="archived" <?= $statusFilter == 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>

            <select id="categoryFilter" onchange="applyFilters()">
                <option value="">All Categories</option>
                <option value="general" <?= $categoryFilter == 'general' ? 'selected' : '' ?>>General Feedback</option>
                <option value="bug_report" <?= $categoryFilter == 'bug_report' ? 'selected' : '' ?>>Bug Report</option>
                <option value="feature_request" <?= $categoryFilter == 'feature_request' ? 'selected' : '' ?>>Feature Request</option>
                <option value="service_quality" <?= $categoryFilter == 'service_quality' ? 'selected' : '' ?>>Service Quality</option>
                <option value="complaint" <?= $categoryFilter == 'complaint' ? 'selected' : '' ?>>Complaint</option>
                <option value="praise" <?= $categoryFilter == 'praise' ? 'selected' : '' ?>>Praise</option>
                <option value="suggestion" <?= $categoryFilter == 'suggestion' ? 'selected' : '' ?>>Suggestion</option>
                <option value="other" <?= $categoryFilter == 'other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="feedback-table">
            <?php if (count($feedbacks) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $feedback): ?>
                    <tr>
                        <td>#<?= $feedback['feedback_id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($feedback['name']) ?></strong><br>
                            <small style="color: #6b7280;"><?= htmlspecialchars($feedback['email']) ?></small>
                        </td>
                        <td>
                            <span class="category-badge">
                                <?= ucwords(str_replace('_', ' ', $feedback['category'])) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(substr($feedback['subject'], 0, 50)) ?>...</td>
                        <td>
                            <span class="rating-stars">
                                <?= str_repeat('⭐', $feedback['rating']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $feedback['status'] ?>">
                                <?= ucfirst($feedback['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($feedback['submitted_at'])) ?></td>
                        <td>
                            <button class="btn btn-view" onclick="viewFeedback(<?= $feedback['feedback_id'] ?>)">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <p>No feedback found matching the selected filters.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Feedback Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        const feedbackData = <?= json_encode($feedbacks) ?>;

        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const category = document.getElementById('categoryFilter').value;
            
            let url = 'admin_feedback.php?';
            if (status) url += 'status=' + status + '&';
            if (category) url += 'category=' + category;
            
            window.location.href = url;
        }

        function viewFeedback(id) {
            const feedback = feedbackData.find(f => f.feedback_id == id);
            if (!feedback) return;

            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="detail-group">
                    <div class="detail-label">From</div>
                    <div class="detail-value">
                        <strong>${escapeHtml(feedback.name)}</strong><br>
                        Email: ${escapeHtml(feedback.email)}<br>
                        ${feedback.phone ? 'Phone: ' + escapeHtml(feedback.phone) : ''}
                    </div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Category</div>
                    <div class="detail-value">
                        <span class="category-badge">
                            ${ucwords(feedback.category.replace(/_/g, ' '))}
                        </span>
                    </div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Subject</div>
                    <div class="detail-value">${escapeHtml(feedback.subject)}</div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Message</div>
                    <div class="detail-value" style="white-space: pre-wrap;">${escapeHtml(feedback.message)}</div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Rating</div>
                    <div class="detail-value">
                        <span class="rating-stars">${'⭐'.repeat(feedback.rating)}</span>
                        ${feedback.rating > 0 ? '(' + feedback.rating + '/5)' : 'Not rated'}
                    </div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Submitted</div>
                    <div class="detail-value">${new Date(feedback.submitted_at).toLocaleString()}</div>
                </div>

                ${feedback.admin_response ? `
                <div class="detail-group">
                    <div class="detail-label">Admin Response</div>
                    <div class="detail-value" style="white-space: pre-wrap; background: #f9fafb; padding: 10px; border-radius: 5px;">
                        ${escapeHtml(feedback.admin_response)}
                    </div>
                </div>
                ` : ''}

                <form method="POST" action="">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="feedback_id" value="${feedback.feedback_id}">

                    <div class="form-group">
                        <label>Update Status</label>
                        <select name="status" required>
                            <option value="pending" ${feedback.status == 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="reviewed" ${feedback.status == 'reviewed' ? 'selected' : ''}>Reviewed</option>
                            <option value="resolved" ${feedback.status == 'resolved' ? 'selected' : ''}>Resolved</option>
                            <option value="archived" ${feedback.status == 'archived' ? 'selected' : ''}>Archived</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Admin Response (Optional)</label>
                        <textarea name="admin_response" placeholder="Add your response or notes here...">${escapeHtml(feedback.admin_response || '')}</textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-submit">Update</button>
                    </div>
                </form>
            `;

            document.getElementById('feedbackModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('feedbackModal').classList.remove('show');
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text || '').replace(/[&<>"']/g, m => map[m]);
        }

        function ucwords(str) {
            return str.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        window.onclick = function(event) {
            const modal = document.getElementById('feedbackModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    <?php include 'footer.html'; ?>
</body>
</html>