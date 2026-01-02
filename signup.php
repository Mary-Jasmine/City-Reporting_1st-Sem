<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT barangay_id, barangay_name FROM barangay_stats ORDER BY barangay_name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

$SITE_KEY = "6LfgmRcsAAAAAJk-fO9sSGO-4uFJ8dOq2XzgstCj";
$SECRET_KEY = "6LfgmRcsAAAAAD4MR1htN3z3f9lypLB3Z7ctJI-M";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $captcha = $_POST['g-recaptcha-response'] ?? '';
    if (!$captcha) {
        $error = "Please verify you are not a robot.";
    } else {
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$SECRET_KEY&response=$captcha");
        $response = json_decode($verify);
        if (!$response->success) {
            $error = "Captcha verification failed!";
        }
    }

    if (!isset($error)) {
        $full_name = sanitizeInput($_POST['fullName']);
        $email = sanitizeInput($_POST['emailAddress']);
        $contact_number = sanitizeInput($_POST['contactNumber']);
        $barangay = sanitizeInput($_POST['barangay']);
        $password = password_hash($_POST['regPassword'], PASSWORD_DEFAULT);

    }

    if (!isset($error)) {


        try {
            $query = "INSERT INTO users 
                (full_name, email, password, contact_number, barangay_id)
                VALUES 
                (:full_name, :email, :password, :contact_number, :barangay_id)";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':contact_number', $contact_number);
            $stmt->bindParam(':barangay_id', $barangay);

            if ($stmt->execute()) {
                header("Location: index.php?registered=1");
                exit();
            }

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                die("DATABASE ERROR: " . $e->getMessage());

                if (strpos($msg, 'email') !== false) {
                    $error = "This Email is already registered.";
                } elseif (strpos($msg, 'contact_number') !== false) {
                    $error = "This Contact Number is already registered.";
                } else {
                    $error = "A record with this specific detail (Email, Phone, or Name) already exists.";
                }
            } else {
                $error = "System Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipality Incident Reporting - Register</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<style>
    #validId {
        opacity: 0;
        position: absolute;
        z-index: -1;
    }

    .file-upload-wrapper {
        position: relative;
        margin-bottom: 15px;
    }

    .custom-file-btn {
        display: inline-block;
        background-color: whitesmoke;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: 0.2s ease;
        margin-top: 5px;
        border-color: #1e1a1aff;
    }

    .custom-file-btn:hover {
        background: #cccbcbff;
    }

    .file-selected-name {
        margin-left: 10px;
        font-size: 14px;
        color: #444;
    }
</style>

<body class="register-page">
    <div class="register-container">
        <div class="register-card">
            <div class="app-header">
                <h1 class="app-title">Municipality Incident Reporting</h1>
                <p class="app-subtitle" style="font-size: 18px;">System</p>
                <div class="app-logo"><img src="logowo.png" alt=""></div>
            </div>

            <h2 class="register-title">Create Your Account</h2>
            <p class="register-subtitle">Join us to report incidents and help build a safer, more responsive community.
            </p>

            <?php if (isset($error)): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="register-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fullName">Full Barangay Name</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>

                <div class="form-group">
                    <label for="emailAddress">Email Address</label>
                    <input type="email" id="emailAddress" name="emailAddress" required>
                </div>

                <div class="form-group">
                    <label for="contactNumber">Contact Number</label>
                    <input type="tel" id="contactNumber" name="contactNumber" required>
                </div>

                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select id="barangay" name="barangay" required>
                        <option value="">Select your Barangay</option>
                        <?php foreach ($barangays as $b): ?>
                            <option value="<?php echo $b['barangay_id']; ?>">
                                <?php echo htmlspecialchars($b['barangay_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="captcha-group" style="margin-left: 10%;">
                    <div class="g-recaptcha" data-sitekey="<?php echo $SITE_KEY; ?>"></div>
                </div>

                <div class="form-group">
                    <label for="regPassword">Password</label>
                    <input type="password" id="regPassword" name="regPassword" placeholder="Create a strong password"
                        required>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword"
                        placeholder="Re-enter your password" required>
                </div>

                <button type="submit" class="signup-btn">Sign Up</button>
                <p class="login-link">Already have an account? <a href="index.php">Login</a></p>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('regPassword').addEventListener('input', validatePassword);
        document.getElementById('confirmPassword').addEventListener('input', validatePassword);

        function validatePassword() {
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (password !== confirmPassword) {
                document.getElementById('confirmPassword').style.borderColor = 'red';
            } else {
                document.getElementById('confirmPassword').style.borderColor = '#ddd';
            }

            document.getElementById("validId").addEventListener("change", function () {
                const fileName = this.files[0]?.name || "No file chosen";
                document.querySelector(".file-selected-name").textContent = fileName;
            });

        }
    </script>
</body>

</html>