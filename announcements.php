<?php
include "config.php";

if (!isset($db) || !($db instanceof Database)) {
    $db = new Database();
    $conn = $db->getConnection();
} else {
    $conn = $db->getConnection();
}

if (!$conn) {
    die("Database connection failed.");
}

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

$categoryMap = [
    'Disaster Warnings'      => ['emergency'],
    'Public Works'           => ['announcement'], 
    'Road Closures'          => ['maintenance'],
    'Health Advisories'      => ['announcement', 'alert'], 
    'Emergency Evacuation'   => ['emergency'],
    'Water Interruption'     => ['maintenance'],
];

function fetchAnnouncements($conn, $limit = 6, $types = []) {
    $sql = "SELECT announcements_id, title, content, cover_image, type, priority, published_at 
            FROM announcements 
            WHERE status='published'";
            
    $params = [];
    
    if (!empty($types)) {
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $sql .= " AND type IN ($placeholders)";
        $params = $types;
    }
    
    $sql .= " AND (expires_at IS NULL OR expires_at > NOW())";
    $sql .= " ORDER BY published_at DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT " . intval($limit);
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Announcement query failed: " . $e->getMessage());
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements and Alerts</title>
    <style>
        * {margin: 0;padding: 0;box-sizing: border-box;}
        body.ann-body{
            background: linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)), url('chujjrch.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background-color: #eaeaea;min-height: 100vh;}
        .content {max-width: 1300px;margin: auto;padding: 40px 50px;background-color: white;margin-top: 5%;border-radius: 14px;margin-bottom: 7%}
        .page-title {font-size: 28px;font-weight: 600;color: #333;margin-bottom: 35px;}
        .layout {display: grid;grid-template-columns: 3fr 1.2fr;gap: 30px;}
        .section-title {font-size: 18px;font-weight: 700;margin-bottom: 20px;}
        .announcements-grid {display: grid;grid-template-columns: repeat(2,1fr);gap: 20px;}
        .announcement-card {background-color: #fff;border-radius: 8px;overflow: hidden;box-shadow: 0 2px 4px rgba(0,0,0,0.08);transition: 0.2s;}
        .announcement-card:hover {transform: translateY(-3px);}
        .card-image {width: 100%;height: 160px;background: #ddd;object-fit: cover;}
        .card-image img {width: 100%;height: 100%;object-fit: cover;}
        .card-content {padding: 18px;}
        .card-type {font-size: 12px;text-transform: uppercase;font-weight: bold;color: #555;margin-bottom: 6px;}
        .card-title {font-size: 17px;font-weight: 700;margin-bottom: 10px;}
        .card-description {font-size: 14px;color: #666;line-height: 1.45;margin-bottom: 15px;}
        .card-date {font-size: 12px;color: #777;margin-bottom: 8px;}
        .read-more {font-size: 12px;font-weight: 600;color: #1565c0;text-decoration: none;cursor: pointer;}
        .read-more:hover {text-decoration: underline;}
        .right-panel {position: sticky;top: 20px;}
        .alert-category {display: inline-block;background-color: #c41e3a;color: white;padding: 6px 14px;border-radius: 18px;margin-bottom: 12px;font-size: 12px;font-weight: bold;}
        .category-tags {display: flex;flex-wrap: wrap;gap: 8px;margin-bottom: 20px;}
        .category-tag {background-color: #f4f4f4;padding: 6px 12px;border-radius: 4px;color: #555;font-size: 12px;text-decoration: none;display: inline-block;transition: 0.2s;}
        .category-tag:hover {background-color: #e0e0e0;}
        .alert-item {background-color: #fff;padding: 15px;border-radius: 6px;margin-bottom: 15px;box-shadow: 0 1px 2px rgba(0,0,0,0.08);}
        .alert-urgent {background-color: #d32f2f;color: white;font-size: 10px;font-weight: bold;padding: 3px 8px;border-radius: 10px;margin-left: 6px;}
        .modal {display: none;position: fixed;z-index: 1000;left: 0;top: 0;width: 100%;height: 100%;background-color: rgba(0,0,0,0.8);overflow: auto;}
        .modal-content {background-color: white;margin: 5% auto;padding: 30px;width: 80%;max-width: 600px;border-radius: 10px;position: relative;max-height: 80vh;overflow-y: auto;}
        .close-modal {position: absolute;top: 15px;right: 20px;font-size: 30px;cursor: pointer;color: #888;}
        .close-modal:hover {color: #000;}
        #announcementDetails p {font-size: 16px;color: #444;margin-top: 5px;}
        #viewDescription {padding: 10px;background-color: #f9f9f9;border: 1px solid #eee;border-radius: 4px;}
        .no-image-placeholder {width: 100%;height: 160px;background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);display: flex;align-items: center;justify-content: center;color: #999;font-size: 48px;}
        
        .loading-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 9999;
            display: none;
        }
        .loading-indicator.show {
            display: block;
        }
    </style>
</head>
<body class="ann-body">
<?php include 'header.php'; ?>

<div class="loading-indicator" id="loadingIndicator">Updating announcements...</div>

<div class="content">
    <h1 class="page-title">Announcements and Alerts</h1>
    <div class="layout">
        <div>
            <h2 class="section-title">Recent Municipal Announcements</h2>
            <div class="announcements-grid" id="announcementsContainer">
            <?php
            $announcements = fetchAnnouncements($conn, 6);
            if ($announcements):
                foreach ($announcements as $row):
            ?>
                <div class="announcement-card" 
                     id="announcement-<?= $row['announcements_id']; ?>"
                     data-full-content="<?= htmlspecialchars($row['content']); ?>"
                     data-title="<?= htmlspecialchars($row['title']); ?>"
                     data-date="<?= date("F d, Y", strtotime($row['published_at'])); ?>">
                    <div class="card-image">
                        <?php if (!empty($row['cover_image'])): ?>
                            <img src="<?= htmlspecialchars($row['cover_image']); ?>" alt="<?= htmlspecialchars($row['title']); ?>">
                        <?php else: ?>
                            <div class="no-image-placeholder"></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <div class="card-type"><?= ucfirst(htmlspecialchars($row['type'])); ?></div>
                        <div class="card-title"><?= htmlspecialchars($row['title']); ?></div>
                        <div class="card-description"><?= htmlspecialchars(substr($row['content'], 0, 120)); ?>...</div>
                        <div class="card-date"><?= date("F d, Y", strtotime($row['published_at'])); ?></div>
                        <a href="#" class="read-more" onclick="showAnnouncementModal(<?= $row['announcements_id']; ?>); return false;">Read More</a>
                    </div>
                </div>
            <?php
                endforeach;
            else:
                echo "<p>No announcements found.</p>";
            endif;
            ?>
            </div>
        </div>

        <div class="right-panel">
            <h3 style="margin: 10px 0;">Browse Alert Categories</h3>
            <div class="alert-category">Sort by Category</div>
            <div class="category-tags">
                <?php foreach ($categoryMap as $uiName => $dbTypes): ?>
                    <a href="announcements.php?category=<?= urlencode($uiName); ?>" class="category-tag"><?= htmlspecialchars($uiName); ?></a>
                <?php endforeach; ?>
            </div>

            <h3 style="margin: 20px 0 15px;" id="alertsHeader">Active Safety Alerts</h3>
            <div id="alertsContainer">
            <?php 
            $limit = 5;
            $types = [];
            
            if ($selectedCategory && isset($categoryMap[$selectedCategory])) {
                $mappedTypes = $categoryMap[$selectedCategory];
                $types = $mappedTypes;
                $limit = 0;
                echo '<p style="margin-bottom: 10px;">Showing results for: <strong>' . htmlspecialchars($selectedCategory) . '</strong></p>';
            } 
            
            $alerts = fetchAnnouncements($conn, $limit, $types);
            
            if ($alerts):
                foreach ($alerts as $alert):
            ?>
            <div class="alert-item" 
                 id="alert-<?= $alert['announcements_id']; ?>"
                 data-full-content="<?= htmlspecialchars($alert['content']); ?>"
                 data-title="<?= htmlspecialchars($alert['title']); ?>"
                 data-date="<?= date("F d, Y", strtotime($alert['published_at'])); ?>">
                <strong><?= htmlspecialchars($alert['title']); ?></strong>
                <?php if (strtolower($alert['priority']) == 'urgent'): ?>
                    <span class="alert-urgent">Urgent</span>
                <?php endif; ?>
                <br><br>
                <span><?= htmlspecialchars(substr($alert['content'], 0, 100)); ?>...</span> 
                <a href="#" class="read-more" onclick="showAnnouncementModal(<?= $alert['announcements_id']; ?>); return false;">Read More</a>
                <br><br>
                <strong>Issued:</strong> <?= date("F d, Y", strtotime($alert['published_at'])); ?><br>
                <strong>Source:</strong> Local Government Office
            </div>
            <?php
                endforeach;
            else:
                echo "<p>No alerts found.</p>";
            endif;
            ?>
            </div>
        </div>
    </div>
</div>

<div id="announcementModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h2>Announcement Details</h2>
        <br>
        <div style="margin-top: 20px;" id="announcementDetails">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Title</label>
                <p id="viewTitle" style="padding: 8px 0; font-size: 18px; font-weight: 600;"></p>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Content</label>
                <p id="viewDescription" style="padding: 10px; background-color: #f9f9f9; border: 1px solid #eee; border-radius: 4px; white-space: pre-wrap;"></p>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Published Date</label>
                <p id="viewDate" style="padding: 8px 0; font-size: 14px; color: #666;"></p>
            </div>
            <div style="margin-top: 25px; text-align: right;">
                <button type="button" onclick="closeModal()" style="padding: 10px 20px; background: #c41e3a; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showAnnouncementModal(announcementId) {
        const cardElement = document.getElementById('announcement-' + announcementId) || 
                            document.getElementById('alert-' + announcementId);
        
        if (!cardElement) {
            console.error("Announcement element not found for ID:", announcementId);
            return;
        }

        const title = cardElement.getAttribute('data-title');
        const fullContent = cardElement.getAttribute('data-full-content');
        const date = cardElement.getAttribute('data-date');
        
        document.getElementById('viewTitle').textContent = title;
        document.getElementById('viewDescription').textContent = fullContent;
        document.getElementById('viewDate').textContent = date;
        
        document.getElementById('announcementModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('announcementModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('announcementModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    async function fetchLatestAnnouncements(showIndicator = false) {
        if (isRefreshing) return;
        isRefreshing = true;
        
        const indicator = document.getElementById('loadingIndicator');
        if (showIndicator && indicator) {
            indicator.classList.add('show');
        }

        try {
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category');
            
            let apiUrl = 'api_announcements_public.php?limit=20';
            if (category) {
                apiUrl += '&category=' + encodeURIComponent(category);
            }
            
            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error('Failed to fetch announcements');
            
            const data = await response.json();
            
            if (data.success && data.data) {
                updateAnnouncementsDisplay(data.data);
                console.log('Announcements refreshed successfully');
            }
        } catch (error) {
            console.error('Error fetching announcements:', error);
        } finally {
            isRefreshing = false;
            if (indicator) {
                setTimeout(() => indicator.classList.remove('show'), 1000);
            }
        }
    }

    function updateAnnouncementsDisplay(announcements) {
        const container = document.getElementById('announcementsContainer');
        if (!container) return;
        
        const mainAnnouncements = announcements.slice(0, 6);
        
        let html = '';
        
        if (mainAnnouncements.length === 0) {
            html = '<p>No announcements found.</p>';
        } else {
            mainAnnouncements.forEach(row => {
                const excerpt = row.content.substring(0, 120) + '...';
                const publishDate = new Date(row.published_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                let imageHtml;
                if (row.cover_image) {
                    const imageUrl = row.cover_image + '?t=' + new Date().getTime();
                    imageHtml = `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(row.title)}">`;
                } else {
                    imageHtml = '<div class="no-image-placeholder">📢</div>';
                }
                
                html += `
                    <div class="announcement-card" 
                         id="announcement-${row.id}"
                         data-full-content="${escapeHtml(row.content)}"
                         data-title="${escapeHtml(row.title)}"
                         data-date="${publishDate}">
                        <div class="card-image">
                            ${imageHtml}
                        </div>
                        <div class="card-content">
                            <div class="card-type">${escapeHtml(row.type.charAt(0).toUpperCase() + row.type.slice(1))}</div>
                            <div class="card-title">${escapeHtml(row.title)}</div>
                            <div class="card-description">${escapeHtml(excerpt)}</div>
                            <div class="card-date">${publishDate}</div>
                            <a href="#" class="read-more" onclick="showAnnouncementModal(${row.id}); return false;">Read More</a>
                        </div>
                    </div>
                `;
            });
        }
        
        container.innerHTML = html;
        
        updateAlertsSidebar(announcements.slice(0, 5));
    }

    function updateAlertsSidebar(alerts) {
        const alertsContainer = document.getElementById('alertsContainer');
        if (!alertsContainer) return;
        
        let html = '';
        
        if (alerts.length === 0) {
            html = '<p>No alerts found.</p>';
        } else {
            alerts.forEach(alert => {
                const excerpt = alert.content.substring(0, 100) + '...';
                const publishDate = new Date(alert.published_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                const urgentBadge = alert.priority.toLowerCase() === 'urgent' 
                    ? '<span class="alert-urgent">Urgent</span>' 
                    : '';
                
                html += `
                    <div class="alert-item" 
                         id="alert-${alert.id}"
                         data-full-content="${escapeHtml(alert.content)}"
                         data-title="${escapeHtml(alert.title)}"
                         data-date="${publishDate}">
                        <strong>${escapeHtml(alert.title)}</strong>
                        ${urgentBadge}
                        <br><br>
                        <span>${escapeHtml(excerpt)}</span> 
                        <a href="#" class="read-more" onclick="showAnnouncementModal(${alert.id}); return false;">Read More</a>
                        <br><br>
                        <strong>Issued:</strong> ${publishDate}<br>
                        <strong>Source:</strong> Local Government Office
                    </div>
                `;
            });
        }
        
        alertsContainer.innerHTML = html;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && Date.now() - lastFetchTime > 5000) {
            fetchLatestAnnouncements(true);
            lastFetchTime = Date.now();
        }
    });

    window.addEventListener('load', () => {
        setTimeout(() => {
            fetchLatestAnnouncements(false);
        }, 2000);
    });


</script>
</body>
<?php include 'footer.html'; ?>
</html>