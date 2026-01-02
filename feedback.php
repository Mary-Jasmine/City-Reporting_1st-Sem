<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists('config.php')) {
    die('Error: config.php not found. Please ensure the file exists in the same directory.');
}

include "config.php";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        die('Database connection failed. Please check your database settings.');
    }
} catch (Exception $e) {
    die('Database error: ' . $e->getMessage());
}

$submitSuccess = false;
$submitError = false;
$errorMessage = '';

try {
    $checkTable = $conn->query("SHOW TABLES LIKE 'feedback'");
    if ($checkTable->rowCount() == 0) {
        die('Error: feedback table does not exist. Please run the SQL script to create the table first.');
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $category = $_POST['category'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    
    if (empty($name) || empty($email) || empty($category) || empty($subject) || empty($message)) {
        $submitError = true;
        $errorMessage = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $submitError = true;
        $errorMessage = 'Please enter a valid email address.';
    } elseif (strlen($message) < 20) {
        $submitError = true;
        $errorMessage = 'Message must be at least 20 characters long.';
    } else {
        try {
            $sql = "INSERT INTO feedback (
                        name, email, phone, category, subject, message, rating, 
                        status, submitted_at, created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW()
                    )";
            
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . print_r($conn->errorInfo(), true));
            }
            
            $result = $stmt->execute([
                $name, 
                $email, 
                $phone, 
                $category, 
                $subject, 
                $message, 
                $rating
            ]);
            
            if ($result) {
                $submitSuccess = true;
                $_POST = array();
                
                try {
                    $logSql = "INSERT INTO system_logs (user_id, action, details, ip_address, created_at) 
                               VALUES (NULL, 'Feedback Submitted', ?, ?, NOW())";
                    $logStmt = $conn->prepare($logSql);
                    $logStmt->execute([
                        'Feedback from: ' . $name . ' - ' . substr($subject, 0, 50),
                        $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                    ]);
                } catch (Exception $logEx) {
                    error_log("Failed to log feedback submission: " . $logEx->getMessage());
                }
            } else {
                $submitError = true;
                $errorMessage = 'Failed to submit feedback. Error: ' . print_r($stmt->errorInfo(), true);
            }
        } catch (PDOException $e) {
            $submitError = true;
            $errorMessage = 'Database error: ' . $e->getMessage();
            error_log("Feedback submission error: " . $e->getMessage());
        } catch (Exception $e) {
            $submitError = true;
            $errorMessage = 'An error occurred: ' . $e->getMessage();
            error_log("General error: " . $e->getMessage());
        }
    }
}

