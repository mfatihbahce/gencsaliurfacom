<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$unit_id = filter_input(INPUT_GET, 'unit_id', FILTER_SANITIZE_NUMBER_INT);

if ($unit_id) {
    $db = Database::getInstance();
    
    $trainings = $db->query(
        "SELECT id, title, start_date, end_date 
         FROM trainings 
         WHERE unit_id = ? AND is_active = 1 AND end_date >= CURDATE() 
         ORDER BY start_date",
        [$unit_id]
    )->fetchAll();

    // Tarihleri formatla
    foreach ($trainings as &$training) {
        $training['start_date'] = date('d.m.Y', strtotime($training['start_date']));
        $training['end_date'] = date('d.m.Y', strtotime($training['end_date']));
    }

    header('Content-Type: application/json');
    echo json_encode($trainings);
} 