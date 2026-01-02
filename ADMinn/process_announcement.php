<?php
require_once 'config.php';
$db = (new Database())->getConnection();

function resp($ok, $msg = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $data));
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['announcements_id']) && $_POST['announcements_id'] !== '' ? intval($_POST['announcements_id']) : null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $type = $_POST['type'] ?? 'announcement';
        $priority = $_POST['priority'] ?? 'medium';
        $status = $_POST['status'] ?? 'draft';
        $published_at = $_POST['published_at'] ? date('Y-m-d H:i:s', strtotime($_POST['published_at'])) : null;
        $expires_at = $_POST['expires_at'] ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;

        if (!empty($_FILES['file']['name'])) {
            $up = $_FILES['file'];
            $allowedImgs = ['image/jpeg','image/png','image/gif','image/webp'];
            $allowedVideos = ['video/mp4','video/webm','video/ogg'];
            $destDir = __DIR__ . '/uploads';
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);

            $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
            $basename = uniqid('media_', true) . '.' . $ext;
            $target = $destDir . '/' . $basename;

            if (!move_uploaded_file($up['tmp_name'], $target)) {
                resp(false, 'Upload failed.');
            }

            $rel = 'uploads/' . $basename;
            if (in_array($up['type'], $allowedImgs)) {
                $content .= "<p><img src=\"{$rel}\" alt=\"\" style=\"max-width:100%;height:auto\"></p>";
            } elseif (in_array($up['type'], $allowedVideos)) {
                $content .= "<p><video controls style=\"max-width:100%\"><source src=\"{$rel}\" type=\"{$up['type']}\">Your browser does not support the video tag.</video></p>";
            } else {
                $content .= "<p><a href=\"{$rel}\" target=\"_blank\">Attached file</a></p>";
            }
        }

        if ($id) {
            $sql = "UPDATE announcements
                    SET title = :title, content = :content, type = :type, priority = :priority,
                        status = :status, published_at = :published_at, expires_at = :expires_at,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE announcements_id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':type' => $type,
                ':priority' => $priority,
                ':status' => $status,
                ':published_at' => $published_at,
                ':expires_at' => $expires_at,
                ':id' => $id
            ]);
            resp(true, 'Announcement updated.');
        } else {
            $sql = "INSERT INTO announcements
                    (title, content, type, priority, status, published_at, expires_at, created_at)
                    VALUES
                    (:title, :content, :type, :priority, :status, :published_at, :expires_at, CURRENT_TIMESTAMP)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':type' => $type,
                ':priority' => $priority,
                ':status' => $status,
                ':published_at' => $published_at,
                ':expires_at' => $expires_at
            ]);
            resp(true, 'Announcement created.');
        }
    } elseif ($action === 'delete') {
        $id = isset($_POST['announcements_id']) ? intval($_POST['announcements_id']) : 0;
        if (!$id) resp(false, 'Invalid ID.');

        $stmt = $db->prepare("DELETE FROM announcements WHERE announcements_id = :id");
        $stmt->execute([':id' => $id]);
        resp(true, 'Deleted.');
    }

    resp(false, 'Unknown action.');
}

resp(false, 'Invalid request.');