try {
    $statsQuery = "SELECT 
        COUNT(*) as total_feedback,
        ROUND(
            (SUM(CASE WHEN admin_response IS NOT NULL THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100,
            0
        ) as response_rate,
        ROUND(
            AVG(CASE 
                WHEN responded_at IS NOT NULL 
                THEN DATEDIFF(responded_at, submitted_at) 
                ELSE NULL 
            END),
            0
        ) as avg_response_days
        FROM feedback";
    
    $statsStmt = $conn->query($statsQuery);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $totalFeedback = $stats['total_feedback'] ?? 0;
    $responseRate = $stats['response_rate'] ?? 98;
    $avgResponseDays = $stats['avg_response_days'] ?? 3;
} catch (Exception $e) {
    $totalFeedback = 0;
    $responseRate = 98;
    $avgResponseDays = 3;
    error_log("Stats query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback & Suggestions - Municipal Incident Reporting</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
        }
        
        body.feedback-body {
            background: linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)), url('chujjrch.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        .content {
            max-width: 900px;
            margin: 5% auto 7% auto;
            padding: 40px 50px;
            background-color: white;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .page-header p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }
        
        .info-banner {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-left: 4px solid #2d7a3a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: start;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background-color: #2d7a3a;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-content h3 {
            font-size: 18px;
            color: #2d7a3a;
            margin-bottom: 8px;
        }
        
        .info-content p {
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .feedback-form {
            display: grid;
            gap: 25px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-group label .required {
            color: #e74c3c;
            margin-left: 3px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }
        
        .form-group .helper-text {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }
        
        /* Rating Section */
        .rating-container {
            margin-top: 10px;
        }
        
        .rating-stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .rating-stars input[type="radio"] {
            display: none;
        }
        
        .rating-stars label {
            font-size: 32px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }
        
        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input[type="radio"]:checked ~ label {
            color: #ffa500;
        }
        
        .rating-text {
            font-size: 13px;
            color: #666;
            font-weight: normal;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 10px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff5b5b 0%, #c0352e 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #e74c3c 0%, #a82a24 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }
        
        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .feedback-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #f0f0f0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #c0352e;
            margin-bottom: 5px;
        }
        
        .stat-card .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @media (max-width: 768px) {
            .content {
                margin: 20px;
                padding: 30px 25px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .feedback-stats {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="feedback-body">
    <?php 
    if (file_exists('header.php')) {
        include 'header.php'; 
    }
    ?>
    
    <div class="content">
        <div class="page-header">
            <h1> Feedback & Suggestions</h1>
            <p>Help us improve our municipal services by sharing your feedback, suggestions, or concerns.</p>
        </div>
        
        <div class="info-banner">
            <div class="info-icon">ℹ</div>
            <div class="info-content">
                <h3>Your Voice Matters</h3>
                <p>We value your input and use it to continuously improve our incident reporting system and municipal services. Your feedback helps us serve you better. All submissions are reviewed by our team within 3-5 business days.</p>
            </div>
        </div>
        
        <?php if ($submitSuccess): ?>
        <div class="alert alert-success">
            <strong>✓ Thank you!</strong> Your feedback has been submitted successfully. We appreciate you taking the time to help us improve our services.
        </div>
        <?php endif; ?>
        
        <?php if ($submitError): ?>
        <div class="alert alert-error">
            <strong>✗ Error:</strong> <?= htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>
        
        <form class="feedback-form" method="POST" action="" id="feedbackForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Barangay Name<span class="required">*</span></label>
                    <input type="text" id="name" name="name" required 
                           value="<?= $submitSuccess ? '' : htmlspecialchars($_POST['name'] ?? ''); ?>"
                           placeholder="Enter your Barangay name">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address<span class="required">*</span></label>
                    <input type="email" id="email" name="email" required 
                           value="<?= $submitSuccess ? '' : htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="your.email@example.com">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number (Optional)</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= $submitSuccess ? '' : htmlspecialchars($_POST['phone'] ?? ''); ?>"
                           placeholder="+63 XXX XXX XXXX">
                </div>
                
                <div class="form-group">
                    <label for="category">Feedback Category<span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">Select a category...</option>
                        <option value="general" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'general') ? 'selected' : ''; ?>>General Feedback</option>
                        <option value="bug_report" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'bug_report') ? 'selected' : ''; ?>>Bug Report</option>
                        <option value="feature_request" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'feature_request') ? 'selected' : ''; ?>>Feature Request</option>
                        <option value="service_quality" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'service_quality') ? 'selected' : ''; ?>>Service Quality</option>
                        <option value="complaint" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'complaint') ? 'selected' : ''; ?>>Complaint</option>
                        <option value="praise" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'praise') ? 'selected' : ''; ?>>Praise/Appreciation</option>
                        <option value="suggestion" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'suggestion') ? 'selected' : ''; ?>>Suggestion</option>
                        <option value="other" <?= (!$submitSuccess && ($_POST['category'] ?? '') == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="subject">Subject<span class="required">*</span></label>
                <input type="text" id="subject" name="subject" required 
                       value="<?= $submitSuccess ? '' : htmlspecialchars($_POST['subject'] ?? ''); ?>"
                       placeholder="Brief summary of your feedback">
            </div>
            
            <div class="form-group full-width">
                <label for="message">Message<span class="required">*</span></label>
                <textarea id="message" name="message" required 
                          placeholder="Please provide detailed feedback. The more information you share, the better we can address your concerns or implement your suggestions."><?= $submitSuccess ? '' : htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                <span class="helper-text">Minimum 20 characters</span>
            </div>
            
            <div class="form-group full-width">
                <label>Overall Experience Rating</label>
                <div class="rating-container">
                    <div class="rating-stars">
                        <input type="radio" id="star5" name="rating" value="5" <?= (!$submitSuccess && ($_POST['rating'] ?? 0) == 5) ? 'checked' : ''; ?>>
                        <label for="star5">⭐</label>
                        
                        <input type="radio" id="star4" name="rating" value="4" <?= (!$submitSuccess && ($_POST['rating'] ?? 0) == 4) ? 'checked' : ''; ?>>
                        <label for="star4">⭐</label>
                        
                        <input type="radio" id="star3" name="rating" value="3" <?= (!$submitSuccess && ($_POST['rating'] ?? 0) == 3) ? 'checked' : ''; ?>>
                        <label for="star3">⭐</label>
                        
                        <input type="radio" id="star2" name="rating" value="2" <?= (!$submitSuccess && ($_POST['rating'] ?? 0) == 2) ? 'checked' : ''; ?>>
                        <label for="star2">⭐</label>
                        
                        <input type="radio" id="star1" name="rating" value="1" <?= (!$submitSuccess && ($_POST['rating'] ?? 0) == 1) ? 'checked' : ''; ?>>
                        <label for="star1">⭐</label>
                    </div>
                    <span class="rating-text">Rate your experience with our system (optional)</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Reset Form</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Submit Feedback</button>
            </div>
        </form>
        
        <div class="feedback-stats">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($totalFeedback); ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $responseRate; ?>%</div>
                <div class="stat-label">Response Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $avgResponseDays; ?></div>
                <div class="stat-label">Days Avg Response</div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            const message = document.getElementById('message').value;
            const submitBtn = document.getElementById('submitBtn');
            
            if (message.length < 20) {
                e.preventDefault();
                alert('Please provide a more detailed message (minimum 20 characters).');
                document.getElementById('message').focus();
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        });
        
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transition = 'opacity 0.5s';
                setTimeout(() => successAlert.remove(), 500);
            }, 5000);
        }
        
        window.addEventListener('load', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Feedback';
        });
    </script>
    
    <?php 
    if (file_exists('footer.html')) {
        include 'footer.html'; 
    }
    ?>
</body>
</html>