<?php

// admin_analytics.php

session_start();



// Database connection

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



// Get filter parameters

$filter_days = isset($_GET['days']) ? intval($_GET['days']) : 30;

$filter_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$filter_month = isset($_GET['month']) ? intval($_GET['month']) : date('m');



// Calculate date range

$end_date = date('Y-m-d');

if (isset($_GET['year']) && isset($_GET['month'])) {

    $end_date = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));

}

$start_date = date('Y-m-d', strtotime("-$filter_days days", strtotime($end_date)));



// Format filter display text

$filter_text = '';

if (isset($_GET['month']) && isset($_GET['year'])) {

    $filter_text = date('F Y', strtotime("$filter_year-$filter_month-01"));

} else {

    $filter_text = "Last $filter_days Days";

}



// Get total incidents

$total_query = "SELECT COUNT(*) as total FROM incidents WHERE submitted_at BETWEEN ? AND ?";

$stmt = $conn->prepare($total_query);

$stmt->bind_param("ss", $start_date, $end_date);

$stmt->execute();

$total_incidents = $stmt->get_result()->fetch_assoc()['total'];



// Get resolved incidents

$resolved_query = "SELECT COUNT(*) as total FROM incidents WHERE status IN ('resolved', 'in-progress') AND submitted_at BETWEEN ? AND ?";

$stmt = $conn->prepare($resolved_query);

$stmt->bind_param("ss", $start_date, $end_date);

$stmt->execute();

$resolved_incidents = $stmt->get_result()->fetch_assoc()['total'];



// Get active users (unique reporters)

$users_query = "SELECT COUNT(DISTINCT user_id) as total FROM incidents WHERE submitted_at BETWEEN ? AND ?";

$stmt = $conn->prepare($users_query);

$stmt->bind_param("ss", $start_date, $end_date);

$stmt->execute();

$active_users = $stmt->get_result()->fetch_assoc()['total'];



// Get pending approvals

$pending_query = "SELECT COUNT(*) as total FROM incidents WHERE status = 'pending' AND submitted_at BETWEEN ? AND ?";

$stmt = $conn->prepare($pending_query);

$stmt->bind_param("ss", $start_date, $end_date);

$stmt->execute();

$pending_approvals = $stmt->get_result()->fetch_assoc()['total'];



// Get incidents by barangay

$barangay_query = "SELECT b.barangay_name, COUNT(i.incident_id) as count

                   FROM barangay_stats b

                   LEFT JOIN incidents i ON b.barangay_id = i.barangay_id

                   AND i.submitted_at BETWEEN ? AND ?

                   GROUP BY b.barangay_id, b.barangay_name

                   ORDER BY count DESC

                   LIMIT 10";

$stmt = $conn->prepare($barangay_query);

$stmt->bind_param("ss", $start_date, $end_date);

$stmt->execute();

$barangay_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



// Get incidents by type

$type_query = "SELECT incident_type, COUNT(*) as count

               FROM incidents

               WHERE submitted_at BETWEEN ? AND ?

               GROUP BY incident_type

               ORDER BY count DESC

               LIMIT 5";

$stmt = $conn->prepare($type_query);

$stmt->bind_param("ss", $start_date, $end_date);

$stmt->execute();

$type_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



// Get danger zones (based on your actual database structure)

$danger_query = "SELECT b.barangay_name, d.issue, d.incident_id as incident_count

                 FROM danger_zones d

                 JOIN barangay_stats b ON d.barangay_id = b.barangay_id

                 ORDER BY d.incident_id DESC

                 LIMIT 5";

$danger_zones = $conn->query($danger_query)->fetch_all(MYSQLI_ASSOC);



// Get recent incidents

$recent_query = "SELECT i.incident_id, i.incident_type, b.barangay_name, i.status

                 FROM incidents i

                 LEFT JOIN barangay_stats b ON i.barangay_id = b.barangay_id

                 WHERE i.submitted_at BETWEEN ? AND ?

                 ORDER BY i.submitted_at DESC

                 LIMIT 5";

$stmt = $conn->prepare($recent_query);

$stmt->bind_param("ss", $start_date, $end_date);

$stmt->execute();

$recent_incidents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



// Get trend data (monthly)

$trend_query = "SELECT DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as count

                FROM incidents

                WHERE submitted_at >= DATE_SUB(?, INTERVAL 12 MONTH)

                GROUP BY month

                ORDER BY month ASC";

