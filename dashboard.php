<?php

require_once 'config.php';
redirectIfNotLogged();

try {
    $dbConn = (new Database())->getConnection(); // PDO
} catch (Exception $e) {
    die("Could not connect to database: " . htmlspecialchars($e->getMessage()));
}

function fmt_number($v) {
    return is_numeric($v) ? number_format($v) : htmlspecialchars($v);
}

$stats = [
    'total' => 0,
    'resolved' => 0,
    'avg_days' => 'N/A'
];

try {
    $sql = "SELECT COUNT(*) AS cnt FROM incidents";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $stats['total'] = (int)$stmt->fetchColumn();

    $sql = "SELECT COUNT(*) AS cnt FROM incidents WHERE status = 'resolved'";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $stats['resolved'] = (int)$stmt->fetchColumn();

    $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, submitted_at, resolved_at)) AS avg_seconds
            FROM incidents
            WHERE status = 'resolved' AND resolved_at IS NOT NULL";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $avgSec = $stmt->fetchColumn();
    if ($avgSec !== null && $avgSec !== false) {
        $stats['avg_days'] = round(((float)$avgSec) / (60*60*24), 1) . ' days';
    }
} catch (Exception $e) {
    error_log("Stats query error: " . $e->getMessage());
}

$months = [];
for ($i = 11; $i >= 0; $i--) {
    $months[] = date('Y-m', strtotime("-{$i} months"));
}

$trends = array_fill_keys($months, 0);

try {
    $sql = "SELECT DATE_FORMAT(submitted_at, '%Y-%m') AS ym, COUNT(*) AS cnt
            FROM incidents
            WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY ym
            ORDER BY ym ASC";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($trends[$row['ym']])) {
            $trends[$row['ym']] = (int)$row['cnt'];
        }
    }
} catch (Exception $e) {
    error_log("Trends query error: " . $e->getMessage());
}

$types = [];
try {
    $sql = "SELECT incident_type, COUNT(*) AS cnt FROM incidents GROUP BY incident_type ORDER BY cnt DESC";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Types query error: " . $e->getMessage());
}
$top5 = array_slice($types, 0, 5);

$barangays = [];
try {
    $sql = "SELECT barangay_name AS name, incident_count AS cnt 
            FROM barangay_stats 
            ORDER BY incident_count DESC 
            LIMIT 6";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Barangay query error: " . $e->getMessage());
}

$dangerZones = [];
try {
    $sql = "SELECT 
                b.barangay_name, 
                dz.issue, 
                dz.incident_id AS count_val 
            FROM danger_zones dz
            JOIN barangay_stats b ON dz.barangay_id = b.barangay_id
            ORDER BY dz.incident_id DESC 
            LIMIT 6";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $dangerZones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Danger zones query error: " . $e->getMessage());
}

$recent = [];
try {
    $sql = "SELECT 
                i.incident_id AS id, 
                i.incident_type, 
                i.location, 
                b.barangay_name AS barangay, 
                i.status, 
                i.submitted_at
            FROM incidents i
            LEFT JOIN barangay_stats b ON i.barangay_id = b.barangay_id
            ORDER BY i.submitted_at DESC
            LIMIT 6";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Recent incidents query error: " . $e->getMessage());
}

