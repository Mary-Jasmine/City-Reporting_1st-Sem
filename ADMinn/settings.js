class SettingsManager {
    constructor() {
        this.notification = document.getElementById('notification');
        this.notificationText = document.getElementById('notificationText');
        this.init();
    }

    init() {
        this.setupFontSize();
        this.setupDarkMode();
        this.setupLanguage();
        this.setupUtilities();
        this.loadSavedSettings();
    }

    showNotification(message, type = 'success') {
        this.notificationText.textContent = message;
        this.notification.className = `notification ${type} show`;
        
        setTimeout(() => {
            this.notification.classList.remove('show');
        }, 3000);
    }

    setupFontSize() {
        const slider = document.getElementById('fontSlider');
        const value = document.getElementById('fontValue');
        
        slider.addEventListener('input', () => {
            const size = slider.value;
            value.textContent = size + 'px';
            document.body.style.fontSize = size + 'px';
            localStorage.setItem('fontSize', size);
        });

        slider.addEventListener('change', () => {
            this.saveSetting('update_font_size', { fontSize: slider.value });
        });
    }

    setupDarkMode() {
        const toggle = document.getElementById('darkToggle');
        
        toggle.addEventListener('click', () => {
            const isDark = document.documentElement.hasAttribute('data-theme');
            const newState = !isDark;
            
            if (newState) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
            
            toggle.classList.toggle('on', newState);
            toggle.setAttribute('aria-checked', newState);
            localStorage.setItem('darkMode', newState);
            
            this.saveSetting('toggle_dark_mode', { darkMode: newState });
        });
    }

    setupLanguage() {
        const select = document.getElementById('languageSelect');
        
        select.addEventListener('change', () => {
            this.saveSetting('update_language', { language: select.value });
        });
    }

    setupUtilities() {
        document.getElementById('runBackupBtn').addEventListener('click', () => {
            this.confirmAction(
                'Run System Backup',
                'This will create a full backup of the database and configuration. Continue?',
                () => this.performBackup()
            );
        });

        document.getElementById('clearCacheBtn').addEventListener('click', () => {
            this.confirmAction(
                'Clear Application Cache',
                'Clearing cache will refresh cached assets and data. Continue?',
                () => this.clearCache()
            );
        });

        document.getElementById('viewLogsBtn').addEventListener('click', () => {
            this.viewLogs();
        });

        document.getElementById('overlayCancel').addEventListener('click', () => {
            this.closeOverlay();
        });
    }

    loadSavedSettings() {
        const isDark = localStorage.getItem('darkMode') === 'true';
        if (isDark) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.getElementById('darkToggle').classList.add('on');
        }

        const fontSize = localStorage.getItem('fontSize');
        if (fontSize) {
            document.body.style.fontSize = fontSize + 'px';
            document.getElementById('fontSlider').value = fontSize;
            document.getElementById('fontValue').textContent = fontSize + 'px';
        }
    }

    async saveSetting(action, data) {
        try {
            const formData = new FormData();
            formData.append('action', action);
            
            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }

            const response = await fetch('admin_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error saving setting: ' + error.message, 'error');
        }
    }

    async performBackup() {
        this.closeOverlay();
        this.showNotification('Backup in progress...', 'success');

        try {
            const formData = new FormData();
            formData.append('action', 'backup_system');

            const response = await fetch('admin_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Backup failed: ' + error.message, 'error');
        }
    }

    async clearCache() {
        this.closeOverlay();
        this.showNotification('Clearing cache...', 'success');

        try {
            const formData = new FormData();
            formData.append('action', 'clear_cache');

            const response = await fetch('admin_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Cache clear failed: ' + error.message, 'error');
        }
    }

    async viewLogs() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_logs');

            const response = await fetch('admin_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.displayLogs(result.logs);
            } else {
                this.showNotification('Failed to load logs', 'error');
            }
        } catch (error) {
            this.showNotification('Error loading logs: ' + error.message, 'error');
        }
    }

    displayLogs(logs) {
        const overlay = document.getElementById('overlay');
        const title = document.getElementById('overlayTitle');
        const body = document.getElementById('overlayBody');
        const confirmBtn = document.getElementById('overlayConfirm');

        title.textContent = 'System Logs';
        confirmBtn.textContent = 'Close';
        
        let html = '<div style="max-height:400px;overflow:auto;padding-right:8px">';
        html += '<div style="font-weight:700;margin-bottom:12px">Recent Activity</div>';
        
        if (logs.length > 0) {
            html += '<table style="width:100%;font-size:13px;border-collapse:collapse">';
            html += '<thead><tr style="background:#f9fafb;text-align:left"><th style="padding:8px">User</th><th style="padding:8px">Action</th><th style="padding:8px">Details</th><th style="padding:8px">Time</th></tr></thead>';
            html += '<tbody>';
            
            logs.forEach(log => {
                const date = new Date(log.created_at);
                const timeAgo = this.getTimeAgo(date);
                
                html += '<tr style="border-bottom:1px solid #f1f3f5">';
                html += `<td style="padding:8px">${log.full_name || 'System'}</td>`;
                html += `<td style="padding:8px;font-weight:600">${log.action}</td>`;
                html += `<td style="padding:8px;color:#6b7280">${log.details || '-'}</td>`;
                html += `<td style="padding:8px;color:#9ca3af">${timeAgo}</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
        } else {
            html += '<p style="color:#9ca3af;text-align:center;padding:20px">No logs found</p>';
        }
        
        html += '</div>';
        body.innerHTML = html;
        
        overlay.style.display = 'flex';
        
        confirmBtn.onclick = () => this.closeOverlay();
    }

    confirmAction(title, message, onConfirm) {
        const overlay = document.getElementById('overlay');
        const overlayTitle = document.getElementById('overlayTitle');
        const overlayBody = document.getElementById('overlayBody');
        const confirmBtn = document.getElementById('overlayConfirm');

        overlayTitle.textContent = title;
        overlayBody.textContent = message;
        confirmBtn.textContent = 'Confirm';
        
        overlay.style.display = 'flex';
        
        confirmBtn.onclick = () => {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        };
    }

    closeOverlay() {
        document.getElementById('overlay').style.display = 'none';
    }

    getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        const intervals = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60
        };
        
        for (const [unit, secondsInUnit] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInUnit);
            if (interval >= 1) {
                return interval + ' ' + unit + (interval === 1 ? '' : 's') + ' ago';
            }
        }
        
        return 'Just now';
    }
}

class ArchiveManager {
    constructor() {
        this.setupArchiveHandlers();
    }

    setupArchiveHandlers() {
        this.addArchiveButtons();
    }

    async archiveItem(type, id) {
        if (!confirm(`Are you sure you want to archive this ${type}?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', type === 'incident' ? 'archive_incident' : 'archive_announcement');
            formData.append(type === 'incident' ? 'incident_id' : 'announcement_id', id);

            const response = await fetch('admin_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Error archiving item: ' + error.message);
        }
    }

    async bulkArchive(type, ids) {
        if (!confirm(`Are you sure you want to archive ${ids.length} items?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'bulk_archive');
            formData.append('table', type);
            formData.append('ids', JSON.stringify(ids));

            const response = await fetch('admin_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Error archiving items: ' + error.message);
        }
    }

    addArchiveButtons() {
        console.log('Archive manager ready');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const settingsManager = new SettingsManager();
    const archiveManager = new ArchiveManager();
    
    window.archiveItem = (type, id) => archiveManager.archiveItem(type, id);
    window.bulkArchive = (type, ids) => archiveManager.bulkArchive(type, ids);
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('overlay');
        if (overlay && overlay.style.display === 'flex') {
            overlay.style.display = 'none';
        }
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SettingsManager, ArchiveManager };
}