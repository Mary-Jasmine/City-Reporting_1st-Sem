<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements and Alerts</title>


<style>
    .content {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px;
        min-height: 70vh; 
    }
        body.ann-body{
            background: linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)), url('chujjrch.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .info-banner {
        background-color: #e8f5e9;
        border-left: 4px solid var(--green, #2d7a3a);
        padding: 15px;
        border-radius: var(--radius, 8px);
        margin-bottom: 30px;
        display: flex;
        gap: 12px;
        font-size: 1rem;
        color: var(--text-primary, #333);
        line-height: 1.5;
    }

    .info-icon {
        color: var(--green, #2d7a3a);
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .guides-grid, .videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px;
        margin-top: 20px;
        margin-bottom: 40px;
    }

    .guide-card, .video-card {
        background-color: var(--card, white);
        border-radius: var(--radius, 8px);
        overflow: hidden;
        box-shadow: var(--shadow, 0 4px 12px rgba(0, 0, 0, 0.04));
        display: flex;
        flex-direction: column;
        transition: transform 0.2s;
    }
    .guide-card:hover, .video-card:hover {
        transform: translateY(-3px);
    }
    
    .card-image img {
        width: 100%;
        height: 140px;
        object-fit: cover;
    }

    .video-thumbnail {
        width: 100%;
        height: 140px;
        position: relative;
    }

    .video-thumbnail iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .card-content, .video-content {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .card-badge {
        display: inline-block;
        background-color: var(--green, #2d7a3a);
        color: white;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .card-title, .video-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .card-description, .video-description {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.4;
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .read-guide-btn, .watch-now-btn {
        width: 100%;
        padding: 10px;
        background-color: var(--blue, #3b82f6);
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
            .container {max-width: 1200px;margin: auto;padding: 1%; background-color: white;margin-top: 5%;border-radius: 14px;margin-bottom: 7%}


    .read-guide-btn:hover, .watch-now-btn:hover {
        background-color: #2563eb;
    }
</style>

<body class="ann-body">
    <?php include 'header.php'; ?>
    <div class="container">
<div class="content">
    <h1 class="page-title">First Aid & Disaster Preparedness</h1>

    <div class="info-banner">
        <i class="fas fa-heart-pulse info-icon"></i>
        <div>All information on this page is carefully curated and verified by municipal health and disaster preparedness officials to ensure accuracy and reliability.</div>
    </div>

    <h2 class="section-title">Offline-Ready Guides</h2>
    <p class="section-description">Access essential first aid guides, even without an internet connection, to be prepared for any situation.</p>

    <div class="guides-grid">
        
        <div class="guide-card">
            <div class="card-image">
                <img src="cpr.png" alt="CPR Illustration">
            </div>
            <div class="card-content">
                <div class="card-badge">CPR</div>
                <div class="card-title">Hands-Only CPR Technique</div>
                <div class="card-description">Learn the essential steps for performing compression-only cardiopulmonary resuscitation (CPR) until professional help arrives.</div>
                <a href="HandsOnlyCPRsheet.pdf" target="_blank"><button class="read-guide-btn">Read Guide</button></a>
            </div>
        </div>

        <div class="guide-card">
            <div class="card-image">
                <img src="bandai.png" alt="Bandage Illustration">
            </div>
            <div class="card-content">
                <div class="card-badge">Injuries</div>
                <div class="card-title">Cuts, Scrapes, and Bleeding</div>
                <div class="card-description">A guide to treating common wounds, applying bandages correctly, and managing severe bleeding situations.</div>
                <a href="INJURIES.pdf" target="_blank"><button class="read-guide-btn">Read Guide</button></a>
            </div>
        </div>

        <div class="guide-card">
            <div class="card-image">
                <img src="firstaid.png" alt="First Aid Kit Illustration">
            </div>
            <div class="card-content">
                <div class="card-badge">Emergencies</div>
                <div class="card-title">Handling Common Medical Emergencies</div>
                <div class="card-description">Step-by-step instructions for dealing with choking, seizures, shock, burns, and other critical medical events.</div>
                <a href="EMERGENCIES.pdf" target="_blank"><button class="read-guide-btn">Read Guide</button></a>
            </div>
        </div>

        <div class="guide-card">
            <div class="card-image">
                <img src="fracture.png" alt="Fracture Illustration">
            </div>
            <div class="card-content">
                <div class="card-badge">Fractures</div>
                <div class="card-title">Splinting and Immobilizing Fractures</div>
                <div class="card-description">How to recognize signs of a broken bone and safely immobilize the injury before medical personnel can take over.</div>
                <a href="FRACTURES.pdf" target="_blank"><button class="read-guide-btn">Read Guide</button></a>
            </div>
        </div>

        <div class="guide-card">
            <div class="card-image">
                <img src="prepare.png" alt="Disaster Preparedness Illustration">
            </div>
            <div class="card-content">
                <div class="card-badge">Disasters</div>
                <div class="card-title">Essential Disaster Preparedness Checklist</div>
                <div class="card-description">A guide on creating an emergency kit, family evacuation plans, and what to do during a typhoon or earthquake.</div>
                <a href="DISASTERS.pdf" target="_blank"><button class="read-guide-btn">Read Guide</button></a>
            </div>
        </div>
    </div>

    <h2 class="section-title">Video Tutorials</h2>
    <p class="section-description">Watch these informative videos for practical demonstrations on disaster preparedness and first aid techniques.</p>

    <div class="videos-grid">
        
        <div class="video-card">
            <div class="video-thumbnail">
                <iframe src="https://www.youtube.com/embed/2PngCv7NjaI" title="Chest Compressions (CPR Steps)" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="video-content">
                <div class="video-title">Effective Chest Compressions (Adult CPR)</div>
                <div class="video-description">A quick guide demonstrating the proper depth and rate for adult chest compressions in an emergency.</div>
                <button class="watch-now-btn" onclick="window.open('https://www.youtube.com/watch?v=2PngCv7NjaI', '_blank');">Watch Now</button>
            </div>
        </div>

        <div class="video-card">
            <div class="video-thumbnail">
                <iframe src="https://www.youtube.com/embed/MKILThtPxQs" title="When The Earth Shakes - Animated Video" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="video-content">
                <div class="video-title">What to Do "When The Earth Shakes"</div>
                <div class="video-description">Animated guide on the correct "Drop, Cover, and Hold On" procedure during an earthquake.</div>
                <button class="watch-now-btn" onclick="window.open('https://www.youtube.com/watch?v=MKILThtPxQs', '_blank');">Watch Now</button>
            </div>
        </div>

        <div class="video-card">
            <div class="video-thumbnail">
                <iframe src="https://www.youtube.com/embed/2v8vlXgGXwE" title="How To Treat A Fracture & Fracture Types - First Aid Training - St John Ambulance" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="video-content">
                <div class="video-title">First Aid for Fractures & Broken Bones</div>
                <div class="video-description">Detailed first aid training from St John Ambulance on how to manage different types of fractures.</div>
                <button class="watch-now-btn" onclick="window.open('https://www.youtube.com/watch?v=2v8vlXgGXwE', '_blank');">Watch Now</button>
            </div>
        </div>

        <div class="video-card">
            <div class="video-thumbnail">
                <iframe src="https://www.youtube.com/embed/a4cIFZx1f2E?list=PLvd0isBh6beQelNrtp9EdNhwobGV_Taiu" title="Head Injury Symptoms & Advice - First Aid Training - St John Ambulance" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="video-content">
                <div class="video-title">Recognizing and Treating Head Injuries</div>
                <div class="video-description">Guidance on identifying symptoms of a serious head injury and the immediate steps to take.</div>
                <button class="watch-now-btn" onclick="window.open('https://www.youtube.com/watch?v=a4cIFZx1f2E', '_blank');">Watch Now</button>
            </div>
        </div>
    </div>
</div>
</div>
</body>
<?php include 'footer.html'; ?>