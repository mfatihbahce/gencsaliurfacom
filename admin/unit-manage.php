<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Birimleri getir
$units = $db->query("SELECT * FROM units ORDER BY name")->fetchAll();

$page_title = "Birim Yönetimi";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Birim Yönetimi</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
        <i class="bi bi-plus-lg"></i> Yeni Birim Ekle
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Birim Adı</th>
                        <th>Adres</th>
                        <th>Çalışma Saatleri</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $unit): ?>
                    <tr>
                        <td><?= htmlspecialchars($unit['name']) ?></td>
                        <td><?= htmlspecialchars($unit['address']) ?></td>
                        <td><?= htmlspecialchars($unit['working_hours']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editUnit(<?= $unit['id'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-info" onclick="manageGallery(<?= $unit['id'] ?>)">
                                <i class="bi bi-images"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUnit(<?= $unit['id'] ?>)">
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

<!-- Yeni Birim Ekleme Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Birim Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUnitForm" action="save-unit.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Birim Adı</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Çalışma Saatleri</label>
                        <input type="text" name="working_hours" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enlem</label>
                            <input type="number" name="latitude" class="form-control" 
                                   step="any" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Boylam</label>
                            <input type="number" name="longitude" class="form-control" 
                                   step="any" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" form="addUnitForm" class="btn btn-primary">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
function editUnit(id) {
    window.location.href = 'unit-edit.php?id=' + id;
}

function manageGallery(id) {
    window.location.href = 'unit-gallery.php?id=' + id;
}

function deleteUnit(id) {
    if (confirm('Bu birimi silmek istediğinizden emin misiniz?')) {
        window.location.href = 'unit-delete.php?id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?> 