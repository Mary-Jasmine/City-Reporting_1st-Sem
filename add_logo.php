<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_FILES['logo']['name'])) {

        $upload_dir = "uploads/logo/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $newFile = $upload_dir . "system_logo.png";  
        move_uploaded_file($_FILES['logo']['tmp_name'], $newFile);

        header("Location: admin_dashboard.php?msg=Logo Updated");
        exit();
    }
}
?>
