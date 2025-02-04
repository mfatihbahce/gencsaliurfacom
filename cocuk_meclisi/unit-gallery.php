<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
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

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= htmlspecialchars($unit['name']) ?> - Galeri Yönetimi</h3>
    </div>
    <div class="card-body">
        <form action="save-unit-gallery.php" method="POST" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" name="unit_id" value="<?= $unit_id ?>">
            <div class="mb-3">
                <label class="form-label">Yeni Görsel(ler) Ekle</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Yükle</button>
        </form>

        <div class="row g-4">
            <?php foreach ($gallery as $image): ?>
            <div class="col-md-4">
                <div class="card">
                    <img src="<?= SITE_URL ?>/uploads/units/<?= $image['image_path'] ?>" 
                         class="card-img-top" alt="Galeri Görseli">
                    <div class="card-body">
                        <form action="delete-gallery-image.php" method="POST" 
                              onsubmit="return confirm('Bu görseli silmek istediğinizden emin misiniz?')">
                            <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                            <input type="hidden" name="unit_id" value="<?= $unit_id ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 