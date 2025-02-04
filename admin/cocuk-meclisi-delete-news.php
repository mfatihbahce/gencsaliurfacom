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
$db->query("DELETE FROM cocuk_meclisi_news WHERE id = ?", [$newsId]);

// Yönlendirme
header('Location: cocuk-meclisi-news-manage.php');
exit;
?>
