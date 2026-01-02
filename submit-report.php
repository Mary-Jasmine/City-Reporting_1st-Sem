<?php
require_once 'config.php';
redirectIfNotLogged();

$database = new Database();
$db = $database->getConnection();

function getBarangayIdFromName($db, $barangay_name) {
    $query = "SELECT barangay_id FROM barangay_stats WHERE barangay_name = :barangay_name LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':barangay_name', $barangay_name);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['barangay_id'] ?? null; 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $incident_type = sanitizeInput($_POST['incidentType']);

    if ($incident_type === 'other' && !empty($_POST['otherIncidentType'])) {
        $incident_type = sanitizeInput($_POST['otherIncidentType']);
    }
    
    $description = sanitizeInput($_POST['incidentDescription']);
    $location = sanitizeInput($_POST['incidentLocation']);
    
    $barangay_name = sanitizeInput($_POST['barangay_name']); 
    $user_id = $_SESSION['user_id'];
    
    $barangay_id = getBarangayIdFromName($db, $barangay_name);
    
    if ($barangay_id === null) {
        $error = "Failed to submit report: Selected Barangay name is invalid or not mapped in the database.";
    } else {
        try {
            $query = "INSERT INTO incidents (user_id, incident_type, description, location, barangay_id, status) 
                      VALUES (:user_id, :incident_type, :description, :location, :barangay_id, 'Pending')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':incident_type', $incident_type);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':barangay_id', $barangay_id);
            if ($stmt->execute()) {
                $incident_id = $db->lastInsertId();
                
                if (!empty($_FILES['fileUpload']['name'][0])) {
                    $upload_dir = "uploads/incidents/";
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                    foreach ($_FILES['fileUpload']['tmp_name'] as $key => $tmp_name) {
                        $file_name = basename($_FILES['fileUpload']['name'][$key]);
                        $file_path = $upload_dir . uniqid() . '_' . $file_name;

                        if (move_uploaded_file($tmp_name, $file_path)) {
                            $file_query = "INSERT INTO incident_files (incident_id, file_name, file_path) 
                                           VALUES (:incident_id, :file_name, :file_path)";
                            $file_stmt = $db->prepare($file_query);
                            $file_stmt->bindParam(':incident_id', $incident_id);
                            $file_stmt->bindParam(':file_name', $file_name);
                            $file_stmt->bindParam(':file_path', $file_path);
                            $file_stmt->execute();
                        }
                    }
                }

                $success = "Incident report submitted successfully! Your incident ID is: #" . str_pad($incident_id, 5, '0', STR_PAD_LEFT);
            }
        } catch(PDOException $e) {
            $error = "Failed to submit report: " . $e->getMessage();
        }
    }
}

$user_incidents_query = "SELECT i.*, b.barangay_name FROM incidents i 
                         LEFT JOIN barangay_stats b ON i.barangay_id = b.barangay_id
                         WHERE user_id = :user_id ORDER BY submitted_at DESC";
$user_incidents_stmt = $db->prepare($user_incidents_query);
$user_incidents_stmt->bindParam(':user_id', $_SESSION['user_id']);
$user_incidents_stmt->execute();
$user_incidents = $user_incidents_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Report - Municipality Incident Reporting</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<style>
.history-table-scroll {
    max-height: 400px;
    overflow-y: auto;
    background-color: whitesmoke;
    border-radius: 8px;
    padding: 5px;
}
.history-table-scroll table {
    width: 100%;
    border-collapse: collapse;
}
.history-table-scroll table thead th {
    position: sticky;
    top: 0;
    background: #171515ff;
    color: #fff;
    z-index: 2;
    padding: 8px;
    text-align: left;
}
.history-table-scroll table tbody td {
    padding: 8px;
    color: #000;
}
.status-badge {
    padding: 3px 6px;
    border-radius: 4px;
    color: #fff;
}
.status-badge.Pending { background-color: orange; }
.status-badge.Resolved { background-color: green; }
.status-badge['In-progress'] { background-color: blue; }
</style>
</head>
<body class="dashboard-page">

