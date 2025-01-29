<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = Database::getInstance();
    
    try {
        // Uyruk kontrolü
        $nationality = trim($_POST['nationality']);
        if ($nationality === 'Diğer') {
            $nationality = trim($_POST['nationality_actual'] ?? '');
            if (empty($nationality)) {
                throw new Exception('Lütfen uyruğunuzu belirtiniz.');
            }
        }

        // Verileri al ve temizle
        $data = [
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'tc_no' => trim($_POST['tc_no']),
            'birth_date' => $_POST['birth_date'],
            'nationality' => $nationality,
            'district_id' => filter_input(INPUT_POST, 'district_id', FILTER_SANITIZE_NUMBER_INT),
            'neighborhood' => trim($_POST['neighborhood']),
            'phone' => trim($_POST['phone']),
            'training_id' => filter_input(INPUT_POST, 'training_id', FILTER_SANITIZE_NUMBER_INT),
            'registration_type' => 'manual'
        ];

        // Zorunlu alanları kontrol et
        foreach ($data as $key => $value) {
            if (empty($value)) {
                throw new Exception('Lütfen tüm alanları doldurun.');
            }
        }

        // TC No kontrolü
        if (!preg_match('/^[0-9]{11}$/', $data['tc_no'])) {
            throw new Exception('Geçersiz TC Kimlik No.');
        }

        // Telefon formatı kontrolü
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            throw new Exception('Geçersiz telefon numarası.');
        }

        // Mükerrer kayıt kontrolü
        $existing = $db->query(
            "SELECT id FROM training_registrations 
             WHERE tc_no = ? AND training_id = ?",
            [$data['tc_no'], $data['training_id']]
        )->fetch();

        if ($existing) {
            throw new Exception('Bu TC Kimlik No ile daha önce başvuru yapılmış.');
        }

        // Eğitim kapasitesi kontrolü
        $training = $db->query(
            "SELECT capacity, 
                    (SELECT COUNT(*) FROM training_registrations 
                     WHERE training_id = trainings.id) as registered 
             FROM trainings 
             WHERE id = ?",
            [$data['training_id']]
        )->fetch();

        if ($training['registered'] >= $training['capacity']) {
            throw new Exception('Bu eğitim için kontenjan dolmuştur.');
        }

        // Kayıt ekle
        $db->query(
            "INSERT INTO training_registrations (
                first_name, last_name, tc_no, birth_date, nationality,
                district_id, neighborhood, phone, training_id, registration_type,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $data['first_name'],
                $data['last_name'],
                $data['tc_no'],
                $data['birth_date'],
                $data['nationality'],
                $data['district_id'],
                $data['neighborhood'],
                $phone,
                $data['training_id'],
                $data['registration_type']
            ]
        );

        setSuccess('Başvuru başarıyla kaydedildi.');
        redirect('training-manage.php');

    } catch (Exception $e) {
        setError($e->getMessage());
        redirect('add-student-registration.php');
    }
} else {
    redirect('add-student-registration.php');
}