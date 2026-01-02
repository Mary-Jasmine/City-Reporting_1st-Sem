<?php
require_once 'config.php';
redirectIfNotLogged();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM hotlines ORDER BY agency_name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Hotlines</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .search-section {
            text-align: center; 
            margin: 20px 0 30px;
        }
        .search-box-container {
            display: flex;
            width: 800px; 
            margin: 0 auto;
            border: 2px solid rgba(82, 81, 81, 1);
            border-radius: 8px;
            overflow: hidden;
        }
        #hotlineSearch {
            flex-grow: 1;
            padding: 12px 15px;
            border: none;
            outline: none;
            font-size: 16px;
        }
        .search-btn {
            padding: 12px 18px; 
            border: none;
            background-color: rgb(132, 4, 4); 
            color: aliceblue;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
            flex-shrink: 0;
        }
        .search-btn:hover {
            background-color: rgb(100, 3, 3);
        }
        
        .contacts-table td:nth-child(2) {
    padding-left: 50px;

    <style>
    .contacts-table td:nth-child(2) {
        padding-left: 40px;
    }
}

    </style>
</head>

<body class="dashboard-page">
<?php include 'header.php'; ?>

<div class="dashboard-container">
    <div class="hot-content">

        <header class="page-header" style="margin-left:5%">
            <h1>Emergency Hotlines</h1>
            <p class="page-subtitle">Your quick guide to essential emergency contacts in San Pablo City, Laguna. Find fast assistance from key agencies.</p> 
        </header> 

        <div class="search-section"> 
            <div class="search-box-container"> 
                <input type="text" id="hotlineSearch" placeholder="Search agencies or numbers..."> 
                <button class="search-btn">Search</button> 
            </div> 
        </div>

        <div class="contacts-section"  style="margin-left:1%; padding-left: 1%; width:105%;">
            <div class="section-card">
                <h2>Emergency Contact List</h2>
                <p class="section-subtitle">Automatically fetched from database</p>

                <div class="contacts-table-container">
                    <table class="contacts-table" style="margin-left: 10px;">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Agency</th>
                                <th>Description</th>
                                <th>Phone Number</th>
                                <th>Landline Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <?php
                                    $logoSrc = null;
                                    $logoVal = $row['logo_type'] ?? '';
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
                                <td class="col-logo">
                                    <img src="<?= htmlspecialchars($logoSrc) ?>" class="agency-logo-img" alt="<?= htmlspecialchars($row['agency_name']); ?>">
                                </td>

                                <td><strong><?= htmlspecialchars($row['agency_name']); ?></strong></td>

                                <td><?= htmlspecialchars($row['description']); ?></td>

                                <td>
                                    <span class="phone-number"><?= htmlspecialchars($row['phone_number']); ?></span>
                                </td>

                                <td>
                                    <span class="landline-number"><?= htmlspecialchars($row['landline_number']); ?></span>
                                </td>

                                <td>
                                    <button class="view-details-btn" 
                                            onclick="viewDetails(<?= $row['hotlines_id']; ?>)">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('hotlineSearch').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll(".contacts-table tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

<script>
function viewDetails(id) {
    window.location.href = "view_hotline.php?id=" + id;
}
</script>

<script>
    window.addEventListener('scroll', function() {
        const footer = document.querySelector('.footer');
        if (footer) {
            const scrollPosition = window.scrollY + window.innerHeight;
            const documentHeight = document.body.scrollHeight;
            if (scrollPosition >= documentHeight - 100) {
                footer.classList.add('visible');
            } else {
                footer.classList.remove('visible');
            }
        }
    });
</script>
</body>
</html>
<?php include 'footer.html'; ?>