<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Haber ID'sini kontrol et
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Geçersiz Haber ID.');
}

$newsId = intval($_GET['id']);

// Haber sil
$db->query("DELETE FROM news WHERE id = ?", [$newsId]);

// Yönlendirme
header('Location: news-manage.php');
exit;
?>
