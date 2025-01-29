<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $url = isset($_POST['url']) ? $_POST['url'] : null;
    $image_path = null;
    
    // Görsel yükleme işlemi
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/projects/';
        
        // Klasör yoksa oluştur
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                $image_path = $new_filename;
            } else {
                setError('Görsel yüklenirken bir hata oluştu.');
                header('Location: projects-manage.php');
                exit;
            }
        } else {
            setError('Geçersiz dosya formatı. Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir.');
            header('Location: projects-manage.php');
            exit;
        }
    }
    
    if ($title && $description && $image_path) {
        $db = Database::getInstance();
        
        try {
            $stmt = $db->query(
                "INSERT INTO projects (title, description, url, image_path) VALUES (?, ?, ?, ?)",
                [$title, $description, $url, $image_path]
            );
            
            if ($stmt) {
                setSuccess('Proje başarıyla eklendi.');
            } else {
                setError('Proje eklenirken bir hata oluştu.');
            }
        } catch (Exception $e) {
            setError('Veritabanı hatası: ' . $e->getMessage());
        }
    } else {
        setError('Lütfen başlık, açıklama ve görsel alanlarını doldurun.');
    }
}

header('Location: projects-manage.php');
exit;