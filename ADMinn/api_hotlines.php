    <?php
    require_once 'config.php';
    $db = (new Database())->getConnection();

    if (isset($_GET['action']) && $_GET['action'] === 'hotlines') {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        
        error_log("REQUEST_METHOD: " . $method);
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        if ($method === 'GET') {
            $search = isset($_GET['search']) ? "%" . trim($_GET['search']) . "%" : '%';
            
            $stmt = $db->prepare("
                SELECT hotlines_id, agency_name, description, phone_number, landline_number, logo_type, created_at, updated_at
                FROM hotlines
                WHERE agency_name LIKE ? OR phone_number LIKE ? OR landline_number LIKE ? OR description LIKE ?
                ORDER BY hotlines_id DESC
            ");
            $stmt->execute([$search, $search, $search, $search]); 
            $hotlines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true, 'data'=>$hotlines]);
            exit;
        }

        if ($method === 'POST' && !isset($_POST['_method'])) {
            $agency_name = trim($_POST['agency_name'] ?? '');
            $phone_number = trim($_POST['phone_number'] ?? '');
            
            if (empty($agency_name) || empty($phone_number)) {
                echo json_encode(['success'=>false, 'message'=>'Agency Name and Phone are required.']);
                exit;
            }

            $description = trim($_POST['description'] ?? '');
            $landline_number = trim($_POST['landline_number'] ?? '');
            $logo_type = null;

            if (!empty($_FILES['logo']['name'])) {
                $file = $_FILES['logo'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                
                if (in_array(strtolower($ext), $allowed)) {
                    $filename = uniqid() . '.' . $ext;
                    $target = 'uploads/logos/' . $filename;
                    if (!is_dir('uploads/logos')) mkdir('uploads/logos', 0777, true);
                    if (move_uploaded_file($file['tmp_name'], $target)) $logo_type = $target;
                }
            }

            $stmt = $db->prepare("INSERT INTO hotlines (agency_name, description, phone_number, landline_number, logo_type, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())");
            $success = $stmt->execute([$agency_name, $description, $phone_number, $landline_number, $logo_type]);
            
            $lastId = $db->lastInsertId();

            echo json_encode(['success'=>$success, 'id'=>$lastId]);
            exit;
        }

        if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] == 'PUT') {
            $id = $_POST['hotlines_id'] ?? null;
            if (!$id) { 
                echo json_encode(['success'=>false, 'message'=>'ID required']); 
                exit; 
            }

            $agency_name = trim($_POST['agency_name'] ?? '');
            $phone_number = trim($_POST['phone_number'] ?? '');
            
            if (empty($agency_name) || empty($phone_number)) {
                echo json_encode(['success'=>false, 'message'=>'Agency Name and Phone are required for update.']);
                exit;
            }
            
            $description = trim($_POST['description'] ?? '');
            $landline_number = trim($_POST['landline_number'] ?? '');
            $logo_type = null;
            $delete_old_logo = false;

            if (!empty($_FILES['logo']['name'])) {
                $file = $_FILES['logo'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                    $target = 'uploads/logos/' . uniqid() . '.' . $ext;
                    if (!is_dir('uploads/logos')) mkdir('uploads/logos', 0777, true);
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        $logo_type = $target;
                        $delete_old_logo = true;
                    }
                }
            }

            $sql = "UPDATE hotlines SET agency_name=?, description=?, phone_number=?, landline_number=?, updated_at=NOW()";
            $params = [$agency_name, $description, $phone_number, $landline_number];
            
            if ($logo_type) { 
                $stmt_old = $db->prepare("SELECT logo_type FROM hotlines WHERE hotlines_id = ?");
                $stmt_old->execute([$id]);
                $old_hotline = $stmt_old->fetch(PDO::FETCH_ASSOC);

                $sql .= ", logo_type=?"; 
                $params[] = $logo_type; 
            }
            
            $sql .= " WHERE hotlines_id=?";
            $params[] = $id;

            $stmt = $db->prepare($sql);
            $success = $stmt->execute($params);

            if ($success && $logo_type && !empty($old_hotline['logo_type']) && file_exists($old_hotline['logo_type'])) {
                unlink($old_hotline['logo_type']);
            }

            echo json_encode(['success'=>$success]);
            exit;
        }

        if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] == 'DELETE') {
            $ids = $_POST['hotlines_id'] ?? null; 
            
            if (empty($ids)) { 
                echo json_encode(['success'=>false, 'message'=>'ID required']); 
                exit; 
            }
            
            $id_array = explode(',', $ids);
            $sanitized_ids = array_filter($id_array, 'is_numeric'); 
            
            if (empty($sanitized_ids)) {
                echo json_encode(['success'=>false, 'message'=>'Invalid IDs provided']);
                exit;
            }

            $placeholders = implode(',', array_fill(0, count($sanitized_ids), '?'));
            $stmt_select = $db->prepare("SELECT logo_type FROM hotlines WHERE hotlines_id IN ({$placeholders})");
            $stmt_select->execute($sanitized_ids);
            $hotlines_to_delete = $stmt_select->fetchAll(PDO::FETCH_COLUMN);

            $stmt_delete = $db->prepare("DELETE FROM hotlines WHERE hotlines_id IN ({$placeholders})");
            $success = $stmt_delete->execute($sanitized_ids);
            
            if ($success) {
                foreach ($hotlines_to_delete as $logo_type) {
                    if (!empty($logo_type) && file_exists($logo_type)) {
                        unlink($logo_type);
                    }
                }
            }
            
            echo json_encode(['success'=>$success, 'deleted_count'=>count($sanitized_ids)]);
            exit;
        }
        
        $debug_info = [
            'method' => $method,
            '_method' => $_POST['_method'] ?? 'not set',
            'has_agency_name' => isset($_POST['agency_name']),
            'has_phone_number' => isset($_POST['phone_number'])
        ];
        echo json_encode(['success'=>false, 'message'=>'Invalid request method', 'debug'=>$debug_info]);
        exit;
    }
    ?>
    <!doctype html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Emergency Hotlines – Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
    body.ad-hot-body{
        background: linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)), url('chujjrch.jpeg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: #111; line-height: 1.6; }
    .dashboard-container { max-width: 1300px; margin: 0 auto; padding: 20px; }
    .page-title { font-size: 28px; font-weight: 800; color: var(--red-1); margin-bottom: 10px; }
    .page-subtitle { color: var(--muted); font-size: 14px; margin-bottom: 30px; }

    .controls-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap; }
    .search-box { flex: 1; max-width: 400px; position: relative; }
    .search-box input { width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #e0e0e0; border-radius: var(--radius); }
    .search-box svg { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999; }

    .btn { padding: 12px 24px; border: none; border-radius: var(--radius); font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
    .btn-primary { background: #b91e1eff; color: white;  }
    .btn-primary:hover { background: #7f0303ff; transform: translateY(-2px); }
    .btn-danger { background: var(--danger); color: white; display: none; }
    .btn-danger:hover { background: #c0392b; }

    .table-container { background: white; border-radius: var(--radius); box-shadow: 0 8px 24px rgba(16,24,40,0.06); overflow: hidden; }
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead { background: #f8f9fa; }
    th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    th { font-weight: 600; color: #555; font-size: 14px; border-bottom: 2px solid #e0e0e0; }

    .checkbox-cell { width: 40px; text-align: center; }
    .logo-cell { width: 80px; }
    .actions-cell { text-align: right; width: 180px; }

    .logo-preview { width: 50px; height: 50px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .logo-preview img { width: 100%; height: 100%; object-fit: cover; }

    .edit-btn { padding: 6px 12px; border: 1px solid #e0e0e0; border-radius: 6px; background: darkgreen; color: white; font-size: 12px; cursor: pointer; margin-left: 5px; }
    .edit-btn:hover { background: #167c04ff; }
    .action-btn{ padding: 6px 12px; border: 1px solid #e0e0e0; border-radius: 6px; background: red; color: white; font-size: 12px; cursor: pointer; margin-left: 5px; }
    .del-btn:hover { background: #f8f9fa; }
    .btn-del-single { color: var(--danger); border-color: #fadbd8; color: whitesmoke; }
    .btn-del-single:hover { background: #5f0701ff; }

    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal-content { background: white; border-radius: var(--radius); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
    .upload-area { border: 2px dashed #e0e0e0; padding: 20px; text-align: center; cursor: pointer; border-radius: 8px; }
    .alert { padding: 12px 20px; border-radius: var(--radius); margin-bottom: 20px; display: none; }
    .alert-success { background: #d4edda; color: #155724; }
    .alert-error { background: #f8d7da; color: #721c24; }
    </style>
    </head>
    <body class="ad-hot-body">

    <?php include 'adm_header.php'; ?>

    <div class="dashboard-container" style="background-color: white; border-radius:25px; margin-top: 5%; margin-bottom: 5%; padding: 30px;">
        <div class="page-header">
            <h1 class="page-title">Emergency Hotlines Management</h1>
            <p class="page-subtitle">Manage emergency contact numbers and agency information</p>
        </div>

        <div class="controls-row">
            <div class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="searchInput" placeholder="Search agencies...">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-danger" id="bulkDeleteBtn" onclick="bulkDeleteHotlines()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Delete Selected (<span id="selectedCount">0</span>)
                </button>
                
                <button class="btn btn-primary" id="addHotlineBtn">
                  + Add New Hotline
                </button>
            </div>
        </div>

        <div id="alertBox" class="alert"></div>

        <div class="table-container">
            <div class="table-header" style="padding: 20px;">
                <h3 style="font-size: 18px; font-weight: 700;">Emergency Hotlines</h3>
                <p style="font-size: 13px; color: #666;">Total <span id="totalCount">0</span> hotlines registered</p>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" id="selectAll" title="Select All">
                            </th>
                            <th class="logo-cell">Logo</th>
                            <th>Agency Name</th>
                            <th>Description</th>
                            <th>Phone</th>
                            <th>Landline</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="hotlinesTableBody">
                    </tbody>
                </table>
            </div>
            <div id="noDataMessage" style="text-align: center; padding: 40px; color: #999; display: none;">No hotlines found.</div>
        </div>
    </div>

    <div class="modal" id="hotlineModal">
        <div class="modal-content">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <h3 class="modal-title" id="modalTitle">Add New Hotline</h3>
                <span id="closeModal" style="cursor:pointer; font-size:24px;">&times;</span>
            </div>
            <form id="hotlineForm" enctype="multipart/form-data">
                <input type="hidden" name="hotlines_id" id="hotlineId">
                
                <div class="form-group">
                    <label>Agency Name *</label>
                    <input type="text" name="agency_name" id="agency_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="tel" name="phone_number" id="phone_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Landline</label>
                        <input type="tel" name="landline_number" id="landline_number" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Logo</label>
                    <div class="upload-area" id="uploadArea">
                        <span style="font-size:30px;">📷</span><br>Click to upload
                        <input type="file" name="logo" id="logo" style="display:none;" accept="image/*">
                    </div>
                    <div id="previewContainer" style="text-align:center; margin-top:10px; display:none;">
                        <img id="logoPreview" style="max-height:100px; border-radius:5px;">
                    </div>
                </div>
                <div style="text-align:right; margin-top:20px;">
                    <button type="button" class="btn" id="cancelBtn" style="background:#eee;">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <?php include "footer.html"; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('hotlinesTableBody');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectAllCheckbox = document.getElementById('selectAll');
        console.log('Select All Checkbox:', selectAllCheckbox);
        const searchInput = document.getElementById('searchInput');
        const modal = document.getElementById('hotlineModal');
        const form = document.getElementById('hotlineForm');
        
        let selectedHotlines = new Set();
        let currentHotlineId = null;

        function showAlert(msg, type='success') {
            const box = document.getElementById('alertBox');
            box.className = `alert alert-${type}`;
            box.textContent = msg;
            box.style.display = 'block';
            setTimeout(() => box.style.display = 'none', 4000);
        }

        async function loadHotlines(search = '') {
            try {
                const res = await fetch(`?action=hotlines&search=${encodeURIComponent(search)}`);
                const data = await res.json();
                
                selectedHotlines.clear();
                updateBulkUI(); 
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
                
                if (data.success && data.data.length > 0) {
                    tableBody.innerHTML = data.data.map(h => `
                        <tr>
                            <td class="checkbox-cell">
                                <input type="checkbox" class="hotline-checkbox" value="${h.hotlines_id}" onchange="toggleSelection(${h.hotlines_id}, this.checked)">
                            </td>
                            <td>
                                <div class="logo-preview">
                                    ${h.logo_type ? `<img src="${h.logo_type}" onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJMMiAyMWgyMGwtMTAtMTl6bTkgMTloLTQuMjVsMi43NS01LjI1di02Ljc1aDEuNW0tMTQgMGgtMS41djYuNzVsMi43NSA1LjI1aC00LjI1bDEuNS0zLjE1VjIuMjVsLTktMi4yNXoiIGZpbGw9IiM2YjcyODAiLz48L3N2ZyBzYWZldHkgZW1lcmdlbmN5IGhvdGxpbmUi>" title="🏢 Logo missing">` : '🏢'}
                                </div>
                            </td>
                            <td><strong>${h.agency_name}</strong></td>
                            <td>${h.description || '-'}</td>
                            <td>${h.phone_number}</td>
                            <td>${h.landline_number || '-'}</td>
                            <td class="actions-cell">
                                <button class="edit-btn" onclick="openEdit(${h.hotlines_id})">Edit</button>
                                <button class="action-btn btn-del-single" onclick="deleteHotline(${h.hotlines_id})">Delete</button>
                            </td>
                        </tr>
                    `).join('');
                    document.getElementById('totalCount').textContent = data.data.length;
                    document.getElementById('noDataMessage').style.display = 'none';
                } else {
                    tableBody.innerHTML = '';
                    document.getElementById('totalCount').textContent = '0';
                    document.getElementById('noDataMessage').style.display = 'block';
                }
            } catch (e) { 
                console.error('Error loading hotlines:', e); 
                showAlert('Error loading data', 'error');
            }
        }

       window.toggleSelection = function(id, isChecked) {
            if (isChecked) selectedHotlines.add(id);
            else selectedHotlines.delete(id);
            updateBulkUI();
        }

        function attachSelectAllListener() {
            const selectAllCheckboxElement = document.getElementById('selectAll');
            if (selectAllCheckboxElement) {
                selectAllCheckboxElement.removeEventListener('change', handleSelectAll);
                selectAllCheckboxElement.addEventListener('change', handleSelectAll);
            }
        }

        function handleSelectAll(e) {
            const checkboxes = document.querySelectorAll('.hotline-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                const id = parseInt(cb.value);
                if(this.checked) selectedHotlines.add(id);
                else selectedHotlines.delete(id);
            });
            updateBulkUI();
        }

        function updateBulkUI() {
            const count = selectedHotlines.size;
            selectedCountSpan.textContent = count;
            bulkDeleteBtn.style.display = count > 0 ? 'inline-flex' : 'none';
            
            const all = document.querySelectorAll('.hotline-checkbox');
            if(all.length > 0 && selectAllCheckbox) {
                selectAllCheckbox.disabled = false; 
                if(count === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else if(count === all.length) { 
                    selectAllCheckbox.checked = true; 
                    selectAllCheckbox.indeterminate = false; 
                } else { 
                    selectAllCheckbox.checked = false; 
                    selectAllCheckbox.indeterminate = true; 
                }
            } else if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.disabled = (all.length === 0);
            }
        }

        attachSelectAllListener();

        window.bulkDeleteHotlines = async function() {
            if (selectedHotlines.size === 0) return;
            if (!confirm(`Are you sure you want to delete ${selectedHotlines.size} agencies?`)) return;

            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.innerHTML = 'Deleting...';

            const idsToDelete = Array.from(selectedHotlines);
            
            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('hotlines_id', idsToDelete.join(','));
                
                const res = await fetch('?action=hotlines', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    showAlert(`Successfully deleted ${data.deleted_count || idsToDelete.length} hotlines.`);
                } else {
                    showAlert(data.message || 'Error performing bulk delete.', 'error');
                }
            } catch (e) { 
                console.error('Bulk Delete error:', e); 
                showAlert('A network error occurred during bulk deletion.', 'error');
            }

            bulkDeleteBtn.disabled = false;
            bulkDeleteBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete Selected (<span id="selectedCount">0</span>)`;
            loadHotlines(searchInput.value);
        }

        window.deleteHotline = async function(id) {
            if(!confirm("Delete this hotline?")) return;
            try {
                const fd = new FormData(); 
                fd.append('_method','DELETE'); 
                fd.append('hotlines_id', id); 
                const res = await fetch('?action=hotlines', { method:'POST', body:fd });
                const data = await res.json();
                if (data.success) {
                    showAlert('Deleted successfully');
                    loadHotlines();
                } else {
                    showAlert('Error deleting', 'error');
                }
            } catch(e) {
                console.error('Delete error:', e);
                showAlert('Error deleting', 'error');
            }
        }
        
        document.getElementById('addHotlineBtn').onclick = () => {
            currentHotlineId = null;
            form.reset();
            
            const methodField = document.getElementById('formMethod');
            if (methodField) {
                methodField.remove();
            }
            
            document.getElementById('hotlineId').value = '';
            document.getElementById('modalTitle').textContent = 'Add New Hotline';
            document.getElementById('previewContainer').style.display = 'none';
            modal.style.display = 'flex';
        };

        window.openEdit = async (id) => {
            try {
                const res = await fetch(`?action=hotlines&search=`);
                const data = await res.json();
                const item = data.data.find(i => i.hotlines_id == id);
                
                if(item) {
                    currentHotlineId = id;
                    
                    let methodField = document.getElementById('formMethod');
                    if (!methodField) {
                        methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.id = 'formMethod';
                        form.insertBefore(methodField, form.firstChild);
                    }
                    methodField.value = 'PUT';
                    
                    document.getElementById('hotlineId').value = id;
                    document.getElementById('agency_name').value = item.agency_name;
                    document.getElementById('description').value = item.description;
                    document.getElementById('phone_number').value = item.phone_number;
                    document.getElementById('landline_number').value = item.landline_number;
                    document.getElementById('modalTitle').textContent = 'Edit Hotline';
                    
                    if(item.logo_type) {
                        document.getElementById('logoPreview').src = item.logo_type;
                        document.getElementById('previewContainer').style.display = 'block';
                    } else {
                        document.getElementById('logoPreview').src = '';
                        document.getElementById('previewContainer').style.display = 'none';
                    }
                    modal.style.display = 'flex';
                }
            } catch(e) {
                console.error('Error loading hotline:', e);
                showAlert('Error loading data', 'error');
            }
        };

        form.onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            btn.disabled = true; 
            btn.textContent = 'Saving...';
            
            try {
                const formData = new FormData(form);
                
                console.log('Submitting form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ':', value);
                }
                
                const res = await fetch('?action=hotlines', { 
                    method:'POST', 
                    body: formData
                });
                
                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await res.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response');
                }
                
                const data = await res.json();
                console.log('Server response:', data);
                
                if(data.success) {
                    modal.style.display = 'none';
                    showAlert('Saved successfully');
                    loadHotlines(searchInput.value); 
                } else {
                    const errorMsg = data.message || 'Error saving';
                    const debugInfo = data.debug ? '\n\nDebug Info:\n' + JSON.stringify(data.debug, null, 2) : '';
                    showAlert(errorMsg + debugInfo, 'error');
                    console.error('Save failed:', data);
                }
            } catch(e) { 
                console.error('Save error:', e); 
                showAlert('Error saving data: ' + e.message, 'error');
            }
            
            btn.disabled = false; 
            btn.textContent = 'Save';
        };

        searchInput.oninput = (e) => {
            e.preventDefault(); 
            loadHotlines(e.target.value);
        }
        
        document.getElementById('closeModal').onclick = () => modal.style.display = 'none';
        document.getElementById('cancelBtn').onclick = () => modal.style.display = 'none';
        document.getElementById('uploadArea').onclick = () => document.getElementById('logo').click();
        document.getElementById('logo').onchange = (e) => {
            if(e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    document.getElementById('logoPreview').src = ev.target.result;
                    document.getElementById('previewContainer').style.display = 'block';
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        };
        loadHotlines();
    });
    </script>
    </body>
    </html>