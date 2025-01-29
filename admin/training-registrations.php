<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
checkAdmin();

$db = Database::getInstance();

$training_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Eğitim bilgilerini getir
$training = $db->query(
    "SELECT t.*, u.name as unit_name, u.id as unit_id 
     FROM trainings t 
     JOIN units u ON t.unit_id = u.id 
     WHERE t.id = ?",
    [$training_id]
)->fetch();

if (!$training) {
    setError('Eğitim bulunamadı.');
    redirect('training-manage.php');
}

// Kayıtları getir
$registrations = $db->query(
    "SELECT r.*, d.name as district_name 
     FROM training_registrations r 
     JOIN districts d ON r.district_id = d.id 
     WHERE r.training_id = ? 
     ORDER BY r.created_at DESC",
    [$training_id]
)->fetchAll();

$page_title = "Eğitim Kayıtları";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="bi bi-mortarboard me-2"></i><?= htmlspecialchars($training['title']) ?> - Kayıtlar
        </h2>
        <div>
            <a href="export-registrations.php?id=<?= $training_id ?>" class="btn btn-success me-2">
                <i class="bi bi-file-excel me-1"></i>Excel'e Aktar
            </a>
            <a href="training-manage.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Geri Dön
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Eğitim Detayları</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        <i class="bi bi-calendar me-1"></i>
                        <?= date('d.m.Y', strtotime($training['start_date'])) ?> - 
                        <?= date('d.m.Y', strtotime($training['end_date'])) ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if ($registrations): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>TC No</th>
                                <th>Doğum Tarihi</th>
                                <th>İlçe</th>
                                <th>Telefon</th>
                                <th>Kayıt Tarihi</th>
                                <th>Kayıt Türü</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($reg['first_name']) ?> 
                                    <?= htmlspecialchars($reg['last_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($reg['tc_no']) ?></td>
                                <td><?= date('d.m.Y', strtotime($reg['birth_date'])) ?></td>
                                <td><?= htmlspecialchars($reg['district_name']) ?></td>
                                <td><?= htmlspecialchars($reg['phone']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($reg['created_at'])) ?></td>
                                <td><?= htmlspecialchars($reg['registration_type']) ?></td>
                                <td>
                                    <?php
                                    // Öğrenci listesinde var mı kontrol et
                                    $is_approved = $db->query(
                                        "SELECT id FROM students WHERE tc_no = ? AND training_id = ?",
                                        [$reg['tc_no'], $training_id]
                                    )->fetch();
                                    ?>
                                    <?php if ($is_approved): ?>
                                        <span class="badge bg-success">Onaylandı</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Beklemede</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info" 
                                                onclick="viewDetails(<?= $reg['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (!$is_approved): ?>
                                        <button type="button" class="btn btn-success" 
                                                onclick="approveRegistration(<?= $reg['id'] ?>)">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-danger" 
                                                onclick="deleteRegistration(<?= $reg['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i>Henüz kayıt bulunmuyor.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Kayıt Detay Modal -->
<div class="modal fade" id="registrationDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kayıt Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="registrationDetailContent">
                <!-- AJAX ile doldurulacak -->
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('registrationDetailModal'));
    
    fetch('get-registration-details.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('registrationDetailContent').innerHTML = html;
            modal.show();
        });
}

function approveRegistration(id) {
    if (confirm('Bu kaydı onaylamak ve öğrenci listesine eklemek istediğinizden emin misiniz?')) {
        window.location.href = 'approve-registration.php?id=' + id + '&training_id=<?= $training_id ?>&unit_id=<?= $training['unit_id'] ?>';
    }
}

function deleteRegistration(id) {
    if (confirm('Bu kaydı silmek istediğinizden emin misiniz?')) {
        window.location.href = 'delete-registration.php?id=' + id + '&training_id=<?= $training_id ?>';
    }
}
</script>

<?php include 'includes/footer.php'; ?>