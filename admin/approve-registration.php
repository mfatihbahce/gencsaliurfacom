<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
checkAdmin();

$registration_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$training_id = filter_input(INPUT_GET, 'training_id', FILTER_SANITIZE_NUMBER_INT);
$unit_id = filter_input(INPUT_GET, 'unit_id', FILTER_SANITIZE_NUMBER_INT);

if ($registration_id && $training_id && $unit_id) {
    $db = Database::getInstance();
    
    try {
        // Başvuru bilgilerini al (gender alanını ekledik)
        $registration = $db->query(
            "SELECT ta.*, u.first_name, u.last_name, u.tc_no, u.birth_date, 
                    u.nationality, u.district_id, u.phone, u.gender
             FROM training_applications ta
             JOIN users u ON ta.user_id = u.id
             WHERE ta.id = ?",
            [$registration_id]
        )->fetch();

        if ($registration) {
            // Önce bu TC no ile aynı eğitimde kayıt var mı kontrol et
            $existingStudent = $db->query(
                "SELECT id FROM students 
                 WHERE tc_no = ? AND training_id = ?",
                [$registration['tc_no'], $training_id]
            )->fetch();

            if ($existingStudent) {
                setError('Bu öğrenci zaten bu eğitime kayıtlı.');
            } else {
                // Öğrenci listesine ekle (gender alanını ekledik)
                $db->query(
                    "INSERT INTO students SET 
                        first_name = ?,
                        last_name = ?,
                        tc_no = ?,
                        gender = ?,
                        birth_date = ?,
                        nationality = ?,
                        district_id = ?,
                        phone = ?,
                        unit_id = ?,
                        training_id = ?,
                        created_at = NOW()",
                    [
                        $registration['first_name'],
                        $registration['last_name'],
                        $registration['tc_no'],
                        $registration['gender'],
                        $registration['birth_date'],
                        $registration['nationality'],
                        $registration['district_id'],
                        $registration['phone'],
                        $unit_id,
                        $training_id
                    ]
                );

                // Başvuru durumunu güncelle
                $db->query(
                    "UPDATE training_applications 
                     SET status = 'approved' 
                     WHERE id = ?",
                    [$registration_id]
                );
                
                setSuccess('Başvuru onaylandı ve öğrenci listesine eklendi.');
            }
        } else {
            setError('Başvuru bulunamadı.');
        }
    } catch (Exception $e) {
        setError('Başvuru onaylanırken bir hata oluştu: ' . $e->getMessage());
    }
}

redirect('training-applications.php?id=' . $training_id);