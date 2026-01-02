<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET["token"])) {
    die("Invalid link.");
}

$token = $_GET["token"];

$stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
$stmt->bindParam(":token", $token);
$stmt->execute();

if ($stmt->rowCount() != 1) {
    die("This link is invalid or expired.");
}

$resetData = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $resetData["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $update = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
    $update->bindParam(":password", $newPassword);
    $update->bindParam(":id", $userId);
    $update->execute();

    $del = $db->prepare("DELETE FROM password_resets WHERE user_id = :id");
    $del->bindParam(":id", $userId);
    $del->execute();

    echo "<p>Password successfully updated. <a href='login.php'>Login</a></p>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Reset Your Password</h2>

    <form method="POST">
        <label>New Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Update Password</button>
    </form>
</body>
</html>
