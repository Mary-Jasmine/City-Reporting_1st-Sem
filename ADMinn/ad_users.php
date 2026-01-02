<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>User Management – Municipality</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
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

                
            .container{
                background-color: whitesmoke;
                border-radius: 2%;
                max-width: 1250px ;
                margin-left: 10%;
                padding:2px;
                flex:1;
                
            }

    :root{
      --red-1:#b72a22;
      --red-2:#c7463f;
      --muted:#6b7280;
      --card:#ffffff;
      --bg:#f5f6f8;
      --shadow:0 8px 24px rgba(16,24,40,0.06);
      --radius:10px;
      --green:#1db954;
      --yellow:#ffbf36;
      --danger:#e74c3c;
      --pill-bg:#f4f6f8;
    }

    *{box-sizing:border-box}
    html,body{height:100%;margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial;color:#111}
    a{color:inherit;text-decoration:none}

    .main-wrapper {display:flex; flex-direction:column; min-height:100vh;}
    .main {flex:1; display:flex; flex-direction:column;}

    .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:none;cursor:pointer;font-weight:700}
    .btn.primary{background:var(--danger);color:#fff}
    .btn.ghost{background:#fff;border:1px solid rgba(0,0,0,0.08);color:var(--red-1)}

    .content{padding:5px;flex:1}
    .page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
    .page-title{font-size:24px;font-weight:800}
    .head-actions{display:flex;gap:12px;align-items:center}

    .filter-card{background:var(--card);margin-left: 5.5%;width: 90%;padding:18px;border-radius:10px;box-shadow:var(--shadow);margin-bottom:18px;display:flex;gap:18px;align-items:center;flex-wrap:wrap}
    .filter-col{flex:1;display:flex;flex-direction:column;gap:8px}
    .filter-row{display:flex;gap:10px;align-items:center}
    .input, select{padding:10px 12px;border-radius:8px;border:1px solid #e9edf0;background:#fff;min-width:0;font-size:14px}
    .search-input{display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;border:1px solid #e9edf0;background:var(--pill-bg);width:320px}
    .search-input input{border:0;background:transparent;outline:none;font-size:14px;width:100%}

    .table-card{background:var(--card);margin-left: 5.5%; width: 90%;padding:0;border-radius:10px;box-shadow:var(--shadow);overflow:hidden}
    .table-head{padding:16px;border-bottom:1px solid #f1f3f5}
    .table-head h4{margin:0;font-size:16px}
    .table-sub{font-size:13px;color:var(--muted);margin-top:6px}

    table{width:100%;border-collapse:collapse}
    thead th{padding:14px 16px;text-align:left;font-weight:700;font-size:13px;color:var(--muted);background:transparent;border-bottom:1px solid #f3f4f6}
    tbody td{padding:14px 16px;border-bottom:1px solid #f6f6f8;vertical-align:middle}
    .row-center{display:flex;align-items:center;gap:12px}

    .avatar{width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:700;color:#fff;overflow:hidden}
    .avatar img{width:100%;height:100%;object-fit:cover}
    .avatar.p1{background:#f06292}
    .avatar.p2{background:#7c4dff}
    .avatar.p3{background:#4db6ac}
    .avatar.p4{background:#ffb74d}

    .action-btn{border-radius:6px;padding:6px 10px;border:1px solid #eef0f2;background:#fff;cursor:pointer;margin:2px}
    .action-delete{background:var(--danger);color:#fff;border:none}

    .modal-overlay{ position: fixed; inset: 0; display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.45); z-index: 9999; }
    .modal { background: #fff; border-radius: 10px; padding: 22px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); width: 520px; max-width:calc(100% - 40px); position:relative; max-height:90vh; overflow-y:auto }
    .modal h2 { margin:0 0 20px 0; font-size:18px; font-weight:800; color:var(--red-2); text-align:center }
    .modal label { font-weight:700; margin-top:12px; display:block }
    .modal input, .modal select { width:100%; padding:12px; border-radius:8px; border:1px solid #ddd; margin-top:4px }
    .modal .modal-actions { margin-top:24px; text-align:right }
    .btn-cancel { background:#fff; border:1px solid #ddd; padding:8px 14px; border-radius:6px; cursor:pointer; margin-right:8px }
    .btn-save { background:var(--green); color:#fff; border:none; padding:8px 14px; border-radius:6px; cursor:pointer; }

    .preview-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      margin: 15px auto;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f0f0f0;
      overflow: hidden;
      border: 3px solid #e9edf0;
    }

    .preview-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .preview-avatar-initial {
      font-size: 48px;
      color: #002e8bff;
      font-weight: 600;
    }

    .btn-choose-photo {
      display: inline-block;
      padding: 8px 16px;
      background: #6c757d;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      border: none;
    }

    .btn-choose-photo:hover {
      background: #5a6268;
    }

    @media (max-width:720px){ 
      .filter-card{flex-direction:column;align-items:stretch} 
      .search-input{width:100%} 
      table thead{display:none} 
      tbody td{display:block;padding:12px} 
    }
  </style>
</head>
<body class="dashboard-body">

  <div class="app" style="margin-top: 4%;">
    <?php include 'adm_header.php'?>
  <div class="container">
    <div class="main-wrapper">
      <div class="main">
        <div class="content">
          <div class="page-head">
            <div class="page-title" style="margin-left: 5.5%; color: white; font-weight: 300; font-size: 28px; font-weight: 800; margin-bottom: 10px; margin-top: 4%;  font-family: 'Inter', sans-serif; color: #1f1f1fff;">User Management</div>
            <div class="head-actions" style="margin-right: 5%;">
              <button id="openAddBtn" class="btn primary">+ Add New User</button>
            </div>
          </div>


          <div class="filter-card">
            <div class="filter-col" style="flex:1">
              <label style="font-weight:700;color:#222">Filter Users</label>
              <div class="filter-row">
                <div class="search-input" style="width: 70%;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" color="gray" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg> 
                  <input placeholder="Search by name or email..." id="filterSearch"/>
                </div>
                <div style="margin-left:12px; margin-bottom: 2%;">
                  <label style="font-weight:700;color:#222;display:block;margin-bottom:2%">Barangay</label>
                  <select class="input" id="filterBarangay" style="min-width:260px">
                    <option value="">All Barangays</option>
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
              </div>
            </div>
          </div>

          <div class="table-card">
            <div class="table-head">
              <h4>All Users <span id="totalCount" style="font-weight:600;color:var(--muted);font-size:13px">(0)</span></h4>
              <div class="table-sub">A comprehensive list of all registered users in the municipal system.</div>
            </div>
            <table>
              <thead>
                <tr>
                  <th style="width:36px"><input id="selectAll" type="checkbox" /></th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Barangay</th>
                  <th>Date Created</th>
                  <th style="width:170px">Actions</th>
                </tr>
              </thead>
              <tbody id="usersTbody">
                <tr>
                  <td colspan="9" style="text-align:center;padding:40px;color:#888">Loading users...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="addUserModal" class="modal-overlay">
    <div class="modal">
      <button id="closeModalX" style="position:absolute;right:20px;top:20px;background:none;border:none;font-size:28px;cursor:pointer">&times;</button>
      <h2 id="modalTitle">Add New User</h2>

      <form id="userForm" enctype="multipart/form-data">
        <input type="hidden" id="userId" name="id">
        
        <div style="text-align: center; margin-bottom: 20px;">
          <label style="display:block; margin-bottom:10px;">Profile Picture</label>
          <div class="preview-avatar" id="previewAvatar">
            <img id="previewImage" style="display:none;" />
            <span class="preview-avatar-initial" id="previewInitial">U</span>
          </div>
          <input type="file" id="profilePictureInput" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/gif" style="display:none;">
          <button type="button" class="btn-choose-photo" id="choosePhotoBtn">Choose Photo</button>
          <p style="color:#666; font-size:12px; margin-top:8px;">Max 5MB. JPG, PNG, GIF only.</p>
        </div>

        <label>Barangay Name *</label>
        <input id="userName" name="full_name" type="text" placeholder="e.g. Barangay San Jose" required>

        <label>Email Address *</label>
        <input id="userEmail" name="email" type="email" placeholder="user@example.com" required>

        <label>Password *</label>
        <div style="position: relative;">
          <input id="userPassword" name="password" type="password" placeholder="Enter password (min. 8 characters)" required minlength="8">
          <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 18px;">
            👁️
          </button>
        </div>
        <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">
          Password must be at least 8 characters long
        </small>

        <label>Contact Number</label>
        <input id="userContact" name="contact_number" type="text" placeholder="09123456789">

        <label>Barangay</label>
        <select id="userBarangay" name="barangay">
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

        
        <div class="modal-actions">
          <button type="button" id="modalCancelBtn" class="btn-cancel">Cancel</button>
          <button type="submit" id="saveUserBtn" class="btn-save">Create User</button>
        </div>
      </form>
    </div>
  </div>
  </div>
  
      <br><br>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tbody = document.getElementById('usersTbody');
        const modalOverlay = document.getElementById('addUserModal');
        const userForm = document.getElementById('userForm');
        const modalTitle = document.getElementById('modalTitle');
        const saveBtn = document.getElementById('saveUserBtn');
        const profilePictureInput = document.getElementById('profilePictureInput');
        const choosePhotoBtn = document.getElementById('choosePhotoBtn');
        const previewImage = document.getElementById('previewImage');
        const previewInitial = document.getElementById('previewInitial');
        
        let currentEditId = null;

        choosePhotoBtn.addEventListener('click', () => profilePictureInput.click());
        
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Only JPG, PNG, and GIF allowed.');
                    this.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewImage.style.display = 'block';
                    previewInitial.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        function openModal() {
            modalOverlay.style.display = 'flex';
        }

        function closeModal() {
            modalOverlay.style.display = 'none';
            userForm.reset();
            currentEditId = null;
            document.getElementById('userId').value = '';
            modalTitle.textContent = 'Add New User';
            saveBtn.textContent = 'Create User';
            previewImage.style.display = 'none';
            previewInitial.style.display = 'block';
            previewInitial.textContent = 'U';
        }

        document.getElementById('openAddBtn').addEventListener('click', () => {
            closeModal();
            openModal();
        });

        document.getElementById('closeModalX').addEventListener('click', closeModal);
        document.getElementById('modalCancelBtn').addEventListener('click', closeModal);

        async function loadUsers() {
            const params = new URLSearchParams({
                search: document.getElementById('filterSearch').value.trim(),
                barangay: document.getElementById('filterBarangay').value
            });

            try {
                const res = await fetch(`api_users.php?${params}`);
                const json = await res.json();

                tbody.innerHTML = '';

                if (!json.success || !json.data || json.data.length === 0) {
                    document.getElementById('totalCount').textContent = '(0)';
                    tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:20px;color:#888">No users found</td></tr>`;
                    return;
                }

                document.getElementById('totalCount').textContent = `(${json.data.length})`;

                json.data.forEach(user => {
                    const initials = (user.name || 'U').charAt(0).toUpperCase();
                    const avatarClass = ['p1', 'p2', 'p3', 'p4'][Math.floor(Math.random() * 4)];
                    
                    const tr = document.createElement('tr');
                    
                    let avatarHTML = '';
                    if (user.profile_picture) {
                        avatarHTML = `<div class="avatar"><img src="${user.profile_picture}" alt="${user.name}"></div>`;
                    } else {
                        avatarHTML = `<div class="avatar ${avatarClass}">${initials}</div>`;
                    }
                    
                    tr.innerHTML = `
                        <td><input type="checkbox" class="row-checkbox" value="${user.id}"></td>
                        <td>
                            <div class="row-center">
                                ${avatarHTML}
                                ${user.name}
                            </div>
                        </td>
                        <td>${user.email}</td>
                        <td>${user.contact_number || '-'}</td>
                        <td>${user.barangay || '-'}</td>
                        <td>${user.created_at || '-'}</td>
                        <td>
                            <button class="action-btn" onclick="editUser('${user.id}')">Edit</button>
                            <button class="action-btn action-delete" onclick="deleteUser('${user.id}')">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;color:red">Error loading data</td></tr>`;
            }
        }

        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(userForm);
            
            if (currentEditId) {
                formData.set('id', currentEditId);
            }
            
            try {
                saveBtn.textContent = 'Saving...';
                saveBtn.disabled = true;

                const res = await fetch('api_users.php', {
                    method: 'POST',
                    body: formData
                });
                
                const json = await res.json();
                
                if (json.success) {
                    alert(json.message);
                    closeModal();
                    loadUsers();
                } else {
                    alert("Error: " + json.message);
                }
            } catch (err) {
                alert("System Error: " + err.message);
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = currentEditId ? 'Update User' : 'Create User';
            }
        });

        window.editUser = async (id) => {
            try {
                const res = await fetch(`api_users.php?id=${id}`);
                const json = await res.json();
                
                if (!json.success || !json.data || json.data.length === 0) {
                    alert('User not found.');
                    return;
                }

                const user = json.data[0];

                document.getElementById('userName').value = user.name || '';
                document.getElementById('userEmail').value = user.email || '';
                document.getElementById('userContact').value = user.contact_number || '';
                document.getElementById('userBarangay').value = user.barangay || '';

                
                if (user.profile_picture) {
                    previewImage.src = user.profile_picture;
                    previewImage.style.display = 'block';
                    previewInitial.style.display = 'none';
                } else {
                    const initials = (user.name || 'U').charAt(0).toUpperCase();
                    previewInitial.textContent = initials;
                    previewImage.style.display = 'none';
                    previewInitial.style.display = 'block';
                }

                currentEditId = id;
                document.getElementById('userId').value = id;
                modalTitle.textContent = 'Edit User';
                saveBtn.textContent = 'Update User';
                openModal();

            } catch (err) {
                console.error(err);
                alert('Failed to load user.');
            }
        };

        window.deleteUser = async (id) => {
            if(!confirm('Delete this user? This action cannot be undone.')) return;

            try {
                const res = await fetch('api_users.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const json = await res.json();
                if(json.success) {
                    alert('User deleted.');
                    loadUsers();
                } else {
                    alert('Failed: ' + json.message);
                }
            } catch (err) {
                alert('Error deleting user.');
            }
        };


        let searchTimeout;
        document.getElementById('filterSearch').addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadUsers, 500);
        });
        document.getElementById('filterBarangay').addEventListener('change', loadUsers);

        loadUsers();
    });
  </script>
     
</body>
</html>
<?php include 'footer.html' ?>