<?php include 'header.php'; ?>

<div class="report-container">
    <div class="report-content" style="color: #f0f0f0;">
        <header class="page-header">
            <h1>Submit Report</h1>
        </header>
        
        <div class="report-section">
            <div class="section-card" style="width:75%; margin-left:12%;">
                <h2 style="margin-left: 10%;">Submit New Incident Report</h2>
                <p class="section-subtitle" style="margin-left:10%;">Please provide details about the incident you wish to report.</p>
                
                <?php if (isset($success)): ?>
                    <div class="success-message" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form class="incident-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group" style="margin-left: 10%;">
                        <label for="incidentType">Incident Type</label>
                        <select id="incidentType" name="incidentType" required>
                            <option value="">Select incident type</option>
                            <option value="road-hazard">Road Hazard</option>
                            <option value="waste-management">Waste Management</option>
                            <option value="public-safety">Public Safety</option>
                            <option value="infrastructure">Infrastructure Damage</option>
                            <option value="traffic">Traffic Congestion</option>
                            <option value="environmental">Environmental Issue</option>
                            <option value="emergency">Emergency</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="otherTypeGroup" style="margin-left: 10%; display: none;">
                        <label for="otherIncidentType">Please Specify Incident Type</label>
                        <input type="text" id="otherIncidentType" name="otherIncidentType" placeholder="e.g., Illegal Dumping" />
                    </div>
                    <div class="form-group" style="margin-left: 10%;">
                        <label for="incidentDescription">Detailed Description</label>
                        <textarea id="incidentDescription" name="incidentDescription" rows="5" placeholder="Describe the incident..." required></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-left: 10%;">
                        <label for="incidentLocation">Location</label>
                        <input type="text" id="incidentLocation" name="incidentLocation" placeholder="Enter location" required>
                    </div>
                    
                    <div id="mapPreview" style="width: 90%; height: 200px; margin-left: 10%; margin-top: 10px; border-radius: 10px; display:none;"></div>
                    
                    <div class="form-group"style="margin-left: 10%; margin-bottom: 15px;">
        
                    </div>

                    <div class="form-group"style="margin-left: 10%;">
                        <label for="barangay_name">Barangay</label>
                        <select id="barangay_name" name="barangay_name" required>
                            <option value="">Select Barangay</option>
                            <option value="Atisan">Atisan</option>
                            <option value="Bagong Bayan II-A">Bagong Bayan II-A</option>
                            <option value="Bagong Pook VI-C">Bagong Pook VI-C</option>
                            <option value="Barangay I-A">Barangay I-A</option>
                            <option value="Barangay I-B">Barangay I-B</option>
                            <option value="Barangay II-A">Barangay II-A</option>
                            <option value="Barangay II-B">Barangay II-B</option>
                            <option value="Barangay II-C">Barangay II-C</option>
                            <option value="Barangay II-D">Barangay II-D</option>
                            <option value="Barangay II-E">Barangay II-E</option>
                            <option value="Barangay II-F">Barangay II-F</option>
                            <option value="Barangay III-A">Barangay III-A</option>
                            <option value="Barangay III-B">Barangay III-B</option>
                            <option value="Barangay III-C">Barangay III-C</option>
                            <option value="Barangay III-D">Barangay III-D</option>
                            <option value="Barangay III-E">Barangay III-E</option>
                            <option value="Barangay III-F">Barangay III-F</option>
                            <option value="Barangay IV-A">Barangay IV-A</option>
                            <option value="Barangay IV-B">Barangay IV-B</option>
                            <option value="Barangay IV-C">Barangay IV-C</option>
                            <option value="Barangay V-A">Barangay V-A</option>
                            <option value="Barangay V-B">Barangay V-B</option>
                            <option value="Barangay V-C">Barangay V-C</option>
                            <option value="Barangay V-D">Barangay V-D</option>
                            <option value="Barangay VI-A">Barangay VI-A</option>
                            <option value="Barangay VI-B">Barangay VI-B</option>
                            <option value="Barangay VI-D">Barangay VI-D</option>
                            <option value="Barangay VI-E">Barangay VI-E</option>
                            <option value="Barangay VII-A">Barangay VII-A</option>
                            <option value="Barangay VII-B">Barangay VII-B</option>
                            <option value="Barangay VII-C">Barangay VII-C</option>
                            <option value="Barangay VII-D">Barangay VII-D</option>
                            <option value="Barangay VII-E">Barangay VII-E</option>
                            <option value="Bautista">Bautista</option>
                            <option value="Concepcion">Concepcion</option>
                            <option value="Del Remedio">Del Remedio</option>
                            <option value="Dolores">Dolores</option>
                            <option value="San Antonio 1">San Antonio 1</option>
                            <option value="San Antonio 2">San Antonio 2</option>
                            <option value="San Bartolome">San Bartolome</option>
                            <option value="San Buenaventura">San Buenaventura</option>
                            <option value="San Crispin">San Crispin</option>
                            <option value="San Cristobal">San Cristobal</option>
                            <option value="San Diego">San Diego</option>
                            <option value="San Francisco">San Francisco</option>
                            <option value="San Gabriel">San Gabriel</option>
                            <option value="San Gregorio">San Gregorio</option>
                            <option value="San Ignacio">San Ignacio</option>
                            <option value="San Isidro">San Isidro</option>
                            <option value="San Joaquin">San Joaquin</option>
                            <option value="San Jose">San Jose</option>
                            <option value="San Juan">San Juan</option>
                            <option value="San Lorenzo">San Lorenzo</option>
                            <option value="San Lucas 1">San Lucas 1</option>
                            <option value="San Lucas 2">San Lucas 2</option>
                            <option value="San Marcos">San Marcos</option>
                            <option value="San Mateo">San Mateo</option>
                            <option value="San Miguel">San Miguel</option>
                            <option value="San Nicolas">San Nicolas</option>
                            <option value="San Pedro">San Pedro</option>
                            <option value="San Rafael">San Rafael</option>
                            <option value="San Roque">San Roque</option>
                            <option value="San Vicente">San Vicente</option>
                            <option value="Santa Ana">Santa Ana</option>
                            <option value="Santa Catalina">Santa Catalina</option>
                            <option value="Santa Cruz">Santa Cruz</option>
                            <option value="Santa Elena">Santa Elena</option>
                            <option value="Santa Filomena">Santa Filomena</option>
                            <option value="Santa Isabel">Santa Isabel</option>
                            <option value="Santa Maria">Santa Maria</option>
                            <option value="Santa Maria Magdalena">Santa Maria Magdalena</option>
                            <option value="Santa Monica">Santa Monica</option>
                            <option value="Santa Veronica">Santa Veronica</option>
                            <option value="Santiago I">Santiago I</option>
                            <option value="Santiago II">Santiago II</option>
                            <option value="Santisimo Rosario">Santisimo Rosario</option>
                            <option value="Santo Angel">Santo Angel</option>
                            <option value="Santo Cristo">Santo Cristo</option>
                            <option value="Santo Niño">Santo Niño</option>
                            <option value="Soledad">Soledad</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-left: 10%;">
                        <label for="fileUpload">Upload Photos/Videos (Optional)</label>
                        <div class="file-upload-area" style="max-width:650px;height: 2%">
                            <input type="file" id="fileUpload" name="fileUpload[]" multiple accept="image/*,video/*">
                            <div class="upload-placeholder">
                                <span class="upload-icon">📁</span>
                                <span class="upload-text">Select Files</span>
                                <span class="upload-subtext">No files chosen</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions"style="margin-left: 10%;">
                        <button type="button" class="clear-btn" style="color: white ;" onclick="clearForm()">Clear Form</button>
                        <button type="submit" class="submit-btn">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="history-section" style="width: 75%; margin-left: 12.5%; margin-top: 30px; background-color: #f0f0f0; padding: 25px; border-radius: 8px;">
            <h2 style="color: black;">My Incident Report History</h2>
            <div class="history-table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Incident ID</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Location</th>
                            <th>Barangay</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_incidents as $incident): ?>
                        <tr>
                            <td>#<?php echo str_pad($incident['incident_id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo ucfirst(str_replace('-', ' ', $incident['incident_type'])); ?></td>
                            <td><span class="status-badge <?php echo $incident['status']; ?>"><?php echo ucfirst($incident['status']); ?></span></td>
                            <td><?php echo date('Y-m-d', strtotime($incident['submitted_at'])); ?></td>
                            <td><?php echo $incident['location']; ?></td>
                            <td><?php echo $incident['barangay_name'] ?? $incident['barangay_id']; ?></td>
                            <td><button class="action-btn" style="background-color: #a20606ff;" onclick="viewIncident(<?php echo $incident['incident_id']; ?>)">View</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top:10px ;margin-bottom:40px; color: black;;background-color: white;">Total Reports: <?php echo count($user_incidents); ?></p>
        </div>

        <div id="incidentModal" class="modal" style="display:none;">
            <div class="modal-content" style="max-width:685px;height: 40%; margin-left:27%; margin-top: 5%;">
                <span class="close" onclick="closeIncidentModal()">&times;</span>
                <h2 style="margin-bottom:15px; color: #000;">Incident Report Details</h2>
                <div id="modalBody" style="line-height:1.6;height: 70%;color:black;">Loading...</div>
            </div>
        </div>

    </div>
