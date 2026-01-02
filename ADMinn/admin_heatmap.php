<?php
    require_once 'config.php';
    redirectIfNotLogged();

    $db = (new Database())->getConnection();

    $total_stmt = $db->query("SELECT COUNT(*) AS total FROM incidents");
    $total_incidents = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $resolved_stmt = $db->query("SELECT COUNT(*) AS resolved FROM incidents WHERE status = 'resolved'");
    $resolved_incidents = $resolved_stmt->fetch(PDO::FETCH_ASSOC)['resolved'];

    $resolution_rate = $total_incidents > 0 ? round(($resolved_incidents / $total_incidents) * 100, 1) : 0;

    $high_risk_stmt = $db->query("
        SELECT COUNT(*) as high_risk_count 
        FROM (
            SELECT barangay_id, COUNT(*) as incident_count 
            FROM incidents 
            GROUP BY barangay_id 
            HAVING incident_count >= 16
        ) as high_risk
    ");
    $high_risk_count = $high_risk_stmt->fetch(PDO::FETCH_ASSOC)['high_risk_count'];

    $barangay_stmt = $db->query("
        SELECT 
            b.barangay_name AS barangay,
            COUNT(i.incident_id) AS incident_count
        FROM barangay_stats b
        LEFT JOIN incidents i ON b.barangay_id = i.barangay_id
        GROUP BY b.barangay_id, b.barangay_name
        ORDER BY incident_count DESC
    ");
    $barangay_data_temp = $barangay_stmt->fetchAll(PDO::FETCH_ASSOC);

    $barangay_coords = [
        'Atisan' => ['lat' => 13.976905192566718, 'lng' => 121.27137268656615],
        'Bagong Bayan II-A' => ['lat' => 14.06655171393026, 'lng' => 121.3181866369413],
        'Bagong Pook VI-C' => ['lat' => 14.075959575493144, 'lng' => 121.32055972026782],
        'Barangay I-A' => ['lat' => 14.072952711822191, 'lng' => 121.31661920360418],
        'Barangay I-B' => ['lat' => 14.070133399885423, 'lng' => 121.31627241625503],
        'Barangay II-A' => ['lat' => 14.062502787275777, 'lng' => 121.31993302458228],
        'Barangay II-B' => ['lat' => 14.063257857620634, 'lng' => 121.32163382686113],
        'Barangay II-C' => ['lat' => 14.065724832993165, 'lng' => 121.32234255522489],
        'Barangay II-D' => ['lat' => 14.06754390898966, 'lng' => 121.3230723653112],
        'Barangay II-E' => ['lat' => 14.06505046430382, 'lng' => 121.32485157207459],
        'Barangay II-F' => ['lat' => 14.060323777927222, 'lng' => 121.32199922840947],
        'Barangay III-A' => ['lat' => 14.06703648484579, 'lng' => 121.32663402595864],
        'Barangay III-B' => ['lat' => 14.069502558072687, 'lng' => 121.32736463618917],
        'Barangay III-C' => ['lat' => 14.068077572810438, 'lng' => 121.33058290919763],
        'Barangay III-D' => ['lat' => 14.070272232375494, 'lng' => 121.33092526655999],
        'Barangay III-E' => ['lat' => 14.07179072823698, 'lng' => 121.33377883558266],
        'Barangay III-F' => ['lat' => 14.068253108694467, 'lng' => 121.32824461716608],
        'Barangay IV-A' => ['lat' => 14.072212842164923, 'lng' => 121.33092377804489],
        'Barangay IV-B' => ['lat' => 14.071428540452485, 'lng' => 121.32736454329746],
        'Barangay IV-C' => ['lat' => 14.070993636810554, 'lng' => 121.32592591823355],
        'Barangay V-A' => ['lat' => 14.076984611898377, 'lng' => 121.3244879542749],
        'Barangay V-B' => ['lat' => 14.073261592217586, 'lng' => 121.32468011252199],
        'Barangay V-C' => ['lat' => 14.072571866556197, 'lng' => 121.32502558642732],
        'Barangay V-D' => ['lat' => 14.071874408918783, 'lng' => 121.32539053020801],
        'Barangay VI-A' => ['lat' => 14.073331823576448, 'lng' => 121.32307108936408],
        'Barangay VI-B' => ['lat' => 14.077642237464199, 'lng' => 121.32324331536284],
        'Barangay VI-D' => ['lat' => 14.078510672785278, 'lng' => 121.32056361828403],
        'Barangay VI-E' => ['lat' => 14.075073955179995, 'lng' => 121.3177054378565],
        'Barangay VII-A' => ['lat' => 14.070592430928127, 'lng' => 121.32199699968159],
        'Barangay VII-B' => ['lat' => 14.070020553455716, 'lng' => 121.32378033133675],
        'Barangay VII-C' => ['lat' => 14.069360384049551, 'lng' => 121.32501968343136],
        'Barangay VII-D' => ['lat' => 14.06893695183977, 'lng' => 121.32575263712317],
        'Barangay VII-E' => ['lat' => 14.06785440190932, 'lng' => 121.32431366201784],
        'Bautista' => ['lat' => 13.998270833859886, 'lng' => 121.27742910495544],
        'Concepcion' => ['lat' => 14.079130050960702, 'lng' => 121.33829959855316],
        'Del Remedio' => ['lat' => 14.078523091648943, 'lng' => 121.31353568881873],
        'Dolores' => ['lat' => 14.10289954035137, 'lng' => 121.33415201941692],
        'San Antonio 1' => ['lat' => 14.00960230202481, 'lng' => 121.33987198349459],
        'San Antonio 2' => ['lat' => 13.994750277785752, 'lng' => 121.32700171388865],
        'San Bartolome' => ['lat' => 14.02724542039264, 'lng' => 121.28837827520545],
        'San Buenaventura' => ['lat' => 14.11401006548255, 'lng' => 121.32842920157853],
        'San Crispin' => ['lat' => 14.079491722867678, 'lng' => 121.28066196455866],
        'San Cristobal' => ['lat' => 14.047457468710316, 'lng' => 121.39865258544543],
        'San Diego' => ['lat' => 14.090817621339067, 'lng' => 121.37408605190792],
        'San Francisco' => ['lat' => 14.057901107856285, 'lng' => 121.32912600591888],
        'San Gabriel' => ['lat' => 14.058834952961025, 'lng' => 121.31651359832247],
        'San Gregorio' => ['lat' => 14.046340701575769, 'lng' => 121.32735614496069],
        'San Ignacio' => ['lat' => 14.04475867389449, 'lng' => 121.33937244148254],
        'San Isidro' => ['lat' => 13.989036978861618, 'lng' => 121.31070367176697],
        'San Joaquin' => ['lat' => 14.025625222818539, 'lng' => 121.32658652073631],
        'San Jose' => ['lat' => 14.06356219331834, 'lng' => 121.38485895484477],
        'San Juan' => ['lat' => 14.093858783036852, 'lng' => 121.30182644281263],
        'San Lorenzo' => ['lat' => 14.11185775715155, 'lng' => 121.35032331194877],
        'San Lucas 1' => ['lat' => 14.083429437170969, 'lng' => 121.324729979722],
        'San Lucas 2' => ['lat' => 14.088549305512762, 'lng' => 121.32760530781827],
        'San Marcos' => ['lat' => 14.104111962149632, 'lng' => 121.3036662802465],
        'San Mateo' => ['lat' => 14.110197578190736, 'lng' => 121.29823041664291],
        'San Miguel' => ['lat' => 14.037446981402317, 'lng' => 121.30535038390704],
        'San Nicolas' => ['lat' => 14.066444955501742, 'lng' => 121.29544456059153],
        'San Pedro' => ['lat' => 14.094679058149234, 'lng' => 121.33525448643674],
        'San Rafael' => ['lat' => 14.07264751880339, 'lng' => 121.3036413914332],
        'San Roque' => ['lat' => 14.063661134805521, 'lng' => 121.30927640061552],
        'San Vicente' => ['lat' => 14.026118508187409, 'lng' => 121.33955558159603],
        'Santa Ana' => ['lat' => 14.017434459137915, 'lng' => 121.32839606072636],
        'Santa Catalina' => ['lat' => 14.128884051165597, 'lng' => 121.34489167230684],
        'Santa Cruz' => ['lat' => 14.029500650524588, 'lng' => 121.35600292010677],
        'Santa Elena' => ['lat' => 14.042837224426632, 'lng' => 121.36646875808016],
        'Santa Filomena' => ['lat' => 14.089842261666462, 'lng' => 121.2878915766127],
        'Santa Isabel' => ['lat' => 14.08236075300334, 'lng' => 121.37962105050231],
        'Santa Maria' => ['lat' => 14.022822331384976, 'lng' => 121.30962669148974],
        'Santa Maria Magdalena' => ['lat' => 14.09718513147169, 'lng' => 121.31094717472313],
        'Santa Monica' => ['lat' => 14.052501620821483, 'lng' => 121.296813267396],
        'Santa Veronica' => ['lat' => 14.043492412645682, 'lng' => 121.28796324194353],
        'Santiago I' => ['lat' => 14.02246890147943, 'lng' => 121.28090217677487],
        'Santiago II' => ['lat' => 14.006413708339192, 'lng' => 121.2648943007713],
        'Santisimo Rosario' => ['lat' => 14.005391644491509, 'lng' => 121.31060314454484],
        'Santo Angel' => ['lat' => 14.106729273595047, 'lng' => 121.37056569088408],
        'Santo Cristo' => ['lat' => 14.063691886598095, 'lng' => 121.32988019771327],
        'Santo Niño' => ['lat' => 14.053963516697495, 'lng' => 121.36228045979435],
        'Soledad' => ['lat' => 14.044613618199465, 'lng' => 121.31657336573751]
    ];

    $barangay_data = [];
    foreach ($barangay_data_temp as $item) {
        $name = $item['barangay'];
        if (isset($barangay_coords[$name])) {
            $barangay_data[] = [
                'barangay' => $name,
                'incident_count' => $item['incident_count'],
                'latitude' => $barangay_coords[$name]['lat'],
                'longitude' => $barangay_coords[$name]['lng']
            ];
        }
    }

    $recent_stmt = $db->query("
        SELECT 
            i.incident_type,
            b.barangay_name,
            DATE_FORMAT(i.submitted_at, '%b %d, %Y') as formatted_date,
            i.status
        FROM incidents i
        JOIN barangay_stats b ON i.barangay_id = b.barangay_id
        ORDER BY i.submitted_at DESC
        LIMIT 5
    ");
    $recent_incidents = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

    $all_barangay_stmt = $db->query("SELECT barangay_name FROM barangay_stats ORDER BY barangay_name");
    $all_barangays = $all_barangay_stmt->fetchAll(PDO::FETCH_COLUMN);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Incident Heatmap - Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', sans-serif;
                background: #f5f6f8;
                color: #1f2937;
            }

                        
            .container{
                background-color: whitesmoke;
                padding: 2%;
                border-radius: 2%;

            }

            .dashboard-container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 30px 20px;
            }

            .dashboard-container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 30px 20px;
            }

            .page-title {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 30px;
                color: #111827;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .stat-card {
                background: white;
                padding: 24px;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .stat-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
            }

            .stat-title {
                font-size: 14px;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }

            .stat-value {
                font-size: 36px;
                font-weight: 700;
                color: #111827;
                margin-bottom: 8px;
            }

            .stat-subtitle {
                font-size: 13px;
                color: #9ca3af;
            }

            .filters-section {
                background: white;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                margin-bottom: 30px;
                display: flex;
                gap: 15px;
                flex-wrap: wrap;
                align-items: center;
            }

            .filter-label {
                font-weight: 600;
                color: #374151;
                margin-left: 3  %;
                
            }
            
            .filter-input, .filter-select {
                padding: 10px 15px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                font-size: 14px;

                min-width: 50%;
            }

            .filter-button {
                padding: 10px 20px;
                background: #b72a22;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: background 0.2s;
            }

            .filter-button:hover {
                background: #991f19;
            }

            .clear-button {
                padding: 10px 20px;
                background: #6b7280;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
            }

            .content-grid {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 30px;
                margin-bottom: 30px;
            }

            @media (max-width: 1024px) {
                .content-grid {
                    grid-template-columns: 1fr;
                }
            }

            .map-section {
                background: white;
                padding: 24px;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 20px;
                font-weight: 700;
                margin-bottom: 8px;
                color: #111827;
            }

            .section-subtitle {
                font-size: 14px;
                color: #6b7280;
                margin-bottom: 20px;
            }

            #map {
                width: 100%;
                height: 500px;
                border-radius: 8px;
                margin-bottom: 20px;
            }

            .recent-section {
                background: white;
                padding: 24px;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .incidents-table {
                width: 100%;
                margin-top: 15px;
            }

            .incidents-table th {
                text-align: left;
                padding: 12px 8px;
                font-size: 12px;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                border-bottom: 2px solid #e5e7eb;
            }

            .incidents-table td {
                padding: 12px 8px;
                font-size: 14px;
                border-bottom: 1px solid #f3f4f6;
            }

            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
            }

            .status-resolved {
                background: #d1fae5;
                color: #065f46;
            }

            .status-pending {
                background: #fee2e2;
                color: #991b1b;
            }

            .status-in-progress {
                background: #fef3c7;
                color: #92400e;
            }

            .leaflet-marker-icon,
            .leaflet-marker-icon * {
                background: transparent !important; 
                border: none !important;
                box-shadow: none !important;
            }

            .search-container {
                position: relative;
                flex: 1;
                max-width: 1000%;
            }

            .suggestions-list {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #d1d5db;
                border-top: none;
                border-radius: 0 0 8px 8px;
                max-height: 300px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .suggestions-list.active {
                display: block;
            }

            .suggestion-item {
                padding: 10px 15px;
                cursor: pointer;
                border-bottom: 1px solid #f3f4f6;
                font-size: 14px;
            }

            .suggestion-item:hover {
                background: #f9fafb;
            }

            .suggestion-item:last-child {
                border-bottom: none;
            }
        </style>
    </head>
 <body class="dashboard-body">
        <?php include 'adm_header.php'; ?>
<br><br>
<div class="container">
        <div class="dashboard-container">
            <h1 class="page-title"> Incident Heatmap Dashboard</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title" style="color: #041e57ff">Total Incidents</span>
                        <div class="stat-icon red"></div>
                    </div>
                    <div class="stat-value"><?= number_format($total_incidents) ?></div>
                    <div class="stat-subtitle" style="color: #111827;">All reported incidents</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title" style="color: #991f19;">High-Risk Barangays</span>
                        <div class="stat-icon yellow"></div>
                    </div>
                    <div class="stat-value" style="color: #9c0800ff"><?= $high_risk_count ?></div>
                    <div class="stat-subtitle" style="color: #111827ae;">Barangays with 16+ incidents</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title" style="color: #007604;">Incidents Resolved</span>
                        <div class="stat-icon green"></div>
                    </div>
                    <div class="stat-value"><?= number_format($resolved_incidents) ?></div>
                    <div class="stat-subtitle" style="color: #007604;"><?= $resolution_rate ?>% resolution rate</div>
                </div>
            </div>

            <div class="filters-section">
                <span class="filter-label">Filters:</span>
                <div class="search-container">
                    <input type="text" 
                        id="location-search" 
                        class="filter-input" 
                        placeholder="Search barangay or location..."
                        autocomplete="off">
                    <div id="suggestions-list" class="suggestions-list"></div>
                </div>
                
            </div>

            <div class="content-grid">
                <div class="map-section">
                    <h2 class="section-title">Incident Heatmap Visualization</h2>
                    <p class="section-subtitle">Visual representation of incident density across barangays</p>
                    
                    <div id="map"></div>
                </div>

                <div class="recent-section">
                    <h2 class="section-title">Recent Incidents</h2>
                    <p class="section-subtitle">Overview of the latest reported incidents</p>

                    <table class="incidents-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Barangay</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_incidents as $incident): ?>
                            <tr>
                                <td><?= ucfirst(htmlspecialchars($incident['incident_type'])) ?></td>
                                <td><?= htmlspecialchars($incident['barangay_name']) ?></td>
                                <td><?= htmlspecialchars($incident['formatted_date']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($incident['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($incident['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="incidentModal" tabindex="-1" aria-labelledby="incidentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #b72a22 0%, #991f19 100%); color: white;">
                <h5 class="modal-title" id="incidentModalLabel">
                <span id="modalBarangayName"></span> - Incident Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            
            <div class="modal-body" id="modalIncidentList" style="background: #f9fafb; min-height: 400px;">
                
                <div style="text-align: center; padding: 40px;">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p style="margin-top: 16px; color: #6b7280;">Loading incident details...</p>
                </div>
            </div>
            </div>
        </div>
        </div>
        </div>
        <br><br>

        <?php include 'footer.html'; ?>

        <script>
            const barangayData = <?= json_encode($barangay_data) ?>;
            const allBarangays = <?= json_encode($all_barangays) ?>;

            const map = L.map('map').setView([14.0856, 121.3253], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            function getPinColor(c) {
                if (c >= 16) return "#b72a22";
                if (c >= 6) return "#d7860d";
                return "#007604";
            }

            function createPinIcon(count) {
                const color = getPinColor(count);
                return L.divIcon({
                    html: `<svg fill="${color}" viewBox="0 0 24 24" width="30" height="30">
                             <path d="M12 2C8.1 2 5 5.1 5 9c0 5.3 7 13 7 13s7-7.7 7-13c0-3.9-3.1-7-7-7zm0 9.5
                             c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/>
                           </svg>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                });
            }

            barangayData.forEach(location => {
                const marker = L.marker([location.latitude, location.longitude], {
                    icon: createPinIcon(location.incident_count)
                }).addTo(map);

                marker.bindPopup(`
                    <div style="padding: 10px; min-width: 200px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: bold; color: #111827;">
                            ${location.barangay}
                        </h4>
                        <p style="margin: 5px 0; font-size: 14px; color: #4b5563;">
                            <strong>Incidents:</strong> ${location.incident_count}
                        </p>
                        <button onclick="openModal('${location.barangay}')" 
                                class="btn btn-danger btn-sm mt-2"
                                style="
                                    width: 100%;
                                    padding: 6px 12px;
                                    font-size: 13px;
                                ">
                            View Details
                        </button>
                    </div>
                `);
            });

            const legend = L.control({position: 'bottomleft'});

            legend.onAdd = function(map) {
                const div = L.DomUtil.create('div', 'info legend');
                div.style.background = 'white';
                div.style.padding = '10px';
                div.style.borderRadius = '5px';
                div.style.boxShadow = '0 0 5px rgba(0,0,0,0.3)';
                div.style.fontFamily = 'sans-serif';

                div.innerHTML += '<h4 style="margin:0 0 5px 0; font-size:12px; font-weight:bold;">Legend</h4>';
                div.innerHTML += '<div style="font-size:12px; margin-bottom:3px;"><i style="background:#b72a22;width:20px;height:20px;display:inline-block;margin-right:8px;"></i>16+ (High Risk Barangay)</div>';
                div.innerHTML += '<div style="font-size:12px; margin-bottom:3px;"><i style="background:#d7860d;width:20px;height:20px;display:inline-block;margin-right:8px;"></i>6-15 (Medium Risk)</div>';
                div.innerHTML += '<div style="font-size:12px;"><i style="background:#007604;width:20px;height:20px;display:inline-block;margin-right:8px;"></i>0-5 (Low Risk)</div>';

                return div;
            };

            legend.addTo(map);

            function openModal(barangayName) {
                document.getElementById("modalBarangayName").textContent = barangayName;
                
                document.getElementById("modalIncidentList").innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p style="margin-top: 16px; color: #6b7280;">Loading incident details...</p>
                    </div>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('incidentModal'));
                modal.show();
                
                fetch(`adm_heatfetch.php?barangay=${encodeURIComponent(barangayName)}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById("modalIncidentList").innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById("modalIncidentList").innerHTML = `
                            <div style="text-align: center; padding: 40px; color: #ef4444;">
                                <svg style="width: 64px; height: 64px; margin: 0 auto 16px;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Error Loading Data</p>
                                <p style="font-size: 14px;">Unable to load incident details. Please try again.</p>
                            </div>
                        `;
                    });
            }
            const searchInput = document.getElementById('location-search');
            const suggestionsList = document.getElementById('suggestions-list');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                suggestionsList.innerHTML = '';
                
                if (query.length === 0) {
                    suggestionsList.classList.remove('active');
                    return;
                }

                const matches = allBarangays.filter(b => 
                    b.toLowerCase().includes(query)
                ).slice(0, 10);

                if (matches.length > 0) {
                    matches.forEach(barangay => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.textContent = barangay;
                        div.onclick = () => selectBarangay(barangay);
                        suggestionsList.appendChild(div);
                    });
                    suggestionsList.classList.add('active');
                } else {
                    suggestionsList.classList.remove('active');
                }
            });

            function selectBarangay(barangayName) {
                searchInput.value = barangayName;
                suggestionsList.classList.remove('active');                
                const location = barangayData.find(b => b.barangay === barangayName);
                if (location) {
                    map.setView([location.latitude, location.longitude], 15);
                    setTimeout(() => openModal(barangayName), 500);
                }
            }

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                    suggestionsList.classList.remove('active');
                }
            });

            function applyFilters() {
                const searchValue = searchInput.value.trim();
                const dateValue = document.getElementById('date-filter').value;
                
                if (searchValue) {
                    const location = barangayData.find(b => 
                        b.barangay.toLowerCase() === searchValue.toLowerCase()
                    );
                    if (location) {
                        map.setView([location.latitude, location.longitude], 15);
                        openModal(location.barangay);
                    }
                }
                
                if (dateValue) {
                    console.log('Date filter:', dateValue);
                }
            }

            function clearFilters() {
                searchInput.value = '';
                document.getElementById('date-filter').value = '';
                suggestionsList.classList.remove('active');
                map.setView([14.0856, 121.3253], 13);
            }

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
        </script>
    </body>
    </html>