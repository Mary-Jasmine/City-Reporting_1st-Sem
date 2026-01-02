<?php
require_once 'config.php';
redirectIfNotLogged();

$SCRIPT_CONTEXT = 'admin';
$db = (new Database())->getConnection();

function isImage($source) {
    $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

function isVideo($source) {
    $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    return in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_guide') {
            $data = [
                'type' => 'guide',
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? 'General'),
                'content' => trim($_POST['content'] ?? ''),
                'source' => null
            ];
            
            if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $data['source'] = uploadResourceFile($_FILES['file'], $SCRIPT_CONTEXT);
                $data['content'] = null;
            }
            
            createResource($db, $data);
            $_SESSION['success'] = 'Guide added successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        
        if ($_POST['action'] === 'add_video') {
            $data = [
                'type' => 'video',
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? 'General'),
                'content' => null,
                'source' => null
            ];
            
            if (!empty($_FILES['videoFile']) && $_FILES['videoFile']['error'] === UPLOAD_ERR_OK) {
                $data['source'] = uploadResourceFile($_FILES['videoFile'], $SCRIPT_CONTEXT);
            } 
            elseif (!empty($_POST['source'])) {
                $data['source'] = trim($_POST['source']);
            }
            
            createResource($db, $data);
            $_SESSION['success'] = 'Video added successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        
        if ($_POST['action'] === 'edit_resource') {
            $id = intval($_POST['id']);
            $existing = getResourceById($db, $id);
                        
            $data = [
                'content' => trim($_POST['content'] ?? '') ?: $existing['content'],
                'source' => $existing['source'] 
            ];
            
            if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                if (!empty($existing['source']) && !filter_var($existing['source'], FILTER_VALIDATE_URL)) {
                    deleteOldResourceFile($existing['source'], $SCRIPT_CONTEXT);
                }
                $data['source'] = uploadResourceFile($_FILES['file'], $SCRIPT_CONTEXT);
                $data['content'] = null; 
            }
            
            if ($existing['type'] === 'video' && isset($_POST['source']) && (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK)) {
                $data['source'] = trim($_POST['source']);
            }
            
            updateResource($db, $id, $data);
            $_SESSION['success'] = 'Resource updated successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$guides = getResourcesByType($db, 'guide');
