<?php

$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
.dashboard-container {
    display: flex;
}
    .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 900vh;
    overflow-y: auto;
    background-color: #8B0B0B;
    z-index: 1 1; 
}

.main-content {
    margin-left: 250px;
    padding: 20px;
}

</style>

<div class="sidebar">
    <br><br><br>
    <div class="sidebar-header">
        <div class="municipality-logo">🏛️</div>
        <h3>OFFICIAL TOOLS</h3>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            Analytics Dashboard
        </a>
        <a href="submit-report.php" class="nav-item <?php echo $current_page == 'submit-report.php' ? 'active' : ''; ?>">
            Submit Report
        </a>
        <a href="hotlines.php" class="nav-item <?php echo $current_page == 'hotlines.php' ? 'active' : ''; ?>">
            Hotlines
        </a>
        
        <div class="nav-section">
            <h4>EXPLORE</h4>
            <a href="heatmap.php" class="nav-item <?php echo $current_page == 'heatmap.php' ? 'active' : ''; ?>">
                Heatmap
            </a>
            <a href="announcements.php" class="nav-item <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>">
                Announcements
            </a>
            <a href="firstaid.php" class="nav-item <?php echo $current_page == 'firstaid.php' ? 'active' : ''; ?>">
                First Aid Resources
            </a>
            <a href="settings.php" class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                Settings
            </a>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>