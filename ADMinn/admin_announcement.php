<?php
require_once 'config.php';
redirectIfNotLogged(); 

$db = (new Database())->getConnection();

$stmt = $db->prepare("SELECT * FROM announcements ORDER BY published_at DESC, created_at DESC");
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Announcement Management – Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 0; 
            background: var(--bg);
            color: #1f2937;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 3%;
            padding: 40px;
            border-radius: 15px;
            background-color: #f4f4f4ff;
        }
        
        .under {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
            color: #111827;
        }
        
        .filter-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-actions input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: var(--radius);
            flex-grow: 1;
            max-width: 300px;
        }
        
        .filter-actions select {
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: var(--radius);
        }
        
        .btn-primaryy {
            margin-top: ;
            background: linear-gradient(135deg, #ff5b5bff 0%, #c0352eff 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }

        .announcements-list {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }

        .announcement-card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .announcement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16,24,40,0.12);
        }
        
        .card-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #989898ff 0%, #fefeffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .announcement-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .announcement-card p {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.4;
            flex-grow: 1;
            margin: 0 0 15px 0;
        }

        .meta {
            padding-top: 10px;
            border-top: 1px solid #f3f4f6;
            font-size: 0.8rem;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        .pill-type { background-color: #e5e7eb; color: #4b5563; }
        .pill-status-published { background-color: #048239ff ; color: white; }
        .pill-status-draft { background-color: #93979eff; color: #fafafaff; }
        .pill-status-archived { background-color: #094395ff; color: white; }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .card-actions button {
            background: none;
            border: 1px solid #d1d5db;
            color: var(--muted);
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
            flex: 1;
        }
        .card-actions button:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .card-actions .delete-btn:hover {
            border-color: var(--danger);
            color: var(--danger);
        }

        .modal {
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%;
            background: rgba(0, 0, 0, 0.7); 
            display: none; 
            z-index: 1000;
            overflow-y: auto;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            margin: 20px;
            padding: 30px;
            border-radius: var(--radius);
            width: 90%;
            max-width: 600px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            margin: 0;
        }
        
        .modal-close {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--muted);
            background: none;
            border: none;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .modal-close:hover {
            background: #f3f4f6;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .image-upload-container {
            margin-top: 10px;
        }
        
        .image-preview {
            width: 100%;
            height: 240px;
            background-color: #fafafa;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .image-preview:hover {
            border-color: var(--primary);
            background-color: #f0f9ff;
        }
        
        .image-preview.has-image {
            border-style: solid;
            border-color: #e5e7eb;
            background-color: #ffffff;
        }
        
        .image-preview.has-image:hover {
            border-color: var(--primary);
        }
        
        .placeholder-content {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }
        
        .placeholder-icon {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }
        
        .placeholder-text {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
        }
        
        .placeholder-hint {
            font-size: 12px;
            color: #9ca3af;
        }
        
        .image-preview.has-image .placeholder-content {
            display: none;
        }
        
        .preview-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: none;
        }
        
        .image-preview.has-image .preview-img {
            display: block;
        }
        
        .image-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-change-image,
        .btn-remove-image {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-change-image {
            background-color: #1f2937;
            color: white;
        }
        
        .btn-change-image:hover {
            background-color: #2980b9;
        }
        
        .btn-remove-image {
            background-color: #fee;
            color: var(--danger);
        }
        
        .btn-remove-image:hover {
            background-color: var(--danger);
            color: white;
        }
        
        .image-actions.hidden {
            display: none;
        }
        
        .modal-actions {
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-cancel {
            background-color: black;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
        }
        
        .btn-cancel:hover {
            background-color: #4b5563;
        }
    </style>
</head>
<body class="dashboard-body">

    <?php include 'adm_header.php'; ?>
        <br><br>
    <div class="container">
        <div class="under">
            <h1 > Announcement Management</h1>
            <button class="btn-primaryy" id="openModalBtn">+ Create New Announcement</button>
        </div>

        <div class="filter-actions">
            <input type="text" id="searchAnnouncements" placeholder="Search title or content...">
            <select id="filterType">
                <option value="">All Types</option>
                <option value="announcement">Announcement</option>
                <option value="alert">Alert</option>
                <option value="emergency">Emergency</option>
                <option value="maintenance">Maintenance</option>
            </select>
            <select id="filterStatus">
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        
        <div class="announcements-list" id="announcementsList"></div>
    </div>

    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Create New Announcement</h2>
                <button class="modal-close" id="closeModalBtn">&times;</button>
            </div>
            
            <form id="announcementForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="announcementId">
                <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" name="title" id="title" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea name="content" id="content" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="type">Type *</label>
                    <select name="type" id="type" required>
                        <option value="announcement">Announcement</option>
                        <option value="alert">Alert</option>
                        <option value="emergency">Emergency</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority">Priority *</label>
                    <select name="priority" id="priority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select name="status" id="status" required>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="expires_at">Expires At (Optional)</label>
                    <input type="datetime-local" name="expires_at" id="expires_at">
                </div>
                
                <div class="form-group">
                    <label for="cover_image">Cover Image</label>
                    <div class="image-upload-container">
                        <div id="imagePreview" class="image-preview">
                            <div class="placeholder-content">
                                <div class="placeholder-icon">🖼️</div>
                                <div class="placeholder-text">Click to upload cover image</div>
                                <div class="placeholder-hint">JPG, PNG, GIF, WEBP (Max 5MB)</div>
                            </div>
                            <img id="previewImg" class="preview-img" src="" alt="Preview">
                        </div>
                        
                        <input type="file" id="coverImage" name="cover_image" accept="image/*" style="display: none;">
                        
                        <div id="imageActions" class="image-actions hidden">
                            <button type="button" id="btnChangeImage" class="btn-change-image">Change Image</button>
                            <button type="button" id="btnRemoveImage" class="btn-remove-image">Remove Image</button>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary" id="btnSave">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_ENDPOINT = 'api_announcements.php';
        const listContainer = document.getElementById('announcementsList');
        const modal = document.getElementById('announcementModal');
        const form = document.getElementById('announcementForm');
        const modalTitle = document.getElementById('modalTitle');
        
        const annIdInput = document.getElementById('announcementId');
        const titleInput = document.getElementById('title');
        const contentInput = document.getElementById('content');
        const typeInput = document.getElementById('type');
        const priorityInput = document.getElementById('priority');
        const statusInput = document.getElementById('status');
        const expiresInput = document.getElementById('expires_at');
        const coverImageInput = document.getElementById('coverImage');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const btnChangeImage = document.getElementById('btnChangeImage');
        const btnRemoveImage = document.getElementById('btnRemoveImage');
        const imageActions = document.getElementById('imageActions');
        const removeImageFlag = document.getElementById('removeImageFlag');

        const searchInput = document.getElementById('searchAnnouncements');
        const filterType = document.getElementById('filterType');
        const filterStatus = document.getElementById('filterStatus');

        let currentAnnouncements = [];
        let currentImageFile = null;
        let existingImagePath = null;

        imagePreview.addEventListener('click', () => {
            coverImageInput.click();
        });

        btnChangeImage.addEventListener('click', () => {
            coverImageInput.click();
        });

        coverImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                currentImageFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    imagePreview.classList.add('has-image');
                    imageActions.classList.remove('hidden');
                    removeImageFlag.value = '0';
                };
                reader.readAsDataURL(file);
            }
        });

        btnRemoveImage.addEventListener('click', () => {
            coverImageInput.value = '';
            currentImageFile = null;
            previewImg.src = '';
            imagePreview.classList.remove('has-image');
            imageActions.classList.add('hidden');
            removeImageFlag.value = '1';
        });

        function openModal() {
            modal.classList.add('show');
        }

        function closeModal() {
            modal.classList.remove('show');
            form.reset();
            annIdInput.value = '';
            currentImageFile = null;
            existingImagePath = null;
            previewImg.src = '';
            imagePreview.classList.remove('has-image');
            imageActions.classList.add('hidden');
            removeImageFlag.value = '0';
            modalTitle.textContent = 'Create New Announcement';
            document.getElementById('btnSave').textContent = 'Save';
        }

        function renderList() {
            listContainer.innerHTML = '';
            const searchTerm = searchInput.value.toLowerCase();
            const selectedType = filterType.value;
            const selectedStatus = filterStatus.value;
            
            const filteredList = currentAnnouncements.filter(ann => {
                const matchesSearch = ann.title.toLowerCase().includes(searchTerm) || 
                                    ann.content.toLowerCase().includes(searchTerm);
                const matchesType = selectedType === "" || ann.type === selectedType;
                const matchesStatus = selectedStatus === "" || ann.status === selectedStatus;
                return matchesSearch && matchesType && matchesStatus;
            });

            if (filteredList.length === 0) {
                listContainer.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; color: var(--muted);">No announcements match the current filters.</p>';
                return;
            }

            filteredList.forEach(ann => {
                const card = document.createElement('div');
                card.className = `announcement-card ${ann.type}`;
                
                const excerptContent = ann.content.length > 150 
                    ? ann.content.substring(0, 150) + '...'
                    : ann.content;
                
                const imagePath = ann.cover_image ? ann.cover_image : '';
                
                card.innerHTML = `
                    <div class="card-image">
                        ${imagePath ? `<img src="${imagePath}" alt="${ann.title}">` : ''}
                    </div>
                    <div class="card-body">
                        <h3>${ann.title}</h3>
                        <p>${excerptContent}</p>
                        <div class="meta">
                            <div>
                                <span class="pill pill-type">${ann.type}</span>
                                <span class="pill pill-status-${ann.status}">${ann.status}</span>
                            </div>
                            <small>${new Date(ann.published_at).toLocaleDateString()}</small>
                        </div>
                        <div class="card-actions">
                            <button class="edit-btn" data-id="${ann.id}">Edit</button>
                            <button class="delete-btn" data-id="${ann.id}">Delete</button>
                        </div>
                    </div>
                `;
                listContainer.appendChild(card);
            });
        }

        async function fetchAnnouncements() {
            try {
                const response = await fetch(API_ENDPOINT);
                if (!response.ok) throw new Error('Failed to fetch announcements.');
                const data = await response.json();
                currentAnnouncements = data.data || [];
                renderList();
            } catch (error) {
                console.error('Error fetching announcements:', error);
                listContainer.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; color: var(--danger);">Failed to load announcements.</p>';
            }
        }

        window.editAnnouncement = (id) => {
            const ann = currentAnnouncements.find(a => a.id == id);
            if (!ann) {
                alert('Announcement not found.');
                return;
            }

            modalTitle.textContent = 'Edit Announcement';
            document.getElementById('btnSave').textContent = 'Update';
            annIdInput.value = ann.id;
            titleInput.value = ann.title;
            contentInput.value = ann.content;
            typeInput.value = ann.type;
            priorityInput.value = ann.priority;
            statusInput.value = ann.status;
            
            if (ann.expires_at) {
                const date = new Date(ann.expires_at);
                const localISOTime = new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
                expiresInput.value = localISOTime;
            } else {
                expiresInput.value = '';
            }

            if (ann.cover_image) {
                existingImagePath = ann.cover_image;
                previewImg.src = ann.cover_image;
                imagePreview.classList.add('has-image');
                imageActions.classList.remove('hidden');
                removeImageFlag.value = '0';
            }

            openModal();
        };

        window.deleteAnnouncement = async (id) => {
            if (!confirm('Are you sure you want to delete this announcement?')) return;
            
            try {
                const response = await fetch(`${API_ENDPOINT}?id=${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();

                if (data.success) {
                    alert('Announcement deleted successfully!');
                    fetchAnnouncements();
                } else {
                    alert('Error deleting announcement: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('An error occurred during delete operation.');
            }
        };

        document.getElementById('openModalBtn').addEventListener('click', () => {
            closeModal();
            openModal();
        });

        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('cancelBtn').addEventListener('click', closeModal);

        listContainer.addEventListener('click', (e) => {
            const id = e.target.getAttribute('data-id');
            if (!id) return;

            if (e.target.classList.contains('edit-btn')) {
                window.editAnnouncement(id);
            } else if (e.target.classList.contains('delete-btn')) {
                window.deleteAnnouncement(id);
            }
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(window.searchTimer);
            window.searchTimer = setTimeout(renderList, 300);
        });
        filterType.addEventListener('change', renderList);
        filterStatus.addEventListener('change', renderList);

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('btnSave');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            const formData = new FormData(form);
            
            if (removeImageFlag.value === '1' && !currentImageFile) {
                formData.delete('cover_image');
            }
            
            try {
                const response = await fetch(API_ENDPOINT, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Network response was not ok.');
                const data = await response.json();

                if (data.success) {
                    alert('Announcement saved successfully!');
                    closeModal();
                    fetchAnnouncements();
                } else {
                    alert('Error saving announcement: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('An error occurred during save operation.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = annIdInput.value ? 'Update' : 'Save';
            }
        });

        document.addEventListener('DOMContentLoaded', fetchAnnouncements);
    </script>
<br><br>
  <?php include 'footer.html'; ?>
</body>
</html>