<?php
require_once 'config.php';
redirectIfNotLogged();

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$database = new Database();
$db = $database->getConnection();

$id = intval($_GET['id']);

$query = "SELECT * FROM hotlines WHERE hotlines_id = :id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $id);
$stmt->execute(); 

$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) {
    die("Record not found.");
}

$logoSrc = null;
$logoVal = $data['logo'] ?? $data['logo_type'] ?? '';
if (!empty($logoVal)) {
    if (preg_match('#^https?://#', $logoVal)) {
        $logoSrc = $logoVal;
    } else {
        $candidate = ltrim($logoVal, '/');
        $candidates = [
            $candidate,
            'ADMinn/' . $candidate,
            'ADMinn/uploads/logos/' . basename($candidate),
            'uploads/logos/' . basename($candidate)
        ];
        foreach ($candidates as $c) {
            if (file_exists(__DIR__ . '/' . $c)) {
                $logoSrc = $c;
                break;
            }
        }
    }
}
if (!$logoSrc) {
    if (file_exists(__DIR__ . '/ADMinn/uploads/logos/default.png')) {
        $logoSrc = 'ADMinn/uploads/logos/default.png';
    } else {
        $logoSrc = 'uploads/logos/default.png';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['agency_name']); ?> - Hotline Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .details-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }
        .details-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .details-header img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .details-body p {
            margin: 8px 0;
            font-size: 16px;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 18px;
            background: #a30000;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 15px;
        }
    </style>
</head>

<body>
<?php include 'header.php'; ?>

<div class="details-container">
    
    <div class="details-header">
        <img src="<?= htmlspecialchars($logoSrc); ?>" alt="Logo">
        <h2><?= htmlspecialchars($data['agency_name']); ?></h2>
    </div>

    <div class="details-body">
        <p><strong>Description:</strong> <?= htmlspecialchars($data['description']); ?></p>
        <p><strong>Phone Number:</strong> <?= htmlspecialchars($data['phone_number']); ?></p>
        <p><strong>Landline:</strong> <?= htmlspecialchars($data['landline_number']); ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($data['category'] ?? 'N/A'); ?></p>
    </div>

    <a href="hotlines.php" class="back-btn">← Back to Hotlines</a>

</div>

<?php include 'footer.html'; ?>
</body>
</html>