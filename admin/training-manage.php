<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
checkAdmin();

$db = Database::getInstance();

// Birimleri getir
$units = $db->query("SELECT * FROM units ORDER BY name")->fetchAll();

// Eğitimleri getir
$trainings = $db->query(
    "SELECT t.*, u.name as unit_name,
            (SELECT COUNT(*) FROM training_applications WHERE training_id = t.id) as application_count
     FROM trainings t 
     JOIN units u ON t.unit_id = u.id 
     ORDER BY t.start_date DESC"
)->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Eğitim Yönetimi</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTrainingModal">
        <i class="bi bi-plus-lg"></i> Yeni Eğitim Ekle
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Eğitim Adı</th>
                        <th>Birim</th>
                        <th>Tarih</th>
                        <th>Son Başvuru Tarihi</th>
                        <th>Kapasite</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trainings as $training): ?>
                    <tr>
                        <td><?= htmlspecialchars($training['title']) ?></td>
                        <td><?= htmlspecialchars($training['unit_name']) ?></td>
                        <td>
                            <?= date('d.m.Y', strtotime($training['start_date'])) ?> - 
                            <?= date('d.m.Y', strtotime($training['end_date'])) ?>
                        </td>
                        <td>
                            
                            <?= date('d.m.Y', strtotime($training['deadline_date'])) ?>
                        </td>
                        <td><?= $training['capacity'] ?></td>
                        <td>
                            <span class="badge bg-<?= $training['is_active'] ? 'success' : 'danger' ?>">
                                <?= $training['is_active'] ? 'Aktif' : 'Pasif' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editTraining(<?= $training['id'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-info" onclick="viewRegistrations(<?= $training['id'] ?>)">
                                <i class="bi bi-people"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTraining(<?= $training['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Yeni Eğitim Ekleme Modal -->
<div class="modal fade" id="addTrainingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Eğitim Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTrainingForm" action="save-training.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Birim</label>
                        <select name="unit_id" class="form-select" required>
                            <option value="">Seçiniz</option>
                            <?php foreach ($units as $unit): ?>
                            <option value="<?= $unit['id'] ?>">
                                <?= htmlspecialchars($unit['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Eğitim Adı</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Başlangıç Tarihi</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kapasite</label>
                        <input type="number" name="capacity" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Son Başvuru Tarihi</label>
                        <input type="date" name="deadline_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" 
                                   id="isActive" value="1" checked>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" form="addTrainingForm" class="btn btn-primary">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
function editTraining(id) {
    window.location.href = 'training-edit.php?id=' + id;
}

function viewRegistrations(id) {
    window.location.href = 'training-applications.php?id=' + id;
}

function deleteTraining(id) {
    if (confirm('Bu eğitimi silmek istediğinizden emin misiniz?')) {
        window.location.href = 'training-delete.php?id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?> 