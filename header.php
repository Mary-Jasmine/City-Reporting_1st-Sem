<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Municipality Incident Reporting'; ?></title>

   <style>
    body {
        margin: 0;
        font-family: "Poppins", Arial, sans-serif;
        background-color: #f5f5f5;
    }

    .header {
       background: linear-gradient(to bottom, #9c2a12, #c64242ff);
        padding: 10px 20px;
        color: #fff;
        width: 100%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        position: fixed;
        top: 0;
        z-index: 1000;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1300px;
        margin: 0 auto; 
        padding: 0 15px;
        gap: 10px;
    }

    
    .header-logo-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-right: auto;
        margin-left: -110px;
    }

    .logo-img {
        width: 150px; 
        height: 70px;
        object-fit: contain;
        margin-left: -20px; 
    }

    .logo-text {
        font-weight: 700;
        font-size: 15px;
        white-space: nowrap;
        color: white;
        margin-left: -10px; 
    }

    .header-center {
        display: flex;
        justify-content: center;
        flex: 1;
        margin: 0 20px;
    }

    .header-nav {
        display: flex;
        gap: 5px;
    }

    .header-nav .nav-link {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.3s, color 0.3s;
        padding: 6px 12px;
        border-radius: 6px;
    }

    .header-nav .nav-link:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .header-nav .nav-link.active {
        background: #c62828;
    }

    .header-right {
        display: flex;
        align-items: center;
        margin-left: auto;
        margin-right: -10px;
    }

    .settings-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        text-decoration: none;
        transition: background 0.3s, transform 0.2s;
        font-size: 20px;
        color: white;
    }

    .settings-icon:hover {
        background-color: rgba(255, 255, 255, 0.25);
        transform: scale(1.1);
    }

    .search-box {
        padding: 5px 10px;
        border-radius: 5px;
        border: none;
        background-color: rgba(255, 255, 255, 0.15);
        color: white;
        width: 190px;  
        margin-right: 30px;
    }

    .search-box::placeholder {
        color: #eee;
        width: 190px;
    }

    @media (max-width: 900px) {
        .header-container {
            flex-direction: column;
            gap: 10px;
        }

        .header-logo-container {
            justify-content: center;
            margin-left: 0;
            margin-right: 0;
        }

        .header-center {
            order: 3;
            margin: 0;
        }

        .header-right {
            order: 2;
            justify-content: center;
            margin-left: 0;
            margin-right: 0;
        }
    }

    body {
        padding-top: 80px;
    }

    </style>
</head>
<body class="dashboard-page">
    
    <div class="header">
        
        <div class="header-container">
            <div class="header-logo-container">
                <img src="ccircle.png" alt="Municipality Logo" class="logo-img" onerror="this.style.display='none'">
                <div class="logo-text">Municipality Incident Reporting System</div>
            </div>

            <div class="header-center">
                <nav class="header-nav">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Analytics</a>
                    <a href="submit-report.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'submit-report.php' ? 'active' : ''; ?>">Report</a>
                    <a href="hotlines.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hotlines.php' ? 'active' : ''; ?>">Hotlines</a>
                    <a href="heatmap.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'heatmap.php' ? 'active' : ''; ?>">Heatmap</a>
                    <a href="announcements.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>">Announcements</a>
                    <a href="firstaid.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'firstaid.php' ? 'active' : ''; ?>">Resources</a>
                </nav>
            </div>

            <div class="header-right">
                <a href="settings.php" class="settings-icon" title="Settings">👤</a>
            </div>
           
        </div>
    </div>
</body>
</html>