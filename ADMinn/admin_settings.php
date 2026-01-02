<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');  // ✅ Redirect to login
    exit();
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'update_language':
                $language = $_POST['language'] ?? 'en';
                $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'default_language'");
                if ($stmt->execute([$language])) {
                    echo json_encode(['success' => true, 'message' => 'Language updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update language']);
                }
                break;
                
            case 'update_font_size':
                $fontSize = intval($_POST['fontSize']);
                if ($fontSize < 12 || $fontSize > 20) {
                    echo json_encode(['success' => false, 'message' => 'Invalid font size']);
                    exit();
                }
                $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'default_font_size'");
                if ($stmt->execute([$fontSize])) {
                    echo json_encode(['success' => true, 'message' => 'Font size updated']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update font size']);
                }
                break;
                
            case 'toggle_dark_mode':
                $darkMode = $_POST['darkMode'] === 'true' ? 1 : 0;
                $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'dark_mode_enabled'");
                if ($stmt->execute([$darkMode])) {
                    echo json_encode(['success' => true, 'message' => 'Dark mode preference saved']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to save dark mode']);
                }
                break;
                
            case 'backup_system':
                $backupPath = 'backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
                if (!is_dir('backups')) {
                    mkdir('backups', 0755, true);
                }
                $command = "mysqldump --user=root --password= --host=127.0.0.1 updatcollab > {$backupPath}";
                exec($command, $output, $return);
                
                if ($return === 0) {
                    $stmt = $db->prepare("INSERT INTO system_logs (user_id, action, details) VALUES (?, 'system_backup', ?)");
                    $stmt->execute([$_SESSION['user_id'], "Backup created: {$backupPath}"]);
                    echo json_encode(['success' => true, 'message' => 'System backup completed successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Backup failed']);
                }
                break;
                
            case 'clear_cache':
                $cacheDir = 'cache/';
                if (is_dir($cacheDir)) {
                    $files = glob($cacheDir . '*');
                    foreach($files as $file) {
                        if(is_file($file)) unlink($file);
                    }
                    
                    $stmt = $db->prepare("INSERT INTO system_logs (user_id, action, details) VALUES (?, 'clear_cache', 'Application cache cleared')");
                    $stmt->execute([$_SESSION['user_id']]);
                    echo json_encode(['success' => true, 'message' => 'Cache cleared successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Cache directory not found']);
                }
                break;
                
            case 'get_logs':
                $stmt = $db->prepare("SELECT sl.*, u.full_name FROM system_logs sl 
                                     LEFT JOIN users u ON sl.user_id = u.user_id 
                                     ORDER BY sl.created_at DESC LIMIT 50");
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'logs' => $logs]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}

$settings = [];
try {
    $stmt = $db->query("SHOW TABLES LIKE 'system_settings'");
    if ($stmt->rowCount() > 0) {
        $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } else {
        $db->exec("CREATE TABLE IF NOT EXISTS system_settings (
            setting_id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        $db->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES 
            ('default_language', 'en'),
            ('default_font_size', '16'),
            ('dark_mode_enabled', '0')
        ");
        
        $settings = ['default_language' => 'en', 'default_font_size' => '16', 'dark_mode_enabled' => '0'];
    }
} catch (Exception $e) {
    $settings = ['default_language' => 'en', 'default_font_size' => '16', 'dark_mode_enabled' => '0'];
    error_log("Settings error: " . $e->getMessage());
}
try {
    $stmt = $db->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $user = ['full_name' => 'Admin User', 'email' => 'admin@example.com'];
    }
} catch (Exception $e) {
    $user = ['full_name' => 'Admin User', 'email' => 'admin@example.com'];
    error_log("User fetch error: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Settings – Municipality Incident Reporting App</title>
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

                .containerr{
                background-color: whitesmoke;
                margin-top: 3%;
                border-radius: 2%;
                max-width: 1100px ;
                margin-left: 15%;
                padding:2px;
                flex:1;
                
            }

    :root{
      --red-1:#b72a22;
      --red-2:#c7463f;
      --muted:#6b7280;
      --muted-2:#9aa0a6;
      --card:#ffffff;
      --bg:#f5f6f8;
      --shadow:0 8px 24px rgba(16,24,40,0.06);
      --radius:10px;
      --green:#1db954;
      --danger:#e74c3c;
    }
    [data-theme="dark"] {
      --bg: #0f1720;
      --card: #1a1f2e;
      --muted: #9aa0a6;
      --muted-2: #6b7280;
    }
    [data-theme="dark"] body { color: #e5e7eb; }
    
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;font-family:Inter,sans-serif;}
    body { font-size: 16px; transition: font-size .15s ease; }

    .app{display:grid;grid-template-columns:260px 1fr;min-height:100vh;}

    .brand{display:flex;gap:12px;align-items:center}
    .crest{width:48px;height:48px;border-radius:8px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-size:24px}
    .brand h1{font-size:15px;margin:0;font-weight:800}
    .brand .sub{font-size:12px;opacity:0.95}
    .nav-section{font-size:13px;opacity:0.95;margin-top:8px;margin-bottom:6px}
    .nav a{display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;color:rgba(255,255,255,0.95);transition:all .2s}
    .nav a:hover{background:rgba(0,0,0,0.08)}
    .nav a.active{background:rgba(0,0,0,0.12);font-weight:700}
    .logout{margin-top:auto;background:rgba(255,255,255,0.12);padding:10px;border-radius:8px;text-align:center;font-weight:700;cursor:pointer;transition:all .2s}
    .logout:hover{background:rgba(255,255,255,0.18)}

    .main { display:flex;flex-direction:column; min-height:100vh; }
            .btn-danger {
            background-color: #a10e09ff;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #1a6fe0;
        }
  
    .content{padding:28px 36px 60px;flex:1}
    .container{max-width:1200px;margin:0 auto}
    .page-title{font-size:28px;font-weight:800;margin-bottom:12px}
    .page-sub{color:var(--muted);margin-bottom:18px}

    .card{background:var(--card);padding:20px;border-radius:12px;box-shadow:var(--shadow);margin-bottom:18px}

    .prefs{display:flex;flex-direction:column;gap:16px}
    .pref-item{display:flex;align-items:center;gap:14px;padding:12px;border-radius:8px;border:1px solid #f1f3f5}
    .pref-item .pref-left{width:220px;font-weight:700}
    .pref-item .pref-right{flex:1;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
    .select{padding:10px 12px;border-radius:8px;border:1px solid #e9edf0;background:#fff;font-size:14px;min-width:200px}
    [data-theme="dark"] .select{background:#2a3140;border-color:#3a4150;color:#e5e7eb}
    
    .slider{width:320px}
    .toggle{width:50px;height:28px;border-radius:999px;background:#e9edf0;position:relative;cursor:pointer;transition:all .2s}
    .toggle .knob{position:absolute;top:3px;left:3px;width:22px;height:22px;border-radius:50%;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,0.08);transition:all .18s}
    .toggle.on{background:linear-gradient(90deg,#2ecc71,#1db954)}
    .toggle.on .knob{left:25px}

    .util-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-radius:8px;border:1px solid #f1f3f5;background:#fff}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:none;cursor:pointer;font-weight:700;transition:all .2s}
    .btn.ghost{background:#fff;border:1px solid rgba(0,0,0,0.08);color:var(--red-1)}
    .btn.red{background:var(--danger);color:#fff}
    .small-btn{padding:8px 12px;border-radius:8px;border:1px solid #e9edf0;background:#fff;cursor:pointer}

    .notification{position:fixed;top:20px;right:20px;background:#fff;padding:16px 20px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:1000;display:none}
    .notification.show{display:block}
    .notification.success{border-left:4px solid var(--green)}
    .notification.error{border-left:4px solid var(--danger)}
  </style>
</head>
<body class="dashboard-body">

        <?php include 'adm_header.php'; ?>
        <br><br>
  <div class="notification" id="notification">
    <div id="notificationText"></div>
  </div>
 <div class="containerr">
      <main class="content">
        <div class="container">
          <div class="page-title">Settings</div>
          <div class="page-sub">Configure system preferences and settings.</div>

          <section class="card">
            <h3 style="margin:0 0 10px">General Preferences</h3>
            <div class="prefs">
              <div class="pref-item">
                <div class="pref-left">Language</div>
                <div class="pref-right">
                  <select class="select" id="languageSelect">
                    <option value="en" <?php echo ($settings['default_language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English (US)</option>
                  </select>
                </div>
              </div>

              <div class="pref-item">
                <div class="pref-left">Font Size</div>
                <div class="pref-right">
                  <input id="fontSlider" class="slider" type="range" min="12" max="20" value="<?php echo $settings['default_font_size'] ?? 16; ?>" />
                  <span id="fontValue"><?php echo $settings['default_font_size'] ?? 16; ?>px</span>
                </div>
              </div>

              <div class="pref-item">
                <div class="pref-left">Dark Mode</div>
                <div class="pref-right">
                  <div id="darkToggle" class="toggle <?php echo ($settings['dark_mode_enabled'] ?? 0) ? 'on' : ''; ?>">
                    <div class="knob"></div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="card">
            <h3 style="margin:0 0 10px">Archive Management</h3>
            <div style="display:flex;flex-direction:column;gap:12px">
              <div class="util-row">
                <div>View Archived Incidents</div>
                <button class="btn ghost" onclick="window.location.href='admin_archive.php?type=incidents'">View</button>
              </div>
              <div class="util-row">
                <div>View Archived Announcements</div>
                <button class="btn ghost" onclick="window.location.href='admin_archive.php?type=announcements'">View</button>
              </div>
            </div>
          </section>

          <section class="card">
            <h3 style="margin:0 0 10px">Utilities</h3>
            <div style="display:flex;flex-direction:column;gap:12px">
              <div class="util-row">
                <div>System Backup</div>
                <button class="btn ghost" id="runBackupBtn">Run Backup</button>
              </div>
              <div class="util-row">
                <div>Clear Cache</div>
                <button class="btn red" id="clearCacheBtn">Clear Cache</button>
              </div>
              <div class="util-row">
                <div>View Logs</div>
                <button class="small-btn" id="viewLogsBtn">View Logs</button>
              </div>
            </div>
                      <div style="margin-top:18px; margin-left: 92%; font-size: 50px; width: 25%;">
                        <button class="btn btn-danger" id="logout-btn" type="button" style="font-size: 15px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; font-weight: 10%;">Logout</button>
                    </div>
          </section>
            
        </div>
      </main>
    </div>
  </div>
  </div>
  <br><br>
    <?php include 'footer.html'; ?>
  <script>
    function showNotification(message, type = 'success') {
      const notification = document.getElementById('notification');
      const notificationText = document.getElementById('notificationText');
      notificationText.textContent = message;
      notification.className = 'notification show ' + type;
      setTimeout(() => notification.classList.remove('show'), 3000);
    }

    document.getElementById('languageSelect').addEventListener('change', function() {
      fetch('admin_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=update_language&language=' + this.value
      })
      .then(res => res.json())
      .then(data => showNotification(data.message, data.success ? 'success' : 'error'))
      .catch(err => showNotification('Error updating language', 'error'));
    });

    const fontSlider = document.getElementById('fontSlider');
    const fontValue = document.getElementById('fontValue');
    fontSlider.addEventListener('input', function() {
      fontValue.textContent = this.value + 'px';
      document.body.style.fontSize = this.value + 'px';
    });
    fontSlider.addEventListener('change', function() {
      fetch('admin_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=update_font_size&fontSize=' + this.value
      })
      .then(res => res.json())
      .then(data => showNotification(data.message, data.success ? 'success' : 'error'));
    });

    document.getElementById('darkToggle').addEventListener('click', function() {
      this.classList.toggle('on');
      const isDark = this.classList.contains('on');
      document.documentElement.setAttribute('data-theme', isDark ? 'dark' : '');
      
      fetch('admin_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle_dark_mode&darkMode=' + isDark
      })
      .then(res => res.json())
      .then(data => showNotification(data.message, data.success ? 'success' : 'error'));
    });

    <?php if (($settings['dark_mode_enabled'] ?? 0) == 1): ?>
    document.documentElement.setAttribute('data-theme', 'dark');
    <?php endif; ?>
    
    document.body.style.fontSize = '<?php echo $settings['default_font_size'] ?? 16; ?>px';

    document.getElementById('runBackupBtn').addEventListener('click', function() {
      if (!confirm('Create system backup?')) return;
      this.disabled = true;
      this.textContent = 'Running...';
      fetch('admin_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=backup_system'
      })
      .then(res => res.json())
      .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
        this.disabled = false;
        this.textContent = 'Run Backup';
      });
    });

    document.getElementById('clearCacheBtn').addEventListener('click', function() {
      if (!confirm('Clear application cache?')) return;
      fetch('admin_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=clear_cache'
      })
      .then(res => res.json())
      .then(data => showNotification(data.message, data.success ? 'success' : 'error'));
    });

    document.getElementById('viewLogsBtn').addEventListener('click', function() {
      fetch('admin_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_logs'
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          console.log('Logs:', data.logs);
          alert('Check console for logs (F12)');
        }
      });
    });
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'logout.php';
                }
            });
        }
  </script>
  
</body>
</html>
