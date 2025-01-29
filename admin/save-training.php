<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = Database::getInstance();

    // Form verilerini al
    $unit_id = filter_input(INPUT_POST, 'unit_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_SANITIZE_NUMBER_INT);
    $deadline_date = filter_input(INPUT_POST, 'deadline_date', FILTER_SANITIZE_STRING);  // Capture the deadline date
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Hata kontrolü
    $errors = [];

    if (!$unit_id) {
        $errors[] = 'Birim seçilmedi.';
    }

    if (!$title) {
        $errors[] = 'Eğitim adı boş olamaz.';
    }

    if (!$start_date || !$end_date) {
        $errors[] = 'Başlangıç ve bitiş tarihleri gereklidir.';
    }

    if ($start_date > $end_date) {
        $errors[] = 'Başlangıç tarihi bitiş tarihinden sonra olamaz.';
    }

    if (!$capacity || $capacity <= 0) {
        $errors[] = 'Geçerli bir kapasite girilmedi.';
    }

    if (!$deadline_date) {  // Validate the deadline date
        $errors[] = 'Son başvuru tarihi gereklidir.';
    }

    // Eğer hata yoksa kaydet
    if (empty($errors)) {
        try {
            // Eğitim ID'si varsa güncelle, yoksa yeni kayıt ekle
            if (isset($_POST['training_id']) && $_POST['training_id']) {
                $training_id = filter_input(INPUT_POST, 'training_id', FILTER_SANITIZE_NUMBER_INT);
                
                $db->query(
                    "UPDATE trainings SET 
                        unit_id = ?, 
                        title = ?, 
                        description = ?, 
                        start_date = ?, 
                        end_date = ?, 
                        capacity = ?, 
                        deadline_date = ?,  -- Include deadline_date
                        is_active = ? 
                    WHERE id = ?",
                    [
                        $unit_id, 
                        $title, 
                        $description, 
                        $start_date, 
                        $end_date, 
                        $capacity, 
                        $deadline_date,  // Pass deadline_date for update
                        $is_active,
                        $training_id
                    ]
                );

                setSuccess('Eğitim başarıyla güncellendi.');
            } else {
                $db->query(
                    "INSERT INTO trainings 
                        (unit_id, title, description, start_date, end_date, capacity, deadline_date, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $unit_id, 
                        $title, 
                        $description, 
                        $start_date, 
                        $end_date, 
                        $capacity, 
                        $deadline_date,  // Pass deadline_date for insertion
                        $is_active
                    ]
                );

                setSuccess('Eğitim başarıyla eklendi.');
            }
        } catch (Exception $e) {
            setError('Bir hata oluştu: ' . $e->getMessage());
        }
    } else {
        // Hataları session'a kaydet
        foreach ($errors as $error) {
            setError($error);
        }
    }
}

// Eğer düzenleme işleminden geliyorsa ilgili sayfaya, değilse eğitim listesine yönlendir
if (isset($_POST['training_id'])) {
    redirect('training-edit.php?id=' . $_POST['training_id']);
} else {
    redirect('training-manage.php');
}
?>