$stmt = $conn->prepare($trend_query);

$stmt->bind_param("s", $end_date);

$stmt->execute();

$trend_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Analytics Dashboard - Municipality Incident Reporting</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>

        * { margin: 0; padding: 0; box-sizing: border-box; }

       

        body.dashboard-body{

            background:

                linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)),

                url('chujjrch.jpeg') ;

            background-size: cover;

            background-position: center;

            background-repeat: no-repeat;

            background-attachment: fixed;

        }



        body {

            font-family: 'Inter', sans-serif;

            background: #f5f7fa;

            min-height: 100vh;

            padding: 0;

        }



        .container{

            background-color: #ffffffff;

            max-width: 1320px;

            margin-left: 8%;

            padding: 1%;

            border-radius: 3%;

            margin-top: 4%;

        }



        .header {

            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);

            padding: 24px 40px;

            box-shadow: 0 4px 12px rgba(185, 28, 28, 0.15);

            display: flex;

            justify-content: space-between;

            align-items: center;

        }



        .header-left {

            display: flex;

            align-items: center;

            gap: 20px;

        }



        .logo {

            width: 56px;

            height: 56px;

            background: white;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: 900;

            font-size: 24px;

            color: #b91c1c;

            box-shadow: 0 4px 8px rgba(0,0,0,0.1);

        }



        .header-title h1 {

            font-size: 26px;

            color: white;

            margin-bottom: 4px;

            font-weight: 700;

        }



        .header-title p {

            font-size: 14px;

            color: rgba(31, 30, 30, 0.9);

            font-weight: 400;

        }



        .filter-controls {

            display: flex;

            gap: 12px;

            align-items: center;

            margin-top: 1%;

            margin-left: 55%;

        }



        .filter-controls select,

        .filter-controls button {

            padding: 11px 18px;

            border: 1px solid rgba(255,255,255,0.2);

            border-radius: 8px;

            font-size: 14px;

            cursor: pointer;

            background: rgba(29, 28, 28, 0.15);

            color: black;

            font-weight: 500;

            transition: all 0.3s ease;

            backdrop-filter: blur(10px);

        }



        .filter-controls select:hover,

        .filter-controls button:hover {

            background: rgba(255,255,255,0.25);

            transform: translateY(-1px);

        }



        .filter-controls select option {

            background: white;

            color: #1a202c;

        }



        .download-btn {

            background: #ca0505ff !important;

            color: #ffffffff !important;

            font-weight: 600;

            border: none !important;

        }



        .download-btn:hover {

            background: #a03d12ff !important;

            box-shadow: 0 4px 12px rgba(0,0,0,0.1);

        }



        /* PDF Header Styles */

        .pdf-header {

            background: linear-gradient(135deg, #ffffff50 0%, #ffffff3a 100%);

            padding: 30px 40px;

            border-radius: 12px;

            margin-bottom: 24px;

            color: white;

            box-shadow: 0 2px 2px rgba(185, 28, 28, 0.15);

        }



        .pdf-header h1 {

            font-size: 35px;

            font-weight: bold;

            margin-bottom: 8px;

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

        }



        .pdf-header .report-meta {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-top: 16px;

            padding-top: 16px;

            border-top: 1px solid rgba(255,255,255,0.2);

        }



        .pdf-header .filter-info {

            background: rgba(219, 219, 219, 0.15);

            padding: 8px 16px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: 600;

            backdrop-filter: blur(10px);

        }



        .pdf-header .date-info {

            font-size: 13px;

            opacity: 0.9;

        }



        .dashboard-container {

            max-width: 1400px;

            margin: 0 auto;

            padding: 30px 40px;

        }



        .stats-grid {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));

            gap: 14px;

            margin-bottom: 30px;

        }



        .stat-card {

            background: white;

            padding: 18px;

            border-radius: 7px;

            border: 1px solid #e5e7eb;

            width: auto;

            position: relative;

            overflow: hidden;

            transition: all 0.3s ease;

        }



        .stat-card:hover {

            transform: translateY(-4px);

            box-shadow: 0 12px 24px rgba(0,0,0,0.08);

        }



        .stat-card::before {

            content: '';

            position: absolute;

            top: 0;

            left: 0;

            width: 4px;

            height: 100%;

            border-color: #000000ff;

        }



        .stat-card h3 {

            font-size: 13px;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            color: #6b7280;

            margin-bottom: 12px;

            font-weight: 600;

        }



        .stat-card .value {

            font-size: 35px;

            font-weight: 800;

            margin-bottom: 8px;

            color: #111827;

            line-height: 1;

        }



        .stat-card .change {

            font-size: 14px;

            color: #6b7280;

            font-weight: 500;

        }



        .charts-grid {

            display: grid;

            grid-template-columns: 1.8fr 1.2fr;

            gap: 15px;

            margin-bottom: 30px;

        }



        .chart-card {

            background: white;

            padding: 28px;

            border-radius: 16px;

            border: 1px solid #e5e7eb;

            box-shadow: 0 1px 3px rgba(0,0,0,0.05);

        }



        .chart-card h3 {

            font-size: 18px;

            color: #111827;

            margin-bottom: 24px;

            font-weight: 700;

            display: flex;

            align-items: center;

            gap: 10px;

        }



        .chart-wrapper {

            position: relative;

            height: 250px;

        }



        .data-grid {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));

            gap: 18px;

            margin-bottom: 10px;

        }



        .data-card {

            background: white;

            padding: 28px;

            border-radius: 16px;

            border: 1px solid #e5e7eb;

            box-shadow: 0 1px 3px rgba(0,0,0,0.05);

        }



        .data-card h3 {

            font-size: 18px;

            color: #111827;

            margin-bottom: 24px;

            font-weight: 700;

            display: flex;

            align-items: center;

            gap: 10px;

        }



        .barangay-bar {

            display: flex;

            align-items: center;

            margin-bottom: 14px;

            gap: 12px;

        }



        .barangay-name {

            min-width: 160px;

            font-size: 14px;

            color: #374151;

            font-weight: 500;

        }



        .bar-container {

            flex: 1;

            height: 36px;

            background: #f3f4f6;

            border-radius: 0px;

            overflow: hidden;

            position: relative;

        }



        .bar-fill {

            height: 80%;

            background: linear-gradient(90deg, #006d10ff 0%, #085b00ff 100%);

            border-radius: 0;

            transition: width 0.8s ease;

            display: flex;

            align-items: center;

            justify-content: flex-end;

            padding-right: 12px;

            color: white;

            font-weight: 700;

            font-size: 13px;

        }



        table {

            width: 100%;

            border-collapse: collapse;

        }



        thead th {

            background: #f9fafb;

            padding: 14px 16px;

            text-align: left;

            font-weight: 600;

            font-size: 12px;

            color: #6b7280;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            border-bottom: 2px solid #e5e7eb;

        }



        tbody td {

            padding: 16px;

            border-bottom: 1px solid #f3f4f6;

            font-size: 14px;

            color: #374151;

        }



        tbody tr:hover {

            background: #f9fafb;

        }



        .status-badge {

            padding: 6px 14px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

            display: inline-block;

        }



        .status-pending {

            background: #dbb702ff;

            color: #462600ff;

        }



        .status-resolved {

            background: #007a3bff;

            color: #d6d6d6ff;

        }



        .status-in-progress {

            background: #dbeafe;

            color: #00277bff;

        }



        .status-rejected {

            background: #fee2e2;

            color: #be0000ff;

        }



        @media print {

            body {

                background: white;

                padding: 0;

            }

            .filter-controls {

                display: none;

            }

        }



        @media (max-width: 1200px) {

            .charts-grid {

                grid-template-columns: 1fr;

            }

            .data-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>

<body class="dashboard-body">

    <?php include 'adm_header.php'; ?>

    <div class="container">

       

        <div class="filter-controls">

            <select id="timeFilter" onchange="updateFilter()">

                <option value="30" <?= $filter_days == 30 ? 'selected' : '' ?>>Last 30 Days</option>

                <option value="60" <?= $filter_days == 60 ? 'selected' : '' ?>>Last 60 Days</option>

                <option value="90" <?= $filter_days == 90 ? 'selected' : '' ?>>Last 90 Days</option>

                <option value="365" <?= $filter_days == 365 ? 'selected' : '' ?>>Last Year</option>

            </select>

            <select id="monthFilter" onchange="updateMonthFilter()">

                <option value="">Select Month</option>

                <?php for ($m = 1; $m <= 12; $m++): ?>

                <option value="<?= $m ?>" <?= (isset($_GET['month']) && $_GET['month'] == $m) ? 'selected' : '' ?>>

                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>

                </option>

                <?php endfor; ?>

            </select>

            <select id="yearFilter" onchange="updateMonthFilter()">

                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>

                <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>><?= $y ?></option>

                <?php endfor; ?>

            </select>

            <button onclick="downloadReport()" class="download-btn">Download Report</button>

        </div>



        <div class="dashboard-container" id="reportContent">

            <!-- PDF Header with Filter Info -->

            <div class="pdf-header">

                <h1 style="font-weight: 27px; font-size: 33px; margin-left: 38%;; color: #3e3e3eff;">Analytics Report</h1>

                <div class="report-meta">

                    <div class="filter-info" style="color: black; font-weight: 100;">

                        Report Period: <?= htmlspecialchars($filter_text) ?>

                    </div>

                    <div class="date-info" style="color: black;">

                        Generated: <?= date('F d, Y - h:i A') ?><br>

                        Date Range: <?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?>

                    </div>

                </div>

            </div>



            <div class="stats-grid">

                <div class="stat-card">

                    <h3>Total Incidents</h3>

                    <div class="value"><?= number_format($total_incidents) ?></div>

                    <div class="change">Tracked incidents</div>

                </div>

                <div class="stat-card">

                    <h3>Resolved Incidents</h3>

                    <div class="value"><?= number_format($resolved_incidents) ?></div>

                    <div class="change">Successfully handled</div>

                </div>

                <div class="stat-card">

                    <h3>Active Users</h3>

                    <div class="value"><?= number_format($active_users) ?></div>

                    <div class="change">Reporting citizens</div>

                </div>

                <div class="stat-card">

                    <h3>Pending Incidents</h3>

                    <div class="value"><?= number_format($pending_approvals) ?></div>

                    <div class="change">Awaiting review</div>

                </div>

            </div>



            <div class="charts-grid">

                <div class="chart-card">

                    <h3>Incident Trends Over Time</h3>

                    <div class="chart-wrapper">

                        <canvas id="trendChart"></canvas>

                    </div>

                </div>

                <div class="chart-card">

                    <h3>Incidents by Type</h3>

                    <div class="chart-wrapper">

                        <canvas id="typeChart"></canvas>

                    </div>

                </div>

            </div>



            <div class="data-grid">

                <div class="data-card">

                    <h3>Incidents by Barangay</h3>

                    <div>

                        <?php

                        $max_count = $barangay_data[0]['count'] ?? 1;

                        foreach ($barangay_data as $barangay):

                            $percentage = ($barangay['count'] / $max_count) * 100;

                        ?>

                        <div class="barangay-bar">

                            <div class="barangay-name"><?= htmlspecialchars($barangay['barangay_name']) ?></div>

                            <div class="bar-container">

                                <div class="bar-fill" style="width: <?= $percentage ?>%">

                                    <?= $barangay['count'] ?>

                                </div>

                            </div>

                        </div>

                        <?php endforeach; ?>

                    </div>

                </div>



                <div class="data-card">

                    <h3>Identified Danger Zones</h3>

                    <table>

                        <thead>

                            <tr>

                                <th>Location</th>

                                <th>Issue</th>

                                <th>Incidents</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($danger_zones as $zone): ?>

                            <tr>

                                <td><?= htmlspecialchars($zone['barangay_name']) ?></td>

                                <td><?= htmlspecialchars($zone['issue']) ?></td>

                                <td><strong><?= $zone['incident_count'] ?></strong></td>

                            </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>



            <div class="data-card">

                <h3>Recent Incident Alerts</h3>

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Type</th>

                            <th>Location</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($recent_incidents as $incident): ?>

                        <tr>

                            <td>INC-<?= str_pad($incident['incident_id'], 4, '0', STR_PAD_LEFT) ?></td>

                            <td><?= htmlspecialchars($incident['incident_type']) ?></td>

                            <td><?= htmlspecialchars($incident['barangay_name'] ?? 'N/A') ?></td>

                            <td>

                                <span class="status-badge status-<?= $incident['status'] ?>">

                                    <?= ucfirst($incident['status']) ?>

                                </span>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <script>

        // Store filter text for PDF filename

        const filterText = '<?= $filter_text ?>';

       

        // Trend Chart

        const trendChartCtx = document.getElementById('trendChart').getContext('2d');

        new Chart(trendChartCtx, {

            type: 'line',

            data: {

                labels: <?= json_encode(array_column($trend_data, 'month')) ?>,

                datasets: [{

                    label: 'Incidents',

                    data: <?= json_encode(array_column($trend_data, 'count')) ?>,

                    borderColor: '#296f00ff',

                    backgroundColor: 'rgba(97, 97, 97, 0.12)',

                    tension: 0.4,

                    fill: true,

                    borderWidth: 3,

                    pointRadius: 5,

                    pointBackgroundColor: '#0b3a00ff',

                    pointBorderColor: '#fff',

                    pointBorderWidth: 2

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        display: false

                    },

                    tooltip: {

                        backgroundColor: '#111827',

                        padding: 12,

                        titleFont: { size: 14, weight: 'bold' },

                        bodyFont: { size: 13 }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        grid: {

                            color: '#f3f4f6',

                            drawBorder: false

                        },

                        ticks: {

                            font: { size: 12 },

                            color: '#6b7280'

                        }

                    },

                    x: {

                        grid: {

                            display: false

                        },

                        ticks: {

                            font: { size: 12 },

                            color: '#6b7280'

                        }

                    }

                }

            }

        });



        // Type Chart

        const typeChartCtx = document.getElementById('typeChart').getContext('2d');

        new Chart(typeChartCtx, {

            type: 'doughnut',

            data: {

                labels: <?= json_encode(array_column($type_data, 'incident_type')) ?>,

                datasets: [{

                    data: <?= json_encode(array_column($type_data, 'count')) ?>,

                    backgroundColor: ['#18a40bff', '#800000ff', '#09055aff', '#7d4d04ff', '#00483aff', '#00483aff'],

                    borderWidth: 0,

                    hoverOffset: 8

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        position: 'bottom',

                        labels: {

                            padding: 20,

                            font: { size: 12 },

                            color: '#374151'

                        }

                    },

                    tooltip: {

                        backgroundColor: '#111827',

                        padding: 12,

                        titleFont: { size: 14, weight: 'bold' },

                        bodyFont: { size: 13 }

                    }

                }

            }

        });



        function updateFilter() {

            const days = document.getElementById('timeFilter').value;

            window.location.href = `admin_analytics.php?days=${days}`;

        }



        function updateMonthFilter() {

            const month = document.getElementById('monthFilter').value;

            const year = document.getElementById('yearFilter').value;

            if (month) {

                window.location.href = `admin_analytics.php?month=${month}&year=${year}`;

            }

        }



        async function downloadReport() {

            const { jsPDF } = window.jspdf;

            const content = document.getElementById('reportContent');

           

            // Show loading indicator

            const downloadBtn = document.querySelector('.download-btn');

            const originalText = downloadBtn.textContent;

            downloadBtn.textContent = 'Generating PDF...';

            downloadBtn.disabled = true;

           

            try {

                const canvas = await html2canvas(content, {

                    scale: 2,

                    useCORS: true,

                    logging: false,

                    backgroundColor: '#ffffff'

                });

               

                const imgData = canvas.toDataURL('image/png');

                const pdf = new jsPDF('p', 'mm', 'a4');

                const pdfWidth = pdf.internal.pageSize.getWidth();

                const pdfHeight = pdf.internal.pageSize.getHeight();

                const imgWidth = canvas.width;

                const imgHeight = canvas.height;

                const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);

                const imgX = (pdfWidth - imgWidth * ratio) / 2;

                const imgY = 5;

               

                pdf.addImage(imgData, 'PNG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);

               

                // Create filename with filter info

                const dateStr = new Date().toISOString().split('T')[0];

                const filterSlug = filterText.replace(/\s+/g, '_');

                const filename = `Analytics_Report_${filterSlug}_${dateStr}.pdf`;

               

                pdf.save(filename);

               

                // Reset button

                downloadBtn.textContent = originalText;

                downloadBtn.disabled = false;

            } catch (error) {

                console.error('Error generating PDF:', error);

                alert('Failed to generate PDF. Please try again.');

                downloadBtn.textContent = originalText;

                downloadBtn.disabled = false;

            }

        }

    </script>

    <br><br>

    <?php include 'footer.html'; ?>

</body>

</html>