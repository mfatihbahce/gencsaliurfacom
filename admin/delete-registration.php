<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$registration_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$training_id = filter_input(INPUT_GET, 'training_id', FILTER_SANITIZE_NUMBER_INT);

if ($registration_id && $training_id) {
    $db = Database::getInstance();
    
    try {
        $db->query("DELETE FROM training_registrations WHERE id = ?", [$registration_id]);
        setSuccess('Kayıt başarıyla silindi.');
    } catch (Exception $e) {
        setError('Kayıt silinirken bir hata oluştu.');
    }
}

redirect('training-registrations.php?id=' . $training_id); 