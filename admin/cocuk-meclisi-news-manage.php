<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';
checkAdmin();

$db = Database::getInstance();

// Durum değiştirme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_status') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $status = $_POST['status'];
    
    $stmt = $db->query(
        "UPDATE cocuk_meclisi_news SET status = ?, updated_at = NOW() WHERE id = ?", 
        [$status, $id]
    );
    
    if ($stmt) {
        setSuccess('Haber durumu güncellendi.');
    } else {
        setError('Haber durumu güncellenirken bir hata oluştu.');
    }
    
    header('Location: cocuk-meclisi-news-manage.php');
    exit;
}

// Haberleri getir
$cocuk_meclisi_news = $db->query(
    "SELECT n.*, u.first_name, u.last_name 
     FROM cocuk_meclisi_news n 
     LEFT JOIN users u ON n.user_id = u.id 
     ORDER BY n.created_at DESC"
)->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Haber Yönetimi</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
            <i class="fas fa-plus"></i> Yeni Haber Ekle
        </button>
    </div>

    <!-- Haberler Listesi -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($cocuk_meclisi_news)): ?>
                <div class="alert alert-info">Henüz haber bulunmamaktadır.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Görsel</th>
                                <th>Başlık</th>
                                <th>Durum</th>
                                <th>Ekleyen</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cocuk_meclisi_news as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td>
                                        <?php if (!empty($item['image_path']) && file_exists('../uploads/news/' . $item['image_path'])): ?>
                                            <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($item['image_path']) ?>" 
                                                 alt="Haber görseli" 
                                                 class="img-thumbnail" 
                                                 style="height: 50px; width: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <img src="<?= SITE_URL ?>/assets/img/default-news.jpg" 
                                                 alt="Varsayılan görsel" 
                                                 class="img-thumbnail" 
                                                 style="height: 50px; width: 50px; object-fit: cover;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['title']) ?></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" 
                                                    onchange="this.form.submit()"
                                                    style="width: 120px;">
                                                <option value="pending" <?= $item['status'] == 'pending' ? 'selected' : '' ?>>
                                                    Beklemede
                                                </option>
                                                <option value="approved" <?= $item['status'] == 'approved' ? 'selected' : '' ?>>
                                                    Onaylandı
                                                </option>
                                                <option value="rejected" <?= $item['status'] == 'rejected' ? 'selected' : '' ?>>
                                                    Reddedildi
                                                </option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <?= $item['first_name'] && $item['last_name'] 
                                            ? htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) 
                                            : '-' ?>
                                    </td>
                                    <td>
                                        <?= date('d.m.Y H:i', strtotime($item['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewNewsModal<?= $item['id'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="cocuk-meclisi-edit-news.php?id=<?= $item['id'] ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="deleteNews(<?= $item['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Haber Görüntüleme Modal -->
                                <div class="modal fade" id="viewNewsModal<?= $item['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?= htmlspecialchars($item['title']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if (!empty($item['image_path']) && file_exists('../uploads/news/' . $item['image_path'])): ?>
                                                    <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($item['image_path']) ?>" 
                                                         class="img-fluid rounded mb-3" 
                                                         alt="<?= htmlspecialchars($item['title']) ?>">
                                                <?php endif; ?>
                                                <div class="news-content">
                                                    <?= $item['content'] ?>
                                                </div>
                                                <hr>
                                                <div class="text-muted">
                                                    <small>
                                                        <i class="fas fa-calendar"></i> 
                                                        Eklenme: <?= date('d.m.Y H:i', strtotime($item['created_at'])) ?>
                                                        <?php if ($item['first_name'] && $item['last_name']): ?>
                                                            <br>
                                                            <i class="fas fa-user"></i> 
                                                            Ekleyen: <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Yeni Haber Ekleme Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Haber Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="cocuk-meclisi-save-news.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İçerik</label>
                        <textarea class="form-control" name="content" id="content" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Görsel</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="pending">Beklemede</option>
                            <option value="approved">Onaylı</option>
                            <option value="rejected">Reddedildi</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteNews(id) {
    if (confirm('Bu haberi silmek istediğinize emin misiniz?')) {
        window.location.href = 'cocuk-meclisi-delete-news.php?id=' + id;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('content');
    }
});
</script>

<style>
.status-form {
    margin: 0;
}

.table td {
    vertical-align: middle;
}

.btn-group {
    gap: 0.25rem;
}
</style>

<?php include 'includes/footer.php'; ?>