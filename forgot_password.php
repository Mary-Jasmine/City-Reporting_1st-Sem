<?php
require_once 'config.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email']);

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user["id"];

        $token = bin2hex(random_bytes(32));

        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $query = "INSERT INTO password_resets (user_id, token, expires_at) 
                  VALUES (:user_id, :token, :expires_at)";
        $reset = $db->prepare($query);
        $reset->bindParam(":user_id", $userId);
        $reset->bindParam(":token", $token);
        $reset->bindParam(":expires_at", $expiry);
        $reset->execute();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'YOUR_GMAIL@gmail.com';
            $mail->Password = 'YOUR_APP_PASSWORD';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('YOUR_GMAIL@gmail.com', 'Municipality Incident Reporting');
            $mail->addAddress($email);

            $resetLink = "https://yourwebsite.com/reset_password.php?token=$token";

            $mail->Subject = "Password Reset Request";
            $mail->Body = "Hello,\n\nClick the link below to reset your password:\n$resetLink\n\nThis link expires in 1 hour.";

            $mail->send();
            $success = "Password reset email has been sent!";
        } catch (Exception $e) {
            $error = "Email sending failed: " . $mail->ErrorInfo;
        }

    } else {
        $error = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Forgot Password</title></head>
<body>
    <h2>Forgot Password</h2>

    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>