$trends_json = json_encode(array_values($trends));
$trends_labels = json_encode(array_keys($trends));
$types_json = json_encode($types);
$barangays_json = json_encode($barangays);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Analytics — Municipality Incident Reporting</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        body,html { margin:0; padding:0; font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif; min-height:100%; display:flex; flex-direction:column; background: white; }
        
        .container { max-width:120%; width:120%; margin:0 auto; }

        .hero {
            height: 200px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            background-size: cover;
            background-position: center;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.39), rgba(0, 0, 0, 0.64)), url('chur.jpeg');
            display:flex;
            align-items:center;
            padding: 20px 24px;
            gap: 1px;
        }

        .hero .logo {
            width: 110px;
            height: 95px;
            border-radius: 50%;
            margin-left: 2%;
            padding: 3px;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.18);
        }

        .hero h1 { margin: 0; color: #fff; font-size: 24px; font-weight: 700; margin-left: 250px; }
        .hero p { margin: 6px 0 0; color: #fff; opacity: 0.95; font-size: 13px; margin-left: 250px; }

        .controls { display:flex; justify-content:space-between; align-items:center; margin-top: 14px; gap: 10px; color: #fff; border: none; cursor: pointer; font-weight: 700; }
        
        .btn-primary{ background: linear-gradient(rgba(16, 128, 54, 1), rgba(0, 74, 53, 0.82)); height: 30%; color: white; padding: 10px 18px; border-radius: 6px; border: none; font-weight: 600; font-size: 14px; text-decoration: none; display: inline-block; cursor: pointer; transition: background 0.2s ease; }
        .btn-primary:hover { background-color: #146c43; }

        .left{ padding: 8px 10px; border-radius: 8px; font-size: 13px; margin-left: 5px; }

        .stats { margin-top: 16px; display:grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .card { background: var(--card, #fff); padding: 13px; border-radius: 10px; box-shadow: 0 8px 22px rgba(0,0,0,0.06); }
        .stat-title { color: var(--muted, #666); font-size: 13px; margin-bottom: 6px; font-weight: 600; }
        .stat-value { font-size: 28px; font-weight: 800; }

        .charts { margin-top: 18px; display:grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .chart-wrap { height: 280px; }

        .bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 18px; }
        .bottom > div { display: flex; flex-direction: column; gap: 12px; }
        .bottom .card { width: 100%; box-sizing: border-box; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; gap: 30px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #f2f2f2; text-align:left; }
        th { color: #666; font-weight:700; font-size:13px; }

        .status { padding: 6px 10px; border-radius: 999px; font-weight:700; font-size: 12px; display:inline-block; }
        .status.pending { background:#fff3cd; color:#856404; border:1px solid #ffeaa7; }
        .status.in-progress { background:#bee5eb; color:#0c5460; border:1px solid #bee5eb; }
        .status.resolved { background:#d4edda; color:#155724; border:1px solid #00821eff; }

        .footer { margin-top: 20px; padding: 14px; border-radius: 8px; background: linear-gradient(90deg, #d32f2f, #b71c1c); color: white; text-align:center; font-size:13px; }

        @media (max-width: 980px) {
            .charts, .bottom { grid-template-columns: 1fr; }
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="dashboard-body">

<?php include 'header.php'; ?>

<div class="container" style="width: 87%; max-width: 120%;">

    <div class="hero" role="banner" aria-label="App hero">
         <img src="chur.jpeg" alt="chur" style="width:150px;height:200px;object-fit:contain;" onerror="this.style.display='none'">
        <div class="hero-overlay">
            <div class="logo" aria-hidden="true">
                <img src="circle.png" alt="logo" style="width:150px;height:200px;object-fit:contain;" onerror="this.style.display='none'">
            </div>
            <div>
                <h1>Municipality Incident Reporting App</h1>
                <p>A simple centralized platform to report, monitor and resolve incidents faster.</p>
            </div>
        </div>
    </div>

    <div class="controls" role="region" aria-label="dashboard controls">
        <div class="left">
            <a href="submit-report.php" class="btn-primary" id="btnReport">Report an Incident</a>
        </div>

        <div class="right">
            <small style="color:#eee">Updated: <?=date('Y-m-d H:i')?></small>   
        </div>
    </div>


    <div style="margin-top:12px;"><?php include 'weatherwo.php'; ?></div>


    <div class="stats" role="region" aria-label="summary statistics">
        <div class="card">
            <div class="stat-title">Total Incidents Reported</div>
            <div class="stat-value" style="color:#d32f2f;"><?=fmt_number($stats['total'])?></div>
            <div style="margin-top:8px;color:#666;font-size:13px;">Compared to last month</div>
        </div>

        <div class="card">
            <div class="stat-title">Incidents Resolved</div>
            <div class="stat-value" style="color:#198754;"><?=fmt_number($stats['resolved'])?></div>
            <div style="margin-top:8px;color:#666;font-size:13px;">Resolution rate</div>
        </div>

        <div class="card">
            <div class="stat-title">Average Resolution Time</div>
            <div class="stat-value" style="color:#c43d3d;"><?=htmlspecialchars($stats['avg_days'])?></div>
            <div style="margin-top:8px;color:#666;font-size:13px;">(resolved cases)</div>
        </div>
    </div>


    <div class="charts" role="region" aria-label="charts">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <strong>Incident Trends (12 months)</strong>
                <small style="color:#666">Monthly</small>
            </div>
            <div class="chart-wrap">
                <canvas id="chartTrends" width="400" height="260"></canvas>
            </div>
        </div>

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <strong>Incidents by Barangay</strong>
                <small style="color:#666">Top areas</small>
            </div>
            <div class="chart-wrap">
                <canvas id="chartBarangay" width="400" height="260"></canvas>
            </div>
        </div>
    </div>

    <div class="bottom" role="region" aria-label="breakdowns">
        <div>
            <div class="card" style="margin-bottom:12px; width: 100%%;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong>Incidents by Type</strong>
                    <small style="color:#666">Distribution</small>
                </div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="chartTypes" width="300" height="260"></canvas>
                </div>
            </div>

            <div class="card" style=" width: 100%;">
                <strong>Top 5 Incident Types</strong>
                <table aria-label="top five">
                    <thead>
                        <tr><th>Incident</th><th style="width:60%;">Short note</th><th style="text-align:right">Count</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top5)): ?>
                            <tr><td colspan="3" style="padding:12px;color:#666">No data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($top5 as $row): ?>
                                <tr>
                                    <td><?=htmlspecialchars(ucfirst(str_replace(['-','_'], ' ', $row['incident_type'])))?></td>
                                    <td><?php
                                        $key = strtolower(trim($row['incident_type']));
                                        $notes = [
                                            'fire' => 'Fire incidents and emergencies',
                                            'medical' => 'Medical emergencies requiring attention',
                                            'traffic' => 'Traffic collisions, hazards',
                                            'waste' => 'Garbage, illegal dumping',
                                            'flood' => 'Floods and water logging',
                                            'crime' => 'Theft, vandalism, or public safety',
                                            'disturbance' => 'Noise, arguments, public disturbance'
                                        ];
                                        echo htmlspecialchars($notes[$key] ?? 'Reported incident');
                                    ?></td>
                                    <td style="text-align:right"><?=fmt_number($row['cnt'])?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="right-col">
            <div class="card">
                <strong>Identified Danger Zones</strong>
                <table>
                    <thead><tr><th>Location</th><th>Issue</th><th style="text-align:right">Incidents</th></tr></thead>
                    <tbody>
                        <?php if (empty($dangerZones)): ?>
                            <tr><td colspan="3" style="color:#666;padding:12px;">No records</td></tr>
                        <?php else: ?>
                            <?php foreach ($dangerZones as $z): ?>
                                <tr>
                                    <td><?=htmlspecialchars($z['barangay_name'])?></td>
                                    <td><?=htmlspecialchars($z['issue'])?></td>
                                    <td style="text-align:right;font-weight:700;"><?=fmt_number($z['count_val'])?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top: 12px;">
                <strong>Recent Incident Alerts</strong>
                <table>
                    <thead><tr><th>ID</th><th>Type</th><th>Location</th><th>Status</th><th>Reported</th></tr></thead>
                    <tbody>
                        <?php if (empty($recent)): ?>
                            <tr><td colspan="5" style="color:#666;padding:12px;">No recent incidents</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent as $r): ?>
                                <tr>
                                    <td>#<?=str_pad($r['id'],5,'0',STR_PAD_LEFT)?></td>
                                    <td><?=htmlspecialchars(ucfirst(str_replace(['-','_'], ' ', $r['incident_type'])))?></td>
                                    <td><?=htmlspecialchars(($r['location'] ?? '') . (!empty($r['barangay']) ? ' — ' . $r['barangay'] : ''))?></td>
                                    <td>
                                        <?php
                                            $s = strtolower(trim($r['status']));
                                            $badge = 'status ' . ($s === 'pending' ? 'pending' : ($s === 'resolved' ? 'resolved' : 'in-progress'));
                                        ?>
                                        <span class="<?=htmlspecialchars($badge)?>"><?=htmlspecialchars(ucfirst($s))?></span>
                                    </td>
                                    <td><?=htmlspecialchars(date('Y-m-d', strtotime($r['submitted_at'])))?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>

<script>
(function(){

    const trendsLabels = <?=json_encode(array_keys($trends))?>;
    const trendsValues = <?=json_encode(array_values($trends))?>;
    const types = <?=json_encode(array_map(function($t){ return ['type'=>$t['incident_type'],'count'=>$t['cnt']]; }, $types)); ?>;
    const barangays = <?=json_encode($barangays)?>;

    const ctxTrends = document.getElementById('chartTrends').getContext('2d');
    new Chart(ctxTrends, {
        type: 'line',
        data: {
            labels: trendsLabels,
            datasets: [{
                label: 'Incidents',
                data: trendsValues,
                borderColor: '#4a7c59',
                backgroundColor: 'rgba(74,124,89,0.12)',
                tension: 0.35,
                fill: true,
                pointRadius: 3
            }]
        },
        options: {
            responsive:true,
            maintainAspectRatio:false,
            plugins: { legend: { display:false } },
            scales: { y: { beginAtZero:true } }
        }
    });

    const ctxBarangay = document.getElementById('chartBarangay').getContext('2d');
    new Chart(ctxBarangay, {
        type: 'bar',
        data: {
            labels: barangays.map(b => b.name || 'Unknown'),
            datasets: [{
                label: 'Incidents',
                data: barangays.map(b => parseInt(b.cnt || 0)),
                backgroundColor: '#198754'
            }]
        },
        options: {
            responsive:true,
            maintainAspectRatio:false,
            plugins: { legend: { display:false } },
            scales: { y: { beginAtZero:true } }
        }
    });

    const ctxTypes = document.getElementById('chartTypes').getContext('2d');
    new Chart(ctxTypes, {
        type: 'doughnut',
        data: {
            labels: types.map(t => (t.type || '').replace(/[-_]/g, ' ')),
            datasets: [{
                data: types.map(t => parseInt(t.count || 0)),
                backgroundColor: ['#ac0606','#0663a1','#ac7e08','#023838','#3b1c77','#0f7b03']
            }]
        },
        options: {
            responsive:true,
            maintainAspectRatio:false,
            plugins: { legend: { position:'bottom' } }
        }
    });

})();
</script>
</body>
    <script>
        window.addEventListener('scroll', function() {
            const footer = document.querySelector('.footer');
            if(footer){
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
</html>
<?php include "footer.html"; ?>