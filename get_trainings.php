<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$category = $_GET['category'] ?? '';
$db = Database::getInstance();

if ($category) {
    try {
        $trainings = $db->query(
            "SELECT id, name FROM training_types WHERE category = ? ORDER BY name",
            [$category]
        )->fetchAll();
        
        echo json_encode($trainings, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Eğitimler getirilirken bir hata oluştu'], JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Kategori parametresi gerekli'], JSON_UNESCAPED_UNICODE);
}