</div>

<?php include "footer.html"; ?>

<script>
let miniMap = null; 
function ucfirst(str) {
    if (typeof str !== 'string' || str.length === 0) return str;
    return str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

function viewIncident(incidentId) {
    const modal = document.getElementById("incidentModal");
    const modalBody = document.getElementById("modalBody");
    modal.style.display = "block";
    modalBody.innerHTML = "Loading…";
    
    fetch("fetch_incident.php?id=" + incidentId)
        .then(res => {
            if (!res.ok) {
                throw new Error('HTTP status ' + res.status);
            }
            return res.text();
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                modalBody.innerHTML = `<p style='color:red;'>**Fatal JSON Error: Server returned invalid data.**</p>
                                       <p>This is often caused by a PHP error/warning in fetch_incident.php or an included file.</p>
                                       <p><strong>Raw Server Response:</strong></p>
                                       <pre style="white-space: pre-wrap; word-wrap: break-word; background: #eee; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: scroll;">${text}</pre>`;
                console.error("JSON Parsing Error. Raw response:", text);
                return;
            }

            if(data.error){ 
                modalBody.innerHTML=`<p style='color:red;'>Failed to load details: ${data.error}</p>`; 
                return; 
            }
            const inc = data.incident;
            const files = data.files;
            
            let fileHTML = "<p>No attachments.</p>";
            if(files.length > 0){
                fileHTML = files.map(f=>{
                    const ext = f.file_path.split(".").pop().toLowerCase();
                    const fullPath = f.file_path.startsWith('uploads/') ? f.file_path : 'uploads/incidents/' + f.file_path; 
                    if(["jpg","jpeg","png","gif","webp"].includes(ext)){
                        return `<img src="${fullPath}" alt="Attachment" style="max-width:100%; height:auto; margin-bottom:10px; border-radius:5px; object-fit: contain;">`;
                    }
                    return `<video controls style="max-width:100%; height:auto; margin-bottom:10px; border-radius:5px;"><source src="${fullPath}"></video>`;
                }).join("<hr style='border-color:#ccc; margin: 10px 0;'>");
                
                fileHTML = `<div style="height: 100%; overflow-y: auto; padding-right: 15px;">${fileHTML}</div>`;
            }
            
            modalBody.innerHTML=`
                <div style="max-height: 100% overflow-y: auto; padding-right: 10px;">
                    <p><strong>Incident ID:</strong> #${String(inc.incident_id).padStart(5,'0')}</p>
                    <p><strong>Type:</strong> ${ucfirst(inc.incident_type.replace(/-/g," "))}</p>
                    <p><strong>Status:</strong> <span class="status-badge ${inc.status}">${ucfirst(inc.status)}</span></p>
                    <p><strong>Date Submitted:</strong> ${inc.submitted_at}</p>
                    <p><strong>Location:</strong> ${inc.location}</p>
                    <p><strong>Barangay:</strong> ${data.barangay_name || inc.barangay_id}</p>
                    <p><strong>Description:</strong><br>${inc.description}</p>
                    <h3 style="margin-top:20px; border-top: 1px solid #ccc; height: 100%; padding-top: 10px;">Attachments</h3>
                    ${fileHTML}
                </div>`;
        })
        .catch(err => {
            console.error('Fetch operation failed:', err);
            modalBody.innerHTML=`<p style='color:red;'>An error occurred during the fetch operation. Check the console for more info.</p>`;
        });
}

