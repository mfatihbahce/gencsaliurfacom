<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    if ($title && $content) {
        $db = Database::getInstance();
        
        try {
            $db->query(
                "INSERT INTO cocuk_meclisi_news (title, content) VALUES (?, ?)",
                [$title, $content]
            );
            
            setSuccess('Haber başarıyla eklendi.');
        } catch (Exception $e) {
            setError('Haber eklenirken bir hata oluştu.');
        }
    } else {
        setError('Lütfen tüm alanları doldurun.');
    }
}

header('Location: cocuk-meclisi-news-manage.php');
exit;