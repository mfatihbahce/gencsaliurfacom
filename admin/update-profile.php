<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect('my-profile.php');
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance();

// Form verilerini al
$data = [
    'first_name' => filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING),
    'last_name' => filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING),
    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
    'phone' => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING),
    'birth_date' => filter_input(INPUT_POST, 'birth_date'),
    'gender' => filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING)
];

// Profil fotoğrafı yükleme
if (!empty($_FILES['profile_image']['name'])) {
    $upload_dir = 'uploads/profile/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    if (in_array($file_extension, $allowed_extensions)) {
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $data['image_path'] = $new_filename;
        }
    }
}

// Veritabanını güncelle
try {
    $columns = array_keys($data);
    $values = array_values($data);
    $set_clause = implode('=?, ', $columns) . '=?';
    
    $values[] = $user_id; // WHERE koşulu için

    $stmt = $db->query(
        "UPDATE users SET $set_clause WHERE id = ?",
        $values
    );

    if ($stmt) {
        setSuccess('Profil bilgileriniz başarıyla güncellendi.');
    } else {
        setError('Profil güncellenirken bir hata oluştu.');
    }
} catch (Exception $e) {
    setError('Bir hata oluştu: ' . $e->getMessage());
}

redirect('my-profile.php');