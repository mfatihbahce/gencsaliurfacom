<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$unit_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($unit_id) {
    $db = Database::getInstance();
    
    try {
        // Önce birime ait görselleri sil
        $gallery_images = $db->query("SELECT image_path FROM unit_gallery WHERE unit_id = ?", [$unit_id])->fetchAll();
        foreach ($gallery_images as $image) {
            $image_path = '../uploads/units/' . $image['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Birime ait galeri kayıtlarını sil
        $db->query("DELETE FROM unit_gallery WHERE unit_id = ?", [$unit_id]);
        
        // Birimi sil
        $db->query("DELETE FROM units WHERE id = ?", [$unit_id]);
        
        setSuccess('Birim ve ilgili tüm içerikler başarıyla silindi.');
    } catch (Exception $e) {
        setError('Birim silinirken bir hata oluştu.');
    }
}

redirect('unit-manage.php'); 