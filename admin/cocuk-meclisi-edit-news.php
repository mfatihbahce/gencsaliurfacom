<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Haber ID'sini al
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Geçersiz Haber ID.');
}

$newsId = intval($_GET['id']);

// Haber detaylarını getir
$cocuk_meclisi_news = $db->query("SELECT * FROM cocuk_meclisi_news WHERE id = ?", [$newsId])->fetch();

if (!$cocuk_meclisi_news) {
    die('Haber bulunamadı.');
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $error = 'Tüm alanlar doldurulmalıdır.';
    } else {
        $db->query("UPDATE cocuk_meclisi_news SET title = ?, content = ? WHERE id = ?", [$title, $content, $newsId]);
        header('Location: cocuk-meclisi-news-manage.php');
        exit;
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Haberi Düzenle</h2>
    <a href="news-manage.php" class="btn btn-secondary">Geri Dön</a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form action="" method="POST">
    <div class="mb-3">
        <label class="form-label">Başlık</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($cocuk_meclisi_news['title']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">İçerik</label>
        <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($cocuk_meclisi_news['content']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Güncelle</button>
</form>

<?php include 'includes/footer.php'; ?>