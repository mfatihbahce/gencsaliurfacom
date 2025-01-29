<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';
checkAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $district_id = filter_input(INPUT_POST, 'district_id', FILTER_SANITIZE_NUMBER_INT);
    $description = trim($_POST['description']);
    
    // Görsel yükleme
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../uploads/places/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            }
        }
    }
    
    // Veritabanına kaydet
    $stmt = $db->query(
        "INSERT INTO places (title, district_id, description, image_path, user_id, status, created_at) 
         VALUES (?, ?, ?, ?, ?, 'pending', NOW())",
        [$title, $district_id, $description, $image_path, $_SESSION['user_id']]
    );
    
    if ($stmt) {
        setSuccess('Mekan başarıyla eklendi.');
    } else {
        setError('Mekan eklenirken bir hata oluştu.');
    }
}

header('Location: places-manage.php');
exit;
?> 