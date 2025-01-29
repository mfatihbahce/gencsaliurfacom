<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
checkAdmin();

header('Content-Type: application/json');

$db = Database::getInstance();

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

try {
    // Geçerli durumları kontrol et
    if (!in_array($status, ['pending', 'approved', 'rejected'])) {
        throw new Exception('Geçersiz durum.');
    }

    // Başvuru durumunu güncelle
    $result = $db->query(
        "UPDATE training_applications SET status = ? WHERE id = ?",
        [$status, $id]
    );

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Güncelleme yapılırken bir hata oluştu.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}