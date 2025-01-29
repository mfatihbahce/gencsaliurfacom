<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';
checkAdmin();

$db = Database::getInstance();

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    if ($_POST['action'] == 'delete') {
        // Önce eski resmi sil
        $old_image = $db->query("SELECT image_path FROM places WHERE id = ?", [$id])->fetch();
        if ($old_image && $old_image['image_path']) {
            @unlink('../uploads/places/' . $old_image['image_path']);
        }
        
        // Mekanı sil
        $stmt = $db->query("DELETE FROM places WHERE id = ?", [$id]);
        if ($stmt) {
            setSuccess('Mekan başarıyla silindi.');
        } else {
            setError('Mekan silinirken bir hata oluştu.');
        }
    } elseif ($_POST['action'] == 'change_status') {
        $status = $_POST['status'];
        $stmt = $db->query(
            "UPDATE places SET status = ? WHERE id = ?", 
            [$status, $id]
        );
        if ($stmt) {
            setSuccess('Mekan durumu güncellendi.');
        } else {
            setError('Mekan durumu güncellenirken bir hata oluştu.');
        }
    }
    
    header('Location: places-manage.php');
    exit;
}

// Mekanları getir
$places = $db->query(
    "SELECT p.*, d.name as district_name, u.first_name, u.last_name 
     FROM places p 
     LEFT JOIN districts d ON p.district_id = d.id
     LEFT JOIN users u ON p.user_id = u.id 
     ORDER BY p.created_at DESC"
)->fetchAll();

// İlçeleri getir (select için)
$districts = $db->query("SELECT * FROM districts ORDER BY name")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Mekan Yönetimi</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlaceModal">
            <i class="fas fa-plus"></i> Yeni Mekan Ekle
        </button>
    </div>

    <!-- Mekanlar Listesi -->
    <div class="row g-4">
        <?php foreach ($places as $place): ?>
            <div class="col-md-3">
                <div class="card h-100 place-card">
                    <div class="position-relative">
                        <?php if (!empty($place['image_path']) && file_exists('../uploads/places/' . $place['image_path'])): ?>
                            <img src="<?= SITE_URL ?>/uploads/places/<?= e($place['image_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= e($place['title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="<?= SITE_URL ?>/assets/img/default-place.jpg" 
                                 class="card-img-top" 
                                 alt="Varsayılan görsel"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <!-- Durum Badge -->
                        <div class="position-absolute top-0 end-0 m-2">
                            <?php
                            $status_class = [
                                'pending' => 'bg-warning',
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger'
                            ];
                            $status_text = [
                                'pending' => 'Beklemede',
                                'approved' => 'Onaylandı',
                                'rejected' => 'Reddedildi'
                            ];
                            ?>
                            <span class="badge <?= $status_class[$place['status']] ?>">
                                <?= $status_text[$place['status']] ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate">
                            <?= e($place['title']) ?>
                        </h5>
                        <p class="card-text small text-muted mb-2">
                            <i class="fas fa-map-marker-alt"></i> <?= e($place['district_name']) ?>
                        </p>
                        <p class="card-text flex-grow-1">
                            <?= mb_substr(strip_tags($place['description']), 0, 100) ?>...
                        </p>
                        <div class="mt-auto">
                            <div class="btn-group w-100 mb-2">
                                <button type="button" 
                                        class="btn btn-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewPlaceModal<?= $place['id'] ?>">
                                    <i class="fas fa-eye"></i> Görüntüle
                                </button>
                                <button type="button" 
                                        class="btn btn-danger btn-sm" 
                                        onclick="if(confirm('Bu mekanı silmek istediğinize emin misiniz?')) 
                                                 document.getElementById('deleteForm<?= $place['id'] ?>').submit();">
                                    <i class="fas fa-trash"></i> Sil
                                </button>
                            </div>
                            
                            <!-- Durum Değiştirme -->
                            <div class="btn-group w-100">
                                <form method="POST" class="w-100">
                                    <input type="hidden" name="action" value="change_status">
                                    <input type="hidden" name="id" value="<?= $place['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" 
                                            onchange="this.form.submit()">
                                        <option value="pending" <?= $place['status'] == 'pending' ? 'selected' : '' ?>>
                                            Beklemede
                                        </option>
                                        <option value="approved" <?= $place['status'] == 'approved' ? 'selected' : '' ?>>
                                            Onayla
                                        </option>
                                        <option value="rejected" <?= $place['status'] == 'rejected' ? 'selected' : '' ?>>
                                            Reddet
                                        </option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Silme Formu -->
            <form id="deleteForm<?= $place['id'] ?>" method="POST" class="d-none">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $place['id'] ?>">
            </form>

            <!-- Mekan Görüntüleme Modal -->
            <div class="modal fade" id="viewPlaceModal<?= $place['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= e($place['title']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($place['image_path']) && file_exists('../uploads/places/' . $place['image_path'])): ?>
                                <img src="<?= SITE_URL ?>/uploads/places/<?= e($place['image_path']) ?>" 
                                     class="img-fluid rounded mb-3" 
                                     alt="<?= e($place['title']) ?>">
                            <?php endif; ?>
                            
                            <h6 class="fw-bold">Mekan Detayları:</h6>
                            <p class="text-muted"><?= nl2br(e($place['description'])) ?></p>
                            
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> 
                                        Eklenme: <?= date('d.m.Y H:i', strtotime($place['created_at'])) ?>
                                        <br>
                                        <i class="fas fa-map-marker-alt"></i> 
                                        İlçe: <?= e($place['district_name']) ?>
                                        <?php if ($place['first_name'] && $place['last_name']): ?>
                                            <br>
                                            <i class="fas fa-user"></i> 
                                            Ekleyen: <?= e($place['first_name'] . ' ' . $place['last_name']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Yeni Mekan Ekleme Modal -->
    <div class="modal fade" id="addPlaceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Mekan Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="save-place.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Mekan Adı</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İlçe</label>
                            <select name="district_id" class="form-select" required>
                                <option value="">İlçe Seçin</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?= $district['id'] ?>">
                                        <?= e($district['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Görsel</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.place-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.place-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.card-title {
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
}

.card-text {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1rem;
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