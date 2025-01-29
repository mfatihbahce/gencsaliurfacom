<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// POST isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setError('Geçersiz istek yöntemi.');
    header('Location: youth-survey.php');
    exit;
}

// Form verilerini session'da saklayalım
$_SESSION['form_data'] = $_POST;

try {
    $db = Database::getInstance();
    
    // Form verilerini temizle ve hazırla
    $data = [
        'age_group' => clean($_POST['age_group'] ?? ''),
        'gender' => clean($_POST['gender'] ?? ''),
        'is_council_member' => isset($_POST['is_council_member']) ? (int)$_POST['is_council_member'] : 0,
        'desired_services' => isset($_POST['desired_services']) ? implode(',', $_POST['desired_services']) : '',
        'service_details' => clean($_POST['service_details'] ?? ''),
        'communication_channels' => isset($_POST['communication_channels']) ? implode(',', $_POST['communication_channels']) : '',
        'feedback_channels' => isset($_POST['feedback_channels']) ? implode(',', $_POST['feedback_channels']) : '',
        'activity_suggestion' => clean($_POST['activity_suggestion'] ?? ''),
        'service_rating' => clean($_POST['service_rating'] ?? ''),
        'previous_contact' => isset($_POST['previous_contact']) ? (int)$_POST['previous_contact'] : 0,
        'contact_method' => clean($_POST['contact_method'] ?? ''),
        'additional_feedback' => clean($_POST['additional_feedback'] ?? ''),
        'contact_info' => clean($_POST['contact_info'] ?? '')
    ];

    // Zorunlu alan kontrolü
    $missing_fields = [];
    $required_fields = [
        'age_group' => 'Yaş grubu',
        'gender' => 'Cinsiyet',
        'is_council_member' => 'Meclis üyeliği',
        'desired_services' => 'Hizmet seçimi',
        'service_rating' => 'Hizmet değerlendirmesi',
        'previous_contact' => 'Önceki iletişim durumu'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty($data[$field])) {
            $missing_fields[] = $label;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception('Lütfen şu alanları doldurun: ' . implode(', ', $missing_fields));
    }

    // Hizmet seçimi kontrolü (1-3 arası)
    $selected_services = explode(',', $data['desired_services']);
    if (count($selected_services) < 1 || count($selected_services) > 3) {
        throw new Exception('Lütfen en az 1, en fazla 3 hizmet seçin.');
    }

    // SQL sorgusu
    $sql = "INSERT INTO youth_surveys (
        age_group, gender, is_council_member, 
        desired_services, service_details,
        communication_channels, feedback_channels,
        activity_suggestion, service_rating,
        previous_contact, contact_method,
        additional_feedback, contact_info
    ) VALUES (
        ?, ?, ?, 
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?
    )";

    // Sorguyu çalıştır
    $result = $db->query($sql, [
        $data['age_group'],
        $data['gender'],
        $data['is_council_member'],
        $data['desired_services'],
        $data['service_details'],
        $data['communication_channels'],
        $data['feedback_channels'],
        $data['activity_suggestion'],
        $data['service_rating'],
        $data['previous_contact'],
        $data['contact_method'],
        $data['additional_feedback'],
        $data['contact_info']
    ]);

    if ($result) {
        // Başarılı kayıt durumunda session'daki form verilerini temizle
        unset($_SESSION['form_data']);
        setSuccess('Anketiniz başarıyla kaydedildi. Katılımınız için teşekkür ederiz!');
        header('Location: youth-survey.php');
        exit;
    } else {
        throw new Exception('Anket kaydedilirken bir hata oluştu.');
    }

} catch (Exception $e) {
    setError($e->getMessage());
    // Hata durumunda aynı sayfaya yönlendir
    header('Location: youth-survey.php#error-section');
    exit;
}