$videos = getResourcesByType($db, 'video');

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Resources — Municipality Admin</title>
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

    * { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; font-family: Inter, system-ui; color: #111; }
    
    .container { max-width: 1200px; margin: 0 auto; margin-bottom: 3%; padding: 28px 36px; background-color: whitesmoke; border-radius: 3%; margin-top: 3%;}
    
    .hero {
      background: #fff;
      padding: 18px 22px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: var(--shadow);
      margin-bottom: 18px;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.2s;
    }
    .btn.red { background: #9f0000ff; color: #fff; }
    .btn.red:hover { background: #014316ff }
    .btn.white { background: #fff; border: 1px solid #e6e6e6; color: #333; }
    .btn.white:hover { background: #f9f9f9; }
    .btn.blue { background: #3b82f6; color: #fff; }
    .btn.blue:hover { background: #2563eb; }
    
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 18px;
      margin-bottom: 32px;
    }
    
    .card {
      background: #032c85ff;
      border-radius: 8px;
      box-shadow: var(--shadow);
      padding: 0;
      position: relative;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
      color: white;
    }
    
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(16,24,40,0.12);
    }
    
    .card-media {
      width: 100%;
      height: 200px;
      background: #f5f5f5;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    
    .card-media img, .card-media video {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .card-media.placeholder {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      font-size: 48px;
    }
    
    .card-media.pdf-preview {
      background: linear-gradient(135deg, #474140ff 0%, #c7c3c3ff 100%);
      color: white;
      font-size: 64px;
    }
    
    .card-media.doc-preview {
      background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
      color: white;
      font-size: 64px;
    }
    
    .card-body { padding: 16px; }
    
    .tag {
      display: inline-block;
      background: #e9f8ef;
      color: #0d6b2b;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 8px;
    }
    
    .card h3 { margin: 0 0 8px 0; font-size: 18px; }
    .card p { margin: 0 0 12px 0; color: var(--muted); line-height: 1.4; font-size: 14px; }
    
    .card-actions {
      display: flex;
      gap: 8px;
      margin-top: 12px;
      flex-wrap: wrap;
      background-color: ;
    }
    
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      align-items: center;
      justify-content: center;
      z-index: 1000;
      overflow-y: auto;
    }
    .modal.show { display: flex; }
    
    .modal-content {
      background: white;
      padding: 24px;
      border-radius: 12px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      margin: 20px;
    }
    
    .viewer-modal .modal-content {
      max-width: 95vw;
      max-height: 95vh;
      padding: 0;
      display: flex;
      flex-direction: column;
    }
    
    .viewer-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 24px;
      border-bottom: 1px solid #e5e7eb;
      background: white;
    }
    
    .viewer-title {
      font-size: 18px;
      font-weight: 700;
      margin: 0;
    }
    
    .viewer-close {
      background: #f3f4f6;
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
    }
    
    .viewer-close:hover { background: #e5e7eb; }
    
    .viewer-body {
      flex: 1;
      overflow: auto;
      background: #1f2937;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .viewer-body img {
      max-width: 100%;
      max-height: calc(95vh - 120px);
      object-fit: contain;
    }
    
    .viewer-body video {
      max-width: 100%;
      max-height: calc(95vh - 120px);
      width: 100%;
    }
    
    .viewer-body iframe {
      width: 100%;
      height: calc(95vh - 120px);
      border: none;
      background: white;
    }
    
    .viewer-footer {
      padding: 16px 24px;
      border-top: 1px solid #e5e7eb;
      background: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .viewer-info {
      color: var(--muted);
      font-size: 14px;
    }
    
    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      font-size: 14px;
    }
    .form-group input, .form-group select, .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-family: inherit;
      font-size: 14px;
    }
    .form-group textarea { min-height: 120px; resize: vertical; }
    
    .form-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 20px;
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 14px;
    }
    .alert.error { background: #fee; color: #c33; }
    .alert.success { background: #d4edda; color: #155724; }
    
    .delete-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }
    
    .edit-btn {
      background: #123600ff;
      color: #ffffffff;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }
    
    .file-info {
      font-size: 12px;
      color: var(--muted);
      margin-top: 4px;
    }
    
    .current-file {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      background: #f8f9fa;
      border-radius: 6px;
      font-size: 14px;
      margin-top: 8px;
    }
    
    .play-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 60px;
      height: 60px;
      background: rgba(0,0,0,0.7);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 24px;
      pointer-events: none;
    }
  </style>
</head>
<body class="dashboard-body">

    <?php include 'adm_header.php'; ?>
<br><br>
  <div class="container">
    <?php if (isset($error)): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <div class="hero">
      <div>
        <div style="font-weight:800">Manage Resources</div>
        <div style="color: var(--muted); margin-top:6px">Add guides, videos, and images for disaster preparedness</div>
      </div>
      <div style="display:flex;gap:12px">
        <button class="btn red" onclick="openModal('guideModal')">Add Guide</button>
        <button class="btn white" onclick="openModal('videoModal')">Add Video</button>
      </div>
    </div>
    
    <h2>Guides (<?= count($guides) ?>)</h2>
    <div class="grid">
      <?php if (empty($guides)): ?>
        <p style="color: var(--muted)">No guides added yet.</p>
      <?php endif; ?>
      
      <?php foreach ($guides as $guide): ?>
        <?php
        $guidePath = $guide;
        if (!empty($guidePath['source']) && !str_starts_with($guidePath['source'], 'http')) {
          if (!str_starts_with($guidePath['source'], '../')) {
            $guidePath['source'] = '../' . $guidePath['source'];
          }
        }
        ?>
        <div class="card" onclick="viewResource(<?= htmlspecialchars(json_encode($guidePath)) ?>)">
          <div class="card-media <?php 
            if (empty($guide['source'])) {
              echo 'placeholder';
            } elseif (isImage($guide['source'])) {
              echo '';
            } elseif (pathinfo($guide['source'], PATHINFO_EXTENSION) === 'pdf') {
              echo 'pdf-preview';
            } else {
              echo 'doc-preview';
            }
          ?>">
            <?php if (!empty($guide['source'])): ?>
              <?php 
              $displaySource = $guide['source'];
              if (!str_starts_with($displaySource, 'http') && !str_starts_with($displaySource, '../')) {
                $displaySource = '../' . $displaySource;
              }
              ?>
              <?php if (isImage($guide['source'])): ?>
                <img src="<?= htmlspecialchars($displaySource) ?>" alt="<?= htmlspecialchars($guide['title']) ?>" onerror="this.parentElement.innerHTML='📕'">
              <?php elseif (pathinfo($guide['source'], PATHINFO_EXTENSION) === 'pdf'): ?>
                
              <?php else: ?>
                
              <?php endif; ?>
            <?php else: ?>
              
            <?php endif; ?>
          </div>
          
          <div class="card-body">
            <div class="tag"><?= htmlspecialchars($guide['category']) ?></div>
            <h3><?= htmlspecialchars($guide['title']) ?></h3>
            <p><?= htmlspecialchars(substr($guide['description'], 0, 100)) ?><?= strlen($guide['description']) > 100 ? '...' : '' ?></p>
            
            <div class="card-actions" onclick="event.stopPropagation()">
              <button class="edit-btn" onclick='editResource(<?= json_encode($guide) ?>)'>Edit</button>
              <button class="delete-btn" onclick="deleteResource(<?= $guide['id'] ?>)">Delete</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <h2>Videos (<?= count($videos) ?>)</h2>
    <div class="grid">
      <?php if (empty($videos)): ?>
        <p style="color: var(--muted)">No videos added yet.</p>
      <?php endif; ?>
      
      <?php foreach ($videos as $video): ?>
        <?php
        $videoPath = $video;
        if (!empty($videoPath['source']) && !str_starts_with($videoPath['source'], 'http')) {
          if (!str_starts_with($videoPath['source'], '../')) {
            $videoPath['source'] = '../' . $videoPath['source'];
          }
        }
        ?>
        <div class="card" onclick="viewResource(<?= htmlspecialchars(json_encode($videoPath)) ?>)">
          <div class="card-media <?= empty($video['source']) ? 'placeholder' : '' ?>">
            <?php if (!empty($video['source'])): ?>
              <?php 
              $displaySource = $video['source'];
              if (!str_starts_with($displaySource, 'http') && !str_starts_with($displaySource, '../')) {
                $displaySource = '../' . $displaySource;
              }
              ?>
              <?php if (isVideo($video['source'])): ?>
                <video>
                  <source src="<?= htmlspecialchars($displaySource) ?>" type="video/<?= pathinfo($video['source'], PATHINFO_EXTENSION) ?>">
                </video>
                <div class="play-overlay">▶</div>
              <?php else: ?>
                <div style="font-size: 32px;">🎥</div>
              <?php endif; ?>
            <?php else: ?>
              🎬
            <?php endif; ?>
          </div>
          
          <div class="card-body">
            <div class="tag"><?= htmlspecialchars($video['category']) ?></div>
            <h3><?= htmlspecialchars($video['title']) ?></h3>
            <p><?= htmlspecialchars(substr($video['description'], 0, 100)) ?><?= strlen($video['description']) > 100 ? '...' : '' ?></p>
            
            <div class="card-actions" onclick="event.stopPropagation()">
              <button class="edit-btn" onclick='editResource(<?= json_encode($video) ?>)'>Edit</button>
              <button class="delete-btn" onclick="deleteResource(<?= $video['id'] ?>)">Delete</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  
  <div id="viewerModal" class="modal viewer-modal">
    <div class="modal-content">
      <div class="viewer-header">
        <h2 class="viewer-title" id="viewerTitle">Resource Viewer</h2>
        <button class="viewer-close" onclick="closeModal('viewerModal')" title="Close (Esc)">✕</button>
      </div>
      <div class="viewer-body" id="viewerBody"></div>
      <div class="viewer-footer">
        <div class="viewer-info" id="viewerInfo"></div>
        <div>
          <button class="btn white" onclick="closeModal('viewerModal')">Close</button>
          <a id="viewerDownload" href="#" target="_blank" class="btn blue" style="margin-left: 8px;">Download</a>
        </div>
      </div>
    </div>
  </div>
  
  <div id="guideModal" class="modal">
    <div class="modal-content">
      <h2>Add New Guide</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_guide">
        <div class="form-group">
          <label>Title *</label>
          <input type="text" name="title" required>
        </div>
        <div class="form-group">
          <label>Category</label>
          <select name="category">
            <option>General</option>
            <option>Basic First Aid</option>
            <option>Disaster Prep</option>
            <option>Medical Conditions</option>
            <option>Emergency Response</option>
          </select>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" placeholder="Brief description of the guide"></textarea>
        </div>
        <div class="form-group">
          <label>Content (Text Guide)</label>
          <textarea name="content" placeholder="Enter guide content here..."></textarea>
        </div>
        <div class="form-group">
          <label>Upload File (PDF, DOCX, or Image)</label>
          <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
          <div class="file-info">Supported: PDF, DOCX, JPG, PNG, GIF. Max 20MB</div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn white" onclick="closeModal('guideModal')">Cancel</button>
          <button type="submit" class="btn red">Save Guide</button>
        </div>
      </form>
    </div>
  </div>
  
  <div id="videoModal" class="modal">
    <div class="modal-content">
      <h2>Add New Video</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_video">
        <div class="form-group">
          <label>Title *</label>
          <input type="text" name="title" required>
        </div>
        <div class="form-group">
          <label>Category</label>
          <select name="category">
            <option>General</option>
            <option>Disaster Prep</option>
            <option>First Aid</option>
            <option>Safety</option>
            <option>Emergency Response</option>
          </select>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" placeholder="Brief description of the video"></textarea>
        </div>
        <div class="form-group">
          <label>Video URL (YouTube, Vimeo, etc.)</label>
          <input type="url" name="source" placeholder="https://youtube.com/watch?v=...">
        </div>
        <div style="text-align: center; margin: 12px 0; color: var(--muted);">— OR —</div>
        <div class="form-group">
          <label>Upload Video File</label>
          <input type="file" name="videoFile" accept="video/mp4,video/webm,video/ogg">
          <div class="file-info">Supported: MP4, WEBM, OGG. Max 20MB</div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn white" onclick="closeModal('videoModal')">Cancel</button>
          <button type="submit" class="btn red">Save Video</button>
        </div>
      </form>
    </div>
  </div>
  
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h2 id="editModalTitle">Edit Resource</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_resource">
        <input type="hidden" name="id" id="edit_id">
        <div class="form-group">
          <label>Title *</label>
          <input type="text" name="title" id="edit_title" required>
        </div>
        <div class="form-group">
          <label>Category</label>
          <select name="category" id="edit_category">
            <option>General</option>
            <option>Basic First Aid</option>
            <option>Disaster Prep</option>
            <option>Medical Conditions</option>
            <option>Emergency Response</option>
            <option>Safety</option>
          </select>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" id="edit_description"></textarea>
        </div>
        <div class="form-group" id="edit_content_group">
          <label>Content</label>
          <textarea name="content" id="edit_content"></textarea>
        </div>
        <div class="form-group" id="edit_source_group">
          <label>Source URL</label>
          <input type="url" name="source" id="edit_source">
        </div>
        <div id="current_file_display"></div>
        <div class="form-group">
          <label>Upload New File (Optional)</label>
          <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,video/mp4,video/webm">
          <div class="file-info">Upload a new file to replace the current one</div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn white" onclick="closeModal('editModal')">Cancel</button>
          <button type="submit" class="btn blue">Update Resource</button>
        </div>
      </form>
    </div>
  </div>
  
  <script>
    function openModal(id) {
      document.getElementById(id).classList.add('show');
    }
    
    function closeModal(id) {
      document.getElementById(id).classList.remove('show');
      if (id === 'viewerModal') {
        document.getElementById('viewerBody').innerHTML = '';
      }
    }
    
    function viewResource(resource) {
      const title = document.getElementById('viewerTitle');
      const body = document.getElementById('viewerBody');
      const info = document.getElementById('viewerInfo');
      const download = document.getElementById('viewerDownload');
      
      title.textContent = resource.title;
      info.innerHTML = `<strong>${resource.category}</strong> • ${resource.description || 'No description'}`;
      body.innerHTML = '';
      
      if (resource.source) {
        const ext = resource.source.split('.').pop().toLowerCase();
        
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
          const img = document.createElement('img');
          img.src = resource.source;
          img.alt = resource.title;
          body.appendChild(img);
          download.href = resource.source;
          download.textContent = 'Download';
          download.style.display = 'inline-flex';
        }
        else if (['mp4', 'webm', 'ogg', 'mov'].includes(ext)) {
          const video = document.createElement('video');
          video.controls = true;
          video.style.maxWidth = '100%';
          video.style.maxHeight = 'calc(95vh - 120px)';
          const source = document.createElement('source');
          source.src = resource.source;
          source.type = `video/${ext}`;
          video.appendChild(source);
          body.appendChild(video);
          download.href = resource.source;
          download.textContent = 'Download';
          download.style.display = 'inline-flex';
        }
        else if (ext === 'pdf') {
          const iframe = document.createElement('iframe');
          iframe.src = resource.source;
          body.appendChild(iframe);
          download.href = resource.source;
          download.textContent = 'Download';
          download.style.display = 'inline-flex';
        }
        else if (resource.source.includes('youtube.com') || resource.source.includes('youtu.be') || resource.source.includes('vimeo.com')) {
          let embedUrl = resource.source;
          
          if (resource.source.includes('youtube.com/watch')) {
            const url = new URL(resource.source);
            const videoId = url.searchParams.get('v');
            embedUrl = `https://www.youtube.com/embed/${videoId}`;
          } else if (resource.source.includes('youtu.be')) {
            const videoId = resource.source.split('youtu.be/')[1].split('?')[0];
            embedUrl = `https://www.youtube.com/embed/${videoId}`;
          } else if (resource.source.includes('vimeo.com')) {
            const videoId = resource.source.split('vimeo.com/')[1].split('?')[0];
            embedUrl = `https://player.vimeo.com/video/${videoId}`;
          }
          
          const iframe = document.createElement('iframe');
          iframe.src = embedUrl;
          iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
          iframe.allowFullscreen = true;
          body.appendChild(iframe);
          download.href = resource.source;
          download.textContent = 'Open Link';
          download.style.display = 'inline-flex';
        }
        else if (['doc', 'docx'].includes(ext)) {
          body.innerHTML = `
            <div style="text-align: center; padding: 40px; color: white;">
              <div style="font-size: 64px; margin-bottom: 20px;">📘</div>
              <h3 style="margin: 0 0 16px 0; color: white;">Document File</h3>
              <p style="margin: 0 0 24px 0; color: #d1d5db;">${resource.title}</p>
              <a href="${resource.source}" target="_blank" class="btn white" style="display: inline-flex;">Download to View</a>
            </div>
          `;
          download.href = resource.source;
          download.style.display = 'inline-flex';
        }
        else {
          body.innerHTML = `
            <div style="text-align: center; padding: 40px; color: white;">
              <div style="font-size: 64px; margin-bottom: 20px;">📄</div>
              <h3 style="margin: 0; color: white;">File Preview Not Available</h3>
              <p style="margin: 0 0 24px 0; color: #d1d5db;">Click download to view this file</p>
            </div>
          `;
          download.href = resource.source;
          download.style.display = 'inline-flex';
        }
      } else if (resource.content) {
        const contentDiv = document.createElement('div');
        contentDiv.style.cssText = 'background: white; padding: 32px; max-width: 800px; margin: 0 auto; line-height: 1.8; color: #111;';
        contentDiv.innerHTML = `<div style="white-space: pre-wrap; font-size: 15px;">${escapeHtml(resource.content)}</div>`;
        body.appendChild(contentDiv);
        download.style.display = 'none';
      } else {
        body.innerHTML = `
          <div style="text-align: center; padding: 40px; color: white;">
            <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
            <h3 style="margin: 0; color: white;">No Content Available</h3>
          </div>
        `;
        download.style.display = 'none';
      }
      
      openModal('viewerModal');
    }
    
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    function editResource(resource) {
      document.getElementById('edit_id').value = resource.id;
      document.getElementById('edit_title').value = resource.title;
      document.getElementById('edit_category').value = resource.category;
      document.getElementById('edit_description').value = resource.description || '';
      document.getElementById('edit_content').value = resource.content || '';
      document.getElementById('edit_source').value = resource.source || '';
      
      if (resource.type === 'guide') {
        document.getElementById('edit_content_group').style.display = 'block';
        document.getElementById('edit_source_group').style.display = 'none';
        document.getElementById('editModalTitle').textContent = 'Edit Guide';
      } else {
        document.getElementById('edit_content_group').style.display = 'none';
        document.getElementById('edit_source_group').style.display = 'block';
        document.getElementById('editModalTitle').textContent = 'Edit Video';
      }
      
      const fileDisplay = document.getElementById('current_file_display');
      if (resource.source) {
        const fileName = resource.source.split('/').pop();
        fileDisplay.innerHTML = `
          <div class="current-file">
            <strong>Current File/Source:</strong> 
            <a href="${resource.source}" target="_blank">${fileName}</a>
          </div>
        `;
      } else {
        fileDisplay.innerHTML = '';
      }
      
      openModal('editModal');
    }
    
    function deleteResource(id) {
      if (!confirm('Are you sure you want to delete this resource? This action cannot be undone.')) return;
      
      fetch('api_resources.php?id=' + id, {
        method: 'DELETE'
      })
      .then(res => {
        if (!res.ok) {
          throw new Error('Network response was not ok');
        }
        return res.json();
      })
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error deleting resource');
      });
    }
    
    window.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.modal').forEach(modal => {
        if (modal) {
          modal.addEventListener('click', (e) => {
            if (e.target === modal) {
              modal.classList.remove('show');
              if (modal.id === 'viewerModal') {
                const viewerBody = document.getElementById('viewerBody');
                if (viewerBody) viewerBody.innerHTML = '';
              }
            }
          });
        }
      });
    });
    
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const viewerModal = document.getElementById('viewerModal');
        if (viewerModal && viewerModal.classList.contains('show')) {
          closeModal('viewerModal');
        }
      }
    });
    
    setTimeout(() => {
      const successAlert = document.querySelector('.alert.success');
      if (successAlert) {
        successAlert.style.transition = 'opacity 0.5s';
        successAlert.style.opacity = '0';
        setTimeout(() => successAlert.remove(), 500);
      }
    }, 5000);
  </script>
</body>
</html>
<?php include 'footer.html'; ?>