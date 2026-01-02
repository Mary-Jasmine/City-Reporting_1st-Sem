<?php
require_once 'config.php';
$db = (new Database())->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo json_encode(['success'=>false,'message'=>'Missing id']);
    exit;
}

$stmt = $db->prepare("SELECT * FROM announcements WHERE announcements_id = :id");
$stmt->execute([':id'=>$id]);
$rec = $stmt->fetch();

if (!$rec) echo json_encode(['success'=>false,'message'=>'Not found']);
else echo json_encode(['success'=>true,'record'=>$rec]);
