<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

$unit_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$unit = $db->query("SELECT * FROM units WHERE id = ?", [$unit_id])->fetch();

if (!$unit) {
    setError('Birim bulunamadı.');
    redirect('unit-manage.php');
}

// Mevcut görselleri getir
$gallery = $db->query("SELECT * FROM unit_gallery WHERE unit_id = ?", [$unit_id])->fetchAll();

$page_title = "Galeri Yönetimi - " . $unit['name'];
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="bi bi-images me-2"></i><?= htmlspecialchars($unit['name']) ?> - Galeri Yönetimi
        </h2>
        <a href="unit-manage.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Geri Dön
        </a>
    </div>

    <!-- Yükleme Formu -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="save-unit-gallery.php" method="POST" enctype="multipart/form-data" class="mb-0">
                <input type="hidden" name="unit_id" value="<?= $unit_id ?>">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Yeni Görsel(ler) Ekle</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                        <small class="text-muted">Birden fazla görsel seçebilirsiniz. (JPG, PNG, GIF)</small>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-1"></i>Yükle
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Galeri -->
    <div class="card">
        <div class="card-body">
            <?php if ($gallery): ?>
                <div class="row g-4">
                    <?php foreach ($gallery as $image): ?>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <img src="<?= SITE_URL ?>/uploads/units/<?= $image['image_path'] ?>" 
                                 class="card-img-top" alt="Galeri Görseli"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <form action="delete-gallery-image.php" method="POST" 
                                      onsubmit="return confirm('Bu görseli silmek istediğinizden emin misiniz?')">
                                    <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                    <input type="hidden" name="unit_id" value="<?= $unit_id ?>">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="bi bi-trash me-1"></i>Sil
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i>Henüz görsel eklenmemiş.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 