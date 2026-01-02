<?php
require_once 'config.php';
$db = (new Database())->getConnection();

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $search = isset($_GET['search']) ? "%{$_GET['search']}%" : '%';
        $stmt = $db->prepare("
            SELECT hotline_id, agency_name, description, phone_number, landline_number, logo_type, created_at, updated_at
            FROM hotlines
            WHERE agency_name LIKE ? OR phone_number LIKE ? OR landline_number LIKE ?
            ORDER BY hotline_id DESC
        ");
        $stmt->execute([$search, $search, $search]);
        $hotlines = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true, 'data'=>$hotlines]);
        exit;
    }

    if ($method === 'POST') {
        $agency_name = $_POST['agency_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $landline_number = $_POST['landline_number'] ?? '';
        $logo_type = null;

        if (!empty($_FILES['logo']['name'])) {
            $file = $_FILES['logo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $target = 'uploads/logos/' . uniqid() . '.' . $ext;
            if (!is_dir('uploads/logos')) mkdir('uploads/logos',0777,true);
            move_uploaded_file($file['tmp_name'],$target);
            $logo_type = $target;
        }

        $stmt = $db->prepare("
            INSERT INTO hotlines (agency_name, description, phone_number, landline_number, logo_type, created_at, updated_at)
            VALUES (?,?,?,?,?,NOW(),NOW())
        ");
        $success = $stmt->execute([$agency_name, $description, $phone_number, $landline_number, $logo_type]);
        echo json_encode(['success'=>$success]);
        exit;
    }

    if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method']=='PUT') {
        $id = $_POST['hotline_id'] ?? null;
        if (!$id) { echo json_encode(['success'=>false]); exit; }

        $agency_name = $_POST['agency_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $landline_number = $_POST['landline_number'] ?? '';
        $logo_type = null;

        if (!empty($_FILES['logo']['name'])) {
            $file = $_FILES['logo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $target = 'uploads/logos/' . uniqid() . '.' . $ext;
            if (!is_dir('uploads/logos')) mkdir('uploads/logos',0777,true);
            move_uploaded_file($file['tmp_name'],$target);
            $logo_type = $target;
        }

        $sql = "UPDATE hotlines SET agency_name=?, description=?, phone_number=?, landline_number=?, updated_at=NOW()";
        $params = [$agency_name, $description, $phone_number, $landline_number];

        if ($logo_type) { $sql .= ", logo_type=?"; $params[]=$logo_type; }

        $sql .= " WHERE hotline_id=?";
        $params[]=$id;

        $stmt=$db->prepare($sql);
        $success=$stmt->execute($params);
        echo json_encode(['success'=>$success]);
        exit;
    }

    if ($method==='POST' && isset($_POST['_method']) && $_POST['_method']=='DELETE') {
        $id=$_POST['hotline_id']??null;
        if(!$id){ echo json_encode(['success'=>false]); exit; }
        $stmt=$db->prepare("DELETE FROM hotlines WHERE hotline_id=?");
        $success=$stmt->execute([$id]);
        echo json_encode(['success'=>$success]);
        exit;
    }

    echo json_encode(['success'=>false]);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Emergency Hotlines — Municipality</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--red-1:#b72a22;--red-2:#c7463f;--muted:#6b7280;--card:#fff;--bg:#f5f6f8;--shadow:0 8px 24px rgba(16,24,40,0.06);--radius:10px;--danger:#e74c3c;--pill:#f4f6f8;--green:#1db954}[data-theme="dark"]{--bg:#0f1720;--card:#0b1220;--muted:#9aa0a6}[data-theme="dark"] .topbar{box-shadow:0 6px 18px rgba(0,0,0,0.5)}*{box-sizing:border-box}html,body{height:100%;margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial;background:var(--bg);color:#111}a{color:inherit;text-decoration:none}.app{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.sidebar{background:linear-gradient(180deg,var(--red-1),var(--red-2));color:#fff;padding:26px;display:flex;flex-direction:column;gap:18px}.brand{display:flex;gap:12px;align-items:center}.crest{width:48px;height:48px;border-radius:8px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-weight:800}.brand h1{font-size:16px;margin:0;font-weight:800}.nav-section{font-size:13px;opacity:0.95;margin-top:8px;margin-bottom:6px}.nav a{display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;color:rgba(255,255,255,0.95)}.nav a.active{background:rgba(0,0,0,0.12);font-weight:700}.logout{margin-top:auto;background:rgba(255,255,255,0.12);padding:10px;border-radius:8px;text-align:center;font-weight:700;cursor:pointer}.main{display:flex;flex-direction:column;min-height:100vh}.topbar{display:flex;align-items:center;justify-content:space-between;padding:12px 28px;background:linear-gradient(90deg,var(--red-1),var(--red-2));color:#fff;box-shadow:0 4px 14px rgba(0,0,0,0.06)}.topnav{display:flex;gap:18px;align-items:center}.topnav .tab{padding:8px 12px;border-radius:8px;font-weight:600;cursor:pointer;opacity:0.95}.topnav .tab.active{background:#fff;color:var(--red-1)}.top-actions{display:flex;gap:12px;align-items:center}.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:none;cursor:pointer;font-weight:700}.btn.primary{background:var(--danger);color:#fff}.btn.ghost{background:#fff;border:1px solid rgba(0,0,0,0.08);color:var(--red-1)}.search-top{display:flex;align-items:center;gap:8px;background:var(--pill);padding:8px 12px;border-radius:8px}.search-top input{border:0;background:transparent;outline:none;font-size:14px}.user-bubble{width:34px;height:34px;border-radius:50%;background:#ffd88f;color:var(--red-1);display:flex;align-items:center;justify-content:center;font-weight:800}.content{padding:28px 36px 60px;flex:1; position:relative}.page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.page-title{font-size:24px;font-weight:800}.head-actions{display:flex;gap:12px;align-items:center}.controls-row{display:flex;gap:12px;align-items:center;margin-bottom:18px}.search-large{display:flex;align-items:center;gap:8px;background:#fff;padding:10px;border-radius:8px;border:1px solid #e9edf0;flex:1}.search-large input{border:0;outline:none;font-size:14px}.add-btn{white-space:nowrap}.table-card{background:var(--card);padding:0;border-radius:10px;box-shadow:var(--shadow);overflow:hidden}.table-head{padding:16px;border-bottom:1px solid #f1f3f5}.table-head h4{margin:0;font-size:16px}.table-sub{font-size:13px;color:var(--muted);margin-top:6px;padding-bottom:8px}table{width:100%;border-collapse:collapse}thead th{padding:12px 16px;text-align:left;font-weight:700;font-size:13px;color:var(--muted);background:#fafafa;border-bottom:1px solid #eef0f2}tbody td{padding:14px 16px;border-bottom:1px solid #f6f6f8;vertical-align:middle}.col-logo{text-align:center;width:64px}.agency-col{width:200px}.desc-col{min-width:300px}.phone-col{width:160px}.landline-col{width:160px}.actions-col{width:150px;text-align:center}.logo-circle{width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:700;background:#f3f3f3;color:#333}.action-btn{border-radius:6px;padding:8px 10px;border:1px solid #eef0f2;background:#fff;cursor:pointer}.action-del{background:var(--danger);color:#fff;border:none}footer{grid-column:1/-1;background:linear-gradient(180deg,var(--red-1),var(--red-2));color:#fff;padding:36px 48px;margin-top:28px}.footer-grid{display:grid;grid-template-columns:320px 1fr 160px 160px;gap:24px}.footer .crest{width:72px;height:72px;border-radius:12px;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;font-weight:800}.footer h6{margin:0 0 8px 0;font-weight:800}.muted{opacity:0.95;font-size:14px;line-height:1.45}.fullpage-bg{background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800"><rect fill="%23f2f3f4" width="100%" height="100%"/></svg>');background-size:cover;background-position:center;min-height:420px;padding:30px;border-radius:8px}.add-page{max-width:980px;margin:18px auto;background:#fff;border-radius:26px;padding:40px 60px;box-shadow:0 12px 40px rgba(0,0,0,0.12);border:1px solid #e6e6e6;position:relative}.add-page .title{display:flex;justify-content:center;align-items:center;gap:12px;font-size:22px;font-weight:800;margin-bottom:18px}.add-page .section-title{font-weight:800;margin-top:6px;margin-bottom:12px}.add-page .form-grid{display:flex;gap:36px;align-items:flex-start}.left-col{width:220px;text-align:center}.upload-box{width:160px;height:160px;border-radius:12px;border:2px dashed #e6e6e6;display:flex;align-items:center;justify-content:center;background:#fafafa;color:#999;margin:10px auto;font-size:36px}.file-input{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:1px solid #e9edf0;background:#fff;cursor:pointer;margin-top:8px}.field{margin-bottom:12px}.field label{display:block;font-weight:700;margin-bottom:6px;color:#222}.field input[type="text"],.field textarea,.field input[type="tel"]{width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e9edf0;font-size:14px;background:#fff}.field textarea{min-height:110px;resize:vertical}.save-row{display:flex;justify-content:flex-end;margin-top:18px}.save-btn{display:inline-block;background:linear-gradient(180deg,#2b8c2b,#1f6f1f);color:#fff;padding:10px 18px;border-radius:999px;border:none;cursor:pointer;box-shadow:0 6px 18px rgba(0,0,0,0.12)}.back-btn{position:absolute;left:24px;top:22px;background:#fff;border-radius:8px;padding:6px 10px;border:1px solid #eee;cursor:pointer;color:var(--red-1);font-weight:700}@media(max-width:1100px){.app{grid-template-columns:72px 1fr}.add-page{padding:20px}.add-page .form-grid{flex-direction:column}.left-col{width:auto;text-align:center}}@media(max-width:720px){.topnav{display:none}.page-head{flex-direction:column;gap:12px}.controls-row{flex-direction:column}.search-large{width:100%}.add-page{padding:18px}}
@media(max-width:720px){.topnav{display:none}.page-head{flex-direction:column;gap:12px}.controls-row{flex-direction:column}.search-large{width:100%}.add-page{padding:18px}}

/* Edit Details container (compact details-style edit view) */
.details-container{max-width:680px;margin:36px auto;background:var(--card);padding:24px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.06)}
.details-header{text-align:center;margin-bottom:18px}
.details-header img{width:120px;height:120px;object-fit:contain;margin-bottom:10px;border-radius:8px}
.details-body p{margin:8px 0;font-size:15px}
.edit-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:14px}
.btn-cancel{background:#fff;border:1px solid #e6e6e6;padding:8px 12px;border-radius:8px;cursor:pointer}
.btn-save{background:linear-gradient(180deg,#2b8c2b,#1f6f1f);color:#fff;padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
</style>
</style>
</head>
<body>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="crest">MD</div><div><h1>Municipality</h1><div style="font-size:13px;opacity:0.95">Incident Reporting</div></div></div>
<div class="nav-section">OFFICIAL TOOLS</div>
<nav class="nav" aria-label="Primary">
<a class="active" href="#">Analytics Dashboard</a>
<a href="#">Heatmap</a>
<a href="#">Users</a>
<div class="nav-section">EXPLORE</div>
<a href="#">Hotlines</a>
<a href="#">Incident Management</a>
<a href="#">Resources</a>
<a href="#">Disaster Alerts</a>
<a href="#">Announcements</a>
<a href="#">Settings</a>
</nav>
<div class="logout">🔒 Logout</div>
</aside>
<!-- MAIN -->
<div class="main">
<div class="topbar">
<div class="topnav" role="navigation">
<div class="tab">Analytics</div>
<div class="tab">Heatmap</div>
<div class="tab">Users</div>
<div class="tab active">Hotlines</div>
<div class="tab">Incidents</div>
<div class="tab">Resources</div>
<div class="tab">Disaster</div>
<div class="tab">Announcements</div>
</div>
<div class="top-actions">
<div class="search-top">🔍 <input placeholder="Search agencies or numbers..." id="globalSearchTop" /></div>
<div class="user-bubble">NP</div>
</div>
</div>
<div class="content" id="contentArea">
<div id="listView">
<div class="page-head">
<div class="page-title">Emergency Hotlines</div>
<div class="head-actions"><button id="openAddBtn" class="btn primary add-btn">+ Add New Contact</button></div>
</div>
<div class="controls-row">
<div class="search-large">🔍 <input id="searchInput" placeholder="Search agencies or numbers..." /></div>
<div style="width:160px;display:flex;justify-content:flex-end"><button class="btn ghost">Take Actions ▾</button></div>
</div>
<div class="table-card" role="region" aria-label="Hotlines list">
<div class="table-head"><h4>Hotlines</h4><div class="table-sub">Reference for emergency contact numbers.</div></div>
<table><thead><tr>
<th class="col-logo">Logo</th>
<th class="agency-col">Agency</th>
<th class="desc-col">Description</th>
<th class="phone-col">Phone Number</th>
<th class="landline-col">Landline Number</th>
<th class="actions-col">Actions</th>
</tr></thead>
<tbody id="hotlinesTbody"></tbody>
</table>
</div>
</div>
<div id="addView" style="display:none;">
<div class="fullpage-bg">
<button id="backBtn" class="back-btn">← Back</button>
<div class="add-page" role="main" aria-labelledby="addTitle">
<div class="title" id="addTitle">🕘 Add New Contact</div>
<div class="section-title">Agency Details</div>
<div class="form-grid">
<div class="left-col">
<label style="font-weight:700">Agency Logo</label>
<div class="upload-box" id="logoBox">📷</div>
<div style="font-size:13px;color:#666;margin-top:8px">Upload Photos/Videos (Optional)</div>
<div style="margin-top:10px">
<label class="file-input" id="filePicker">⤓ Select Files<input id="fileInput" type="file" accept="image/*,video/*" style="display:none" /></label>
</div>
</div>
<div style="flex:1;">
<div class="field"><label>Agency Name</label><input type="text" id="agencyName"></div>
<div class="field"><label>Description</label><textarea id="agencyDesc"></textarea></div>
<div class="field"><label>Phone Number</label><input type="tel" id="agencyPhone"></div>
<div class="field"><label>Landline Number</label><input type="tel" id="agencyLandline"></div>
<div class="save-row"><button class="save-btn" id="saveBtn">Save Contact</button></div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- Edit Details View -->
<div id="editView" style="display:none;">
    <div class="details-container" role="dialog" aria-labelledby="editTitle">
        <div class="details-header">
            <img id="editLogoImg" src="" alt="Logo" />
            <h2 id="editTitle">Edit Contact</h2>
        </div>
        <div class="details-body">
            <div class="field"><label>Agency Name</label><input type="text" id="editAgencyName"></div>
            <div class="field"><label>Description</label><textarea id="editAgencyDesc"></textarea></div>
            <div class="field"><label>Phone Number</label><input type="tel" id="editAgencyPhone"></div>
            <div class="field"><label>Landline Number</label><input type="tel" id="editAgencyLandline"></div>
            <div style="margin-top:12px"><label style="font-weight:700">Replace Logo (optional)</label>
                <div style="display:flex;gap:12px;align-items:center;margin-top:8px">
                    <label class="file-input">⤓ Select File<input id="editFileInput" type="file" accept="image/*,video/*" style="display:none" /></label>
                    <div id="editFileName" style="color:var(--muted)"></div>
                </div>
            </div>
        </div>
        <div class="edit-actions">
            <button id="cancelEditBtn" class="btn-cancel">Cancel</button>
            <button id="saveEditBtn" class="btn-save">Save Changes</button>
        </div>
    </div>
</div>

<footer>
<div class="footer-grid">
<div>
<div class="crest">MD</div>
<h6>Municipality</h6>
<div class="muted">Emergency & Incident Management System</div>
</div>
<div>
<h6>Quick Links</h6>
<div class="muted">Hotlines | Incidents | Resources | Alerts</div>
</div>
<div>
<h6>Support</h6>
<div class="muted">Email: support@municipality.gov</div>
</div>
<div>
<h6>Follow Us</h6>
<div class="muted">Facebook | Twitter | Instagram</div>
</div>
</div>
</footer>
<script>
const contentArea=document.getElementById('contentArea');
const listView=document.getElementById('listView');
const addView=document.getElementById('addView');
const editView=document.getElementById('editView');
const openAddBtn=document.getElementById('openAddBtn');
const backBtn=document.getElementById('backBtn');
const hotlinesTbody=document.getElementById('hotlinesTbody');
const saveBtn=document.getElementById('saveBtn');
const fileInput=document.getElementById('fileInput');
const logoBox=document.getElementById('logoBox');

const editLogoImg=document.getElementById('editLogoImg');
const editFileInput=document.getElementById('editFileInput');
const editFileName=document.getElementById('editFileName');
const saveEditBtn=document.getElementById('saveEditBtn');
const cancelEditBtn=document.getElementById('cancelEditBtn');
const editAgencyName=document.getElementById('editAgencyName');
const editAgencyDesc=document.getElementById('editAgencyDesc');
const editAgencyPhone=document.getElementById('editAgencyPhone');
const editAgencyLandline=document.getElementById('editAgencyLandline');

let currentEditId = null;
let cachedHotlines = [];

openAddBtn.onclick=()=>{listView.style.display='none';addView.style.display='block';logoBox.innerHTML='📷';fileInput.value='';saveBtn.textContent='Save Contact';currentEditId=null;}
backBtn.onclick=()=>{addView.style.display='none';listView.style.display='block';fetchHotlines();}
fileInput.onchange=()=>{if(fileInput.files[0]){logoBox.textContent=fileInput.files[0].name;}};

editFileInput.onchange = ()=>{
    const f = editFileInput.files[0];
    if(!f){ editFileName.textContent=''; return; }
    editFileName.textContent = f.name;
    if(f.type && f.type.startsWith('image/')){
        editLogoImg.src = URL.createObjectURL(f);
    }
}

function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function fetchHotlines(search=''){
    fetch('?action=hotlines&search='+encodeURIComponent(search))
    .then(r=>r.json())
    .then(res=>{
        hotlinesTbody.innerHTML='';
        if(res.success){
            cachedHotlines = res.data;
            res.data.forEach(h=>{
                const tr=document.createElement('tr');
                tr.innerHTML=`
                <td class="col-logo"><div class="logo-circle">${h.logo_type?'<img src="'+h.logo_type+'" style="width:32px;height:32px;border-radius:50%"/>':'N/A'}</div></td>
                <td>${escapeHtml(h.agency_name)}</td>
                <td>${escapeHtml(h.description)}</td>
                <td>${escapeHtml(h.phone_number)}</td>
                <td>${escapeHtml(h.landline_number)}</td>
                <td style="text-align:center">
                <button class="action-btn" onclick="editHotline(${h.hotline_id})">Edit</button>
                <button class="action-btn action-del" onclick="deleteHotline(${h.hotline_id})">Delete</button>
                </td>`;
                hotlinesTbody.appendChild(tr);
            });
        }
    });
}

function saveHotline(){
    const formData=new FormData();
    formData.append('agency_name',document.getElementById('agencyName').value);
    formData.append('description',document.getElementById('agencyDesc').value);
    formData.append('phone_number',document.getElementById('agencyPhone').value);
    formData.append('landline_number',document.getElementById('agencyLandline').value);
    if(fileInput.files[0]) formData.append('logo',fileInput.files[0]);

    fetch('?action=hotlines',{method:'POST',body:formData}).then(r=>r.json()).then(res=>{
        if(res.success){alert('Saved!');addView.style.display='none';listView.style.display='block';fetchHotlines();}
        else alert('Failed to save!');
    });
}
saveBtn.onclick=saveHotline;

function deleteHotline(id){
    if(confirm('Delete this contact?')){
        const fd=new FormData();
        fd.append('_method','DELETE');
        fd.append('hotline_id',id);
        fetch('?action=hotlines',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{
            if(res.success) fetchHotlines();
            else alert('Failed to delete!');
        });
    }
}

function editHotline(id){
    // find in cache
    let h = cachedHotlines.find(x=>x.hotline_id==id);
    const openForm = (record)=>{
        editAgencyName.value = record.agency_name||'';
        editAgencyDesc.value = record.description||'';
        editAgencyPhone.value = record.phone_number||'';
        editAgencyLandline.value = record.landline_number||'';
        editFileInput.value = '';
        editFileName.textContent = '';
        if(record.logo_type){ editLogoImg.src = record.logo_type; }
        else { editLogoImg.src = ''; }
        currentEditId = id;
        listView.style.display='none';
        editView.style.display='block';
    };
    if(h){ openForm(h); return; }
    // fallback: fetch list and try again
    fetch('?action=hotlines').then(r=>r.json()).then(res=>{
        if(!res.success) return alert('Failed to load contact');
        cachedHotlines = res.data;
        h = cachedHotlines.find(x=>x.hotline_id==id);
        if(!h) return alert('Contact not found');
        openForm(h);
    });
}

function saveEdit(){
    if(!currentEditId) return alert('No record selected');
    const fd = new FormData();
    fd.append('_method','PUT');
    fd.append('hotline_id', currentEditId);
    fd.append('agency_name', editAgencyName.value);
    fd.append('description', editAgencyDesc.value);
    fd.append('phone_number', editAgencyPhone.value);
    fd.append('landline_number', editAgencyLandline.value);
    if(editFileInput.files[0]) fd.append('logo', editFileInput.files[0]);

    fetch('?action=hotlines',{method:'POST', body:fd}).then(r=>r.json()).then(res=>{
        if(res.success){
            alert('Updated!');
            currentEditId = null;
            editView.style.display='none';
            listView.style.display='block';
            fetchHotlines();
        } else alert('Failed to update!');
    });
}
saveEditBtn.onclick = saveEdit;
cancelEditBtn.onclick = ()=>{ currentEditId = null; editView.style.display='none'; listView.style.display='block'; };

document.getElementById('searchInput').oninput=e=>fetchHotlines(e.target.value);
fetchHotlines();
</script>
<script>
    
</script>
</body>
</html>
