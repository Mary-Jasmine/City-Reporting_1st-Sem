<?php  
require_once 'config.php';
redirectIfNotLogged();

$db = (new Database())->getConnection();

$total_stmt = $db->query("SELECT COUNT(*) AS total FROM incidents");
$total_incidents = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];

$resolved_stmt = $db->query("SELECT COUNT(*) AS resolved FROM incidents WHERE status = 'resolved'");
$resolved_incidents = $resolved_stmt->fetch(PDO::FETCH_ASSOC)['resolved'];

$resolution_rate = 0;
$resolution_color = "#007604";
if ($total_incidents > 0) {
    $resolution_rate = ($resolved_incidents / $total_incidents) * 100;
    $resolution_rate = round($resolution_rate, 2);

    if ($resolution_rate >= 75) $resolution_color = "#007604";   
    elseif ($resolution_rate >= 50) $resolution_color = "#d7860d"; 
    else $resolution_color = "#b72a22"; 
}

$barangay_stmt = $db->query("
    SELECT 
        b.barangay_name AS barangay, 
        COUNT(i.incident_id) AS incident_count 
    FROM incidents i
    JOIN barangay_stats b ON i.barangay_id = b.barangay_id
    GROUP BY b.barangay_name
");
$barangay_data = $barangay_stmt->fetchAll(PDO::FETCH_ASSOC);

$all_barangay_stmt = $db->query("SELECT barangay_name FROM barangay_stats");
$all_barangay_names = $all_barangay_stmt->fetchAll(PDO::FETCH_COLUMN);
$barangay_suggestion_list = json_encode($all_barangay_names);

$high_risk = 0;
foreach ($barangay_data as $b) {
    if ($b['incident_count'] >= 16) $high_risk++;
}

$baseLat = 14.0856; 
$baseLng = 121.3253;

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
    'Soledad' => ['lat' => 14.044613618199465, 'lng' => 121.31657336573751],

];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Incident Map Visualization</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
            body.heat-page{
            background: 
                linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)),
                url('chujjrch.jpeg') ;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    body { font-family: 'Inter', sans-serif; background: #f5f6f8; margin: 0; padding: 0; }
    .map-card { background: white; margin: 20px 5.5%; padding: 10px; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    #map { width: 100%; height: 700px; border-radius: 8px; box-shadow: 20px 10px 22px 20px rgba(27, 27, 27, 0.33);}

    .map-stats { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;}
    .stat-box { flex: 1; min-width: 10%;height: 20%; width: 10%; padding: 15px; border-radius: 8px;  background: #fff; box-shadow: 20px 10px 22px 20px rgba(147, 147, 147, 0.33); text-align: center; }
    .stat-box h4 { font-size: 14px; color: #4e0d0dff; font-weight: bold; }
    .stat-box p { margin-top: 5px; font-size: 24px; font-weight: bold; }

    .leaflet-marker-icon,
    .leaflet-marker-icon * {
        background: transparent !important; 
        border: none !important;
        box-shadow: none !important;
    }
</style>
</head>


<body class="heat-page">
<?php include 'header.php'; ?>

<h1 style="margin-left:41%;margin-top:2%;padding-top:20px; font-weight:bold; font-size: 30px; color: white;">Incident Map Visualization</h1>
<br><br>
    <div id="search-container" class="mb-3 p-3 rounded shadow-sm position-relative" style="width: 40%; margin: 0 auto; ">
        <div class="input-group" style="width: 100%">
            <input type="text" class="form-control" id="location-search" placeholder="Search Barangay or Specific Location..." autocomplete="off">
            <button class="btn btn-primary"style="background-color: gray" type="button" id="search-btn">Search</button>
        </div>
        <ul id="suggestions-list" class="list-group position-absolute w-100" style="z-index: 1000; background-color: white; max-height: 300px; overflow-y: auto; display: none; top: 100%;">
            </ul>
    </div>
<div class="map-card">

    <div class="map-stats" style="border-color: red;">
        <div class="stat-box" style="height: 150px;">
            <h4>Total Incidents</h4>
            <p><?= number_format($total_incidents) ?></p>
            <div style="margin-top:8px;color:#666;font-size:13px; color: #b9110eff;">Compared to last month</div>
        </div>

        <div class="stat-box" style="height: 150px;">

            <div class="stat-title" style="color: #3f0100ff; font-weight: bold;">Incidents Resolved</div>
            <p><?= number_format($resolved_incidents) ?></p>
            <p style="color: <?= $resolution_color ?>; margin-bottom: 20px; font-weight:lighter; font-size: 15px; color: #268207ff;">
              <?= $resolution_rate ?>% resolution rate</p>
        </div>

        <div class="stat-box" style="height: 150px">
            <h4>High-Risk Barangays (≥16)</h4>
            <p><?= $high_risk ?></p>
        </div>
    </div>
    

    <div id="map"></div>

</div>

<div class="modal fade" id="incidentModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><b>Incidents in <span id="modalBarangayName"></span></b></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="modalIncidentList">
            Loading...
        </div>
      </div>
  </div>
</div>

<script>
const baseLat = <?= $baseLat ?>;
const baseLng = <?= $baseLng ?>;
const barangayData = <?= json_encode($barangay_data) ?>;
const barangayCoords = <?= json_encode($barangay_coords) ?>;

const ALL_BARANGAYS = <?= $barangay_suggestion_list; ?>;

let searchMarker = null; 

const searchPinIcon = L.divIcon({
    html: `<svg fill="#007bff" viewBox="0 0 24 24" width="30" height="30">
             <path d="M12 2C8.1 2 5 5.1 5 9c0 5.3 7 13 7 13s7-7.7 7-13c0-3.9-3.1-7-7-7zm0 9.5
             c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/>
           </svg>`,
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    className: 'search-marker-pin' 
});

let bootstrapModal = new bootstrap.Modal(document.getElementById('incidentModal'));

function openModal(name) {
    document.getElementById("modalBarangayName").textContent = name;
    document.getElementById("modalIncidentList").innerHTML = "Loading...";
    bootstrapModal.show();

    fetch("heatmap_fetch.php?barangay=" + encodeURIComponent(name))
        .then(r => r.text())
        .then(html => document.getElementById("modalIncidentList").innerHTML = html);
}

function getPinColor(c) {
    if (c >= 16) return "#b72a22";
    if (c >= 6) return "#d7860dff";
    return "#007604ff";
}

document.addEventListener("DOMContentLoaded", () => {

    let map = L.map("map").setView([baseLat, baseLng], 13);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(map);

    const searchInput = document.getElementById('location-search');
    const searchButton = document.getElementById('search-btn');
    const suggestionsList = document.getElementById('suggestions-list');

    function showSuggestions(query) {
        suggestionsList.innerHTML = '';
        suggestionsList.style.display = 'none';

        if (query.length < 2) return;

        const filtered = ALL_BARANGAYS.filter(b => 
            b.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 8);

        if (filtered.length > 0) {
            suggestionsList.style.display = 'block';
            filtered.forEach(barangay => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action';
                li.textContent = barangay;
                li.style.cursor = 'pointer';
                li.onmousedown = () => { 
                    searchInput.value = barangay;
                    performSearch(barangay);
                    suggestionsList.style.display = 'none';
                    searchInput.focus();
                };
                suggestionsList.appendChild(li);
            });
        }
    }

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
    div.innerHTML += '<div style="font-size:12px; margin-bottom:3px;"><i style="background:#d7860dff;width:20px;height:20px;display:inline-block;margin-right:8px;"></i>6-15 ( Medium Barangay)</div>';
    div.innerHTML += '<div style="font-size:12px;"><i style="background:#007604ff;width:20px;height:20px;display:inline-block;margin-right:8px;"></i>0-5 (Low Risk Barangay)</div>';

    return div;
};

legend.addTo(map);

    
    searchInput.addEventListener('input', (e) => showSuggestions(e.target.value));
    searchInput.addEventListener('focus', (e) => showSuggestions(e.target.value));
    searchInput.addEventListener('blur', () => {
        setTimeout(() => suggestionsList.style.display = 'none', 150);
    });

    searchButton.addEventListener('click', () => performSearch(searchInput.value));
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            performSearch(searchInput.value);
            suggestionsList.style.display = 'none';
        }
    });

function performSearch(query) {
    if (!query.trim()) return;

    if (searchMarker) {
        map.removeLayer(searchMarker);
        searchMarker = null;
    }

    const coords = barangayCoords[query];
    if (coords) {
        const count = barangayData.find(b => b.barangay === query)?.incident_count || 0;

        map.setView([coords.lat, coords.lng], 16);

        const pin = L.divIcon({
            html: `<svg fill="${getPinColor(count)}" viewBox="0 0 24 24" width="30" height="30">
                     <path d="M12 2C8.1 2 5 5.1 5 9c0 5.3 7 13 7 13s7-7.7 7-13c0-3.9-3.1-7-7-7zm0 9.5
                     c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/>
                   </svg>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        searchMarker = L.marker([coords.lat, coords.lng], { icon: pin }).addTo(map);

        searchMarker.bindPopup(`
            <b>${query}</b><br>
            Incidents: ${count}<br>
            <button onclick="openModal('${query}')" class="btn btn-danger btn-sm mt-2">
                View Details
            </button>
        `).openPopup();

        return; 
    }

    const geocodingUrl = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}, Philippines&format=json&limit=1`;

    fetch(geocodingUrl)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);

                map.setView([lat, lng], 16);

                searchMarker = L.marker([lat, lng], { icon: searchPinIcon }).addTo(map);

                searchMarker.bindPopup(`<b>Searched Location:</b><br>${result.display_name}`).openPopup();
            } else {
                alert('Location not found. Please try a different search term.');
            }
        })
        .catch(error => {
            console.error('Geocoding error:', error);
            alert('An error occurred during search. Please check the console for details.');
        });
}


    barangayData.forEach(b => {
        let name = b.barangay;
        let count = parseInt(b.incident_count);
        let coords = barangayCoords[name];
        if (!coords) return;

        let pin = L.divIcon({
            html: `<svg fill="${getPinColor(count)}" viewBox="0 0 24 24" width="30" height="30">
                     <path d="M12 2C8.1 2 5 5.1 5 9c0 5.3 7 13 7 13s7-7.7 7-13c0-3.9-3.1-7-7-7zm0 9.5
                     c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/>
                   </svg>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        let marker = L.marker([coords.lat, coords.lng], {icon: pin}).addTo(map);

        marker.bindPopup(`
            <b>${name}</b><br>
            Incidents: ${count}<br>
            <button onclick="openModal('${name}')" class="btn btn-danger btn-sm mt-2">
                View Details
            </button>
        `);
    });

});
</script>

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

</body>
</html>
<?php include 'footer.html' ?>