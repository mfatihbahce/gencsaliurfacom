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
        $old_image = $db->query("SELECT image_path FROM projects WHERE id = ?", [$id])->fetch();
        if ($old_image && $old_image['image_path']) {
            @unlink('../uploads/projects/' . $old_image['image_path']);
        }
        
        // Projeyi sil
        $stmt = $db->query("DELETE FROM projects WHERE id = ?", [$id]);
        if ($stmt) {
            setSuccess('Duyuru başarıyla silindi.');
        } else {
            setError('Duyuru silinirken bir hata oluştu.');
        }
    } elseif ($_POST['action'] == 'change_status') {
        $status = $_POST['status'];
        $stmt = $db->query(
            "UPDATE projects SET status = ? WHERE id = ?", 
            [$status, $id]
        );
        if ($stmt) {
            setSuccess('Duyuru durumu güncellendi.');
        } else {
            setError('Duyuru durumu güncellenirken bir hata oluştu.');
        }
    }
    
    header('Location: projects-manage.php');
    exit;
}

// Projeleri getir
$projects = $db->query(
    "SELECT p.*, u.first_name, u.last_name 
     FROM projects p 
     LEFT JOIN users u ON p.user_id = u.id 
     ORDER BY p.created_at DESC"
)->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Duyuru Yönetimi</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="fas fa-plus"></i> Yeni Duyuru Ekle
        </button>
    </div>

    <!-- Projeler Listesi -->
    <div class="row g-4">
        <?php foreach ($projects as $project): ?>
            <div class="col-md-3">
                <div class="card h-100 project-card">
                    <div class="position-relative">
                        <?php if (!empty($project['image_path']) && file_exists('../uploads/projects/' . $project['image_path'])): ?>
                            <img src="<?= SITE_URL ?>/uploads/projects/<?= htmlspecialchars($project['image_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($project['title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="<?= SITE_URL ?>/assets/img/default-project.jpg" 
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
                            <span class="badge <?= $status_class[$project['status']] ?>">
                                <?= $status_text[$project['status']] ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate">
                            <?= htmlspecialchars($project['title']) ?>
                        </h5>
                        <p class="card-text flex-grow-1">
                            <?= mb_substr(strip_tags($project['description']), 0, 100) ?>...
                        </p>
                        <div class="mt-auto">
                            <div class="btn-group w-100 mb-2">
                                <button type="button" 
                                        class="btn btn-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewProjectModal<?= $project['id'] ?>">
                                    <i class="fas fa-eye"></i> Görüntüle
                                </button>
                                <button type="button" 
                                        class="btn btn-danger btn-sm" 
                                        onclick="if(confirm('Bu duyuruyu silmek istediğinize emin misiniz?')) 
                                                 document.getElementById('deleteForm<?= $project['id'] ?>').submit();">
                                    <i class="fas fa-trash"></i> Sil
                                </button>
                            </div>
                            
                            <!-- Durum Değiştirme -->
                            <div class="btn-group w-100">
                                <form method="POST" class="w-100">
                                    <input type="hidden" name="action" value="change_status">
                                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" 
                                            onchange="this.form.submit()">
                                        <option value="pending" <?= $project['status'] == 'pending' ? 'selected' : '' ?>>
                                            Beklemede
                                        </option>
                                        <option value="approved" <?= $project['status'] == 'approved' ? 'selected' : '' ?>>
                                            Onayla
                                        </option>
                                        <option value="rejected" <?= $project['status'] == 'rejected' ? 'selected' : '' ?>>
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
            <form id="deleteForm<?= $project['id'] ?>" method="POST" class="d-none">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $project['id'] ?>">
            </form>

            <!-- Proje Görüntüleme Modal -->
            <div class="modal fade" id="viewProjectModal<?= $project['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($project['title']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($project['image_path']) && file_exists('../uploads/projects/' . $project['image_path'])): ?>
                                <img src="<?= SITE_URL ?>/uploads/projects/<?= htmlspecialchars($project['image_path']) ?>" 
                                     class="img-fluid rounded mb-3" 
                                     alt="<?= htmlspecialchars($project['title']) ?>">
                            <?php endif; ?>
                            
                            <h6 class="fw-bold">Duyuru Detayları:</h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                            
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> 
                                        Eklenme: <?= date('d.m.Y H:i', strtotime($project['created_at'])) ?>
                                        <?php if ($project['first_name'] && $project['last_name']): ?>
                                            <br>
                                            <i class="fas fa-user"></i> 
                                            Ekleyen: <?= htmlspecialchars($project['first_name'] . ' ' . $project['last_name']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <?php if (!empty($project['url'])): ?>
                                        <a href="<?= htmlspecialchars($project['url']) ?>" 
                                           class="btn btn-primary btn-sm" 
                                           target="_blank">
                                            <i class="fas fa-external-link-alt"></i> Bağlantıya Git
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Yeni Proje Ekleme Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Duyuru Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="save-project.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Duyuru Başlığı</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duyuru Açıklaması</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bağlantı URL (Opsiyonel)</label>
                            <input type="url" name="url" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duyuru Görseli</label>
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
.project-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.project-card:hover {
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