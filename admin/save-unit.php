<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $working_hours = filter_input(INPUT_POST, 'working_hours', FILTER_SANITIZE_STRING);
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    if ($name && $address && $working_hours && $latitude && $longitude) {
        $db = Database::getInstance();
        
        try {
            $db->query(
                "INSERT INTO units (name, address, working_hours, latitude, longitude, description) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$name, $address, $working_hours, $latitude, $longitude, $description]
            );
            
            setSuccess('Birim başarıyla eklendi.');
        } catch (Exception $e) {
            setError('Birim eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    } else {
        setError('Lütfen tüm zorunlu alanları doldurun.');
    }
}

redirect('unit-manage.php'); 