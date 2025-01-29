<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tc = filter_input(INPUT_POST, 'tc', FILTER_SANITIZE_STRING);
    
    if (strlen($tc) == 11 && is_numeric($tc)) {
        $db = Database::getInstance();
        
        try {
            $member = $db->query(
                "SELECT name, tc, phone, email, DATE_FORMAT(created_at, '%d.%m.%Y') as created_at 
                 FROM members WHERE tc = ?", 
                [$tc]
            )->fetch();
            
            if ($member) {
                echo json_encode([
                    'success' => true,
                    'member' => $member
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu TC Kimlik numarasına ait üyelik kaydı bulunamadı.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Sorgulama sırasında bir hata oluştu.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz TC Kimlik numarası.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek.'
    ]);
}