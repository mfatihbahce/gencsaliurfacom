<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
checkAdmin();

header('Content-Type: application/json');

$db = Database::getInstance();

$application_id = filter_input(INPUT_POST, 'application_id', FILTER_SANITIZE_NUMBER_INT);
$training_id = filter_input(INPUT_POST, 'training_id', FILTER_SANITIZE_NUMBER_INT);
$unit_id = filter_input(INPUT_POST, 'unit_id', FILTER_SANITIZE_NUMBER_INT);

try {
    // Başvuru bilgilerini al
    $application = $db->query(
        "SELECT ta.*, u.email, u.first_name, u.last_name, u.phone, u.tc_no, 
                u.birth_date, u.district_id, u.nationality
         FROM training_applications ta
         JOIN users u ON ta.user_id = u.id
         WHERE ta.id = ? AND ta.status = 'approved'",
        [$application_id]
    )->fetch();

    if (!$application) {
        throw new Exception('Başvuru bulunamadı veya onaylanmamış.');
    }

    // Öğrenci zaten ekli mi kontrol et
    $existing = $db->query(
        "SELECT id FROM students WHERE tc_no = ? AND training_id = ?",
        [$application['tc_no'], $training_id]
    )->fetch();

    if ($existing) {
        throw new Exception('Bu öğrenci zaten listeye eklenmiş.');
    }

    // Öğrenci listesine ekle
    $result = $db->query(
        "INSERT INTO students (
            training_id, 
            unit_id, 
            first_name, 
            last_name, 
            tc_no, 
            birth_date, 
            nationality, 
            district_id,
            phone,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [
            $training_id,
            $unit_id,
            $application['first_name'],
            $application['last_name'],
            $application['tc_no'],
            $application['birth_date'],
            $application['nationality'],
            $application['district_id'],
            $application['phone']
        ]
    );

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Öğrenci eklenirken bir hata oluştu.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}