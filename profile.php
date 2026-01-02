<style>
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #002e8bff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin-right: 20px;
        }
        
        .profile-info h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        
        .profile-info p {
            color: #6c757d;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .field {
            margin-bottom: 15px;
        }
        
        .field label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .field p {
            font-size: 16px;
        }
        
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 25px 0;
        }
        
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

        <div class="main-content">

            <div class="tab-container">
                <div class="tab active" data-page="profile">Profile</div>
                <div class="tab" data-page="settings">Settings</div>
            </div>

            <div id="profile-page" class="page active">
                <div class="page-title">
                    <h1>User Profile</h1>
                    <p>View and manage your personal information</p>
                </div>

                <div class="card">
                    <div class="profile-header">
                        <div class="avatar">EV</div>
                        <div class="profile-info">
                            <h2>Elara Vance</h2>
                            <p>Registered User</p>
                        </div>
                    </div>

                    <div class="profile-grid">
                        <div class="field">
                            <label>Full Name</label>
                            <p>Mary Jasmine Manalo</p>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <p>majayjay0123.com</p>
                        </div>
                        <div class="field">
                            <label>Contact Number</label>
                            <p>+6391778928</p>
                        </div>
                        <div class="field">
                            <label>Barangay</label>
                            <p>Barangay Pinagdanlayan</p>
                        </div>
                        <div class="field">
                            <label>Age</label>
                            <p>21</p>
                        </div>
                        <div class="field">
                            <label>Sex</label>
                            <p>Female</p>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <button class="btn btn-primary" id="edit-profile-btn">Edit Profile</button>
                </div>

            </div>

</body>
</html>