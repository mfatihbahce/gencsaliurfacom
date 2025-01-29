<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unit_id = filter_input(INPUT_POST, 'unit_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (!$unit_id) {
        setError('Geçersiz birim ID.');
        redirect('unit-manage.php');
    }

    // Yükleme klasörünü kontrol et
    $upload_dir = '../uploads/units/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $success_count = 0;
    $error_count = 0;

    if (isset($_FILES['images'])) {
        $db = Database::getInstance();

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_type = $_FILES['images']['type'][$key];
                $file_size = $_FILES['images']['size'][$key];
                
                if (!in_array($file_type, $allowed_types)) {
                    $error_count++;
                    continue;
                }

                if ($file_size > $max_size) {
                    $error_count++;
                    continue;
                }

                // Benzersiz dosya adı oluştur
                $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $extension;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($tmp_name, $destination)) {
                    try {
                        $db->query(
                            "INSERT INTO unit_gallery (unit_id, image_path) VALUES (?, ?)",
                            [$unit_id, $new_filename]
                        );
                        $success_count++;
                    } catch (Exception $e) {
                        unlink($destination);
                        $error_count++;
                    }
                } else {
                    $error_count++;
                }
            }
        }

        if ($success_count > 0) {
            setSuccess($success_count . ' görsel başarıyla yüklendi.');
        }
        if ($error_count > 0) {
            setError($error_count . ' görsel yüklenemedi.');
        }
    }
}

redirect('unit-gallery.php?id=' . $unit_id); 