function closeIncidentModal(){ document.getElementById("incidentModal").style.display="none"; }
window.onclick=function(e){ if(e.target===document.getElementById("incidentModal")) closeIncidentModal(); }


document.addEventListener('DOMContentLoaded', function() {
    const incidentTypeSelect = document.getElementById('incidentType');
    const otherTypeGroup = document.getElementById('otherTypeGroup');
    const otherTypeInput = document.getElementById('otherIncidentType');

    function toggleOtherTypeInput() {
        if (incidentTypeSelect.value === 'other') {
            otherTypeGroup.style.display = 'block';
            otherTypeInput.setAttribute('required', 'required');
        } else {
            otherTypeGroup.style.display = 'none';
            otherTypeInput.removeAttribute('required');
            otherTypeInput.value = '';
        }
    }

    incidentTypeSelect.addEventListener('change', toggleOtherTypeInput);
    toggleOtherTypeInput(); 

    document.getElementById('fileUpload').addEventListener('change', function(e) {
        const files = e.target.files;
        const uploadText = document.querySelector('.upload-subtext');
        uploadText.textContent = files.length > 0 ? files.length + ' file(s) selected' : 'No files chosen';
    });

    const getLocationBtn = document.getElementById("getLocationBtn");
    
    if (getLocationBtn) {
        getLocationBtn.addEventListener("click", function () {
            const btn = document.getElementById("getLocationBtn");
            const input = document.getElementById("incidentLocation");
            const mapContainer = document.getElementById("mapPreview");
            
            if(!navigator.geolocation){ alert("GPS not supported."); return; }
            btn.textContent="Getting location…"; btn.disabled=true;
            
            navigator.geolocation.getCurrentPosition(async function(pos){
                const lat=pos.coords.latitude, lng=pos.coords.longitude;
                try{
                    const data=await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`).then(r=>r.json());
                    input.value=data.display_name||"Unknown location"; mapContainer.style.display="block";
                    
                    if(miniMap) miniMap.remove();
                    miniMap=L.map("mapPreview",{center:[lat,lng],zoom:18,closePopupOnClick:false});
                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{maxZoom:19}).addTo(miniMap);
                    L.marker([lat,lng]).addTo(miniMap).bindPopup("Your Current Location").openPopup();
                    
                    setTimeout(()=>miniMap.invalidateSize(),300); btn.textContent="Location Added ✔"; btn.disabled=false;
                }catch(err){ 
                    console.error(err); 
                    alert("Address not retrieved."); 
                    btn.textContent="Use My Current Location"; 
                    btn.disabled=false; 
                }
            }, function(){ 
                alert("Permission denied."); 
                btn.textContent="Use My Current Location"; 
                btn.disabled=false; 
            });
        });
    }
});

function clearForm() { 
    document.querySelector('.incident-form').reset(); 
    document.querySelector('.upload-subtext').textContent = 'No files chosen'; 
    document.getElementById("mapPreview").style.display = "none";
    
    const otherTypeGroup = document.getElementById("otherTypeGroup");
    const otherTypeInput = document.getElementById("otherIncidentType");
    if (otherTypeGroup) otherTypeGroup.style.display = "none";
    if (otherTypeInput) {
        otherTypeInput.value = "";
        otherTypeInput.removeAttribute('required');
    }

    if (miniMap) {
        miniMap.remove();
        miniMap = null;
    }
}
</script>

</body>
</html>