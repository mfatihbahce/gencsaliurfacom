<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // Önce eski resmi sil
    $old_image = $db->query("SELECT image_path FROM gallery WHERE id = ?", [$id])->fetch();
    if ($old_image && $old_image['image_path']) {
        @unlink('../uploads/gallery/' . $old_image['image_path']);
    }
    
    // Galeri öğesini sil
    $stmt = $db->query("DELETE FROM gallery WHERE id = ?", [$id]);
    if ($stmt) {
        setSuccess('Görsel başarıyla silindi.');
    } else {
        setError('Görsel silinirken bir hata oluştu.');
    }
    
    header('Location: gallery-manage.php');
    exit;
}

// Galeri öğelerini getir
$gallery = $db->query("SELECT * FROM gallery ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Galeri Yönetimi</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
            Yeni Görsel Ekle
        </button>
    </div>

    <!-- Galeri Grid -->
    <div class="row g-4">
        <?php foreach ($gallery as $item): ?>
            <div class="col-md-3">
                <div class="card h-100 gallery-card">
                    <div class="gallery-image-container">
                        <img src="<?= SITE_URL ?>/uploads/gallery/<?= $item['image_path'] ?>" 
                             class="card-img-top gallery-image" 
                             alt="<?= clean($item['title']) ?>">
                        
                        <!-- Hover Overlay -->
                        <div class="gallery-overlay">
                            <button type="button" class="btn btn-light btn-sm mb-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewGalleryModal<?= $item['id'] ?>">
                                <i class="bi bi-eye"></i> Görüntüle
                            </button>
                            <form method="POST" class="d-inline" 
                                  onsubmit="return confirm('Bu görseli silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Sil
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-truncate"><?= clean($item['title']) ?></h5>
                        <?php if (!empty($item['description'])): ?>
                            <p class="card-text small text-muted">
                                <?= mb_substr(clean($item['description']), 0, 50) ?>...
                            </p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <?= date('d.m.Y', strtotime($item['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Görsel Görüntüleme Modal -->
            <div class="modal fade" id="viewGalleryModal<?= $item['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= clean($item['title']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <img src="<?= SITE_URL ?>/uploads/gallery/<?= $item['image_path'] ?>" 
                                 class="img-fluid rounded" alt="<?= clean($item['title']) ?>">
                            <?php if (!empty($item['description'])): ?>
                                <div class="mt-3">
                                    <h6>Açıklama:</h6>
                                    <p class="text-muted"><?= nl2br(clean($item['description'])) ?></p>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <small class="text-muted">
                                Eklenme Tarihi: <?= date('d.m.Y H:i', strtotime($item['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<!-- Yeni Görsel Ekleme Modal -->
<div class="modal fade" id="addGalleryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Görsel Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="save-gallery.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" name="title[]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Görsel</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" required>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Stil -->
<style>
.gallery-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.gallery-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-image-container:hover .gallery-overlay {
    opacity: 1;
}

.card-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.card-text {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .col-md-3 {
        width: 50%;
    }
}

@media (max-width: 576px) {
    .col-md-3 {
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>