<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $image_id = filter_input(INPUT_POST, 'image_id', FILTER_SANITIZE_NUMBER_INT);
    $unit_id = filter_input(INPUT_POST, 'unit_id', FILTER_SANITIZE_NUMBER_INT);
    
    if ($image_id && $unit_id) {
        $db = Database::getInstance();
        
        // Görsel bilgilerini al
        $image = $db->query(
            "SELECT image_path FROM unit_gallery WHERE id = ? AND unit_id = ?",
            [$image_id, $unit_id]
        )->fetch();

        if ($image) {
            try {
                // Veritabanından sil
                $db->query("DELETE FROM unit_gallery WHERE id = ?", [$image_id]);
                
                // Dosyayı sil
                $file_path = '../uploads/units/' . $image['image_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                setSuccess('Görsel başarıyla silindi.');
            } catch (Exception $e) {
                setError('Görsel silinirken bir hata oluştu.');
            }
        }
    }
}

redirect('unit-gallery.php?id=' . $unit_id); 