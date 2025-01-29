<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';
checkAdmin();

$db = Database::getInstance();

// Filtreleme parametreleri
$unit_id = filter_input(INPUT_GET, 'unit_id', FILTER_SANITIZE_NUMBER_INT);
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$nationality = isset($_GET['nationality']) ? htmlspecialchars($_GET['nationality']) : '';
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// Sorgu oluştur
$query = "SELECT s.*, 
          COALESCE(s.gender, '') as gender,
          COALESCE(s.status, 'inactive') as status,
          u.name as unit_name, 
          t.title as training_name, 
          COALESCE(d.name, '') as district_name 
          FROM students s 
          JOIN units u ON s.unit_id = u.id 
          JOIN trainings t ON s.training_id = t.id 
          LEFT JOIN districts d ON s.district_id = d.id 
          WHERE 1=1";
$params = [];

if ($unit_id) {
    $query .= " AND s.unit_id = ?";
    $params[] = $unit_id;
}

if ($status) {
    $query .= " AND s.status = ?";
    $params[] = $status;
}

if ($nationality) {
    if ($nationality === 'Diğer') {
        $query .= " AND s.nationality NOT IN ('Türk', 'Suriyeli')";
    } else {
        $query .= " AND s.nationality = ?";
        $params[] = $nationality;
    }
}

if ($search) {
    $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.tc_no LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY s.created_at DESC";

// Öğrencileri getir
$students = $db->query($query, $params)->fetchAll();

// Birimleri getir
$units = $db->query("SELECT id, name FROM units ORDER BY name")->fetchAll();

$page_title = "Öğrenci Listesi";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="bi bi-mortarboard me-2"></i>Öğrenci Listesi
        </h2>
        <div>
            <a href="add-student-registration.php" class="btn btn-primary me-2">
                <i class="bi bi-person-plus me-1"></i>Yeni Kayıt
            </a>
            <a href="export-students.php<?= $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
               class="btn btn-success">
                <i class="bi bi-file-excel me-1"></i>Excel'e Aktar
            </a>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Birim</label>
                    <select name="unit_id" class="form-select">
                        <option value="">Tümü</option>
                        <?php foreach ($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>" <?= $unit_id == $unit['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($unit['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="">Tümü</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Uyruk</label>
                    <select name="nationality" class="form-select">
                        <option value="">Tümü</option>
                        <option value="Türk" <?= $nationality === 'Türk' ? 'selected' : '' ?>>Türk</option>
                        <option value="Suriyeli" <?= $nationality === 'Suriyeli' ? 'selected' : '' ?>>Suriyeli</option>
                        <option value="Diğer" <?= $nationality === 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Arama</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Ad, Soyad veya TC No">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i>Filtrele
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Öğrenci Listesi -->
    <div class="card">
        <div class="card-body">
            <?php if ($students): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>TC No</th>
								<th>Cinsiyet</th>
                                <th>Uyruk</th>
                                <th>Birim</th>
                                <th>Eğitim</th>
                                <th>İlçe</th>
                                <th>Telefon</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
<tbody>
    <?php foreach ($students as $student): ?>
    <tr>
        <td>
            <?= htmlspecialchars($student['first_name'] ?? '') ?> 
            <?= htmlspecialchars($student['last_name'] ?? '') ?>
        </td>
        <td><?= htmlspecialchars($student['tc_no'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['gender'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['nationality'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['unit_name'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['training_name'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['district_name'] ?? '') ?></td>
        <td><?= htmlspecialchars($student['phone'] ?? '') ?></td>
        <td>
            <?php if (isset($student['status']) && $student['status'] == 'active'): ?>
                <span class="badge bg-success">Aktif</span>
            <?php else: ?>
                <span class="badge bg-danger">Pasif</span>
            <?php endif; ?>
        </td>
        <td><?= isset($student['created_at']) ? date('d.m.Y', strtotime($student['created_at'])) : '' ?></td>
        <td>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-info" 
                        onclick="viewStudent(<?= $student['id'] ?>)">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="btn btn-warning" 
                        onclick="toggleStatus(<?= $student['id'] ?>)">
                    <i class="bi bi-toggle-on"></i>
                </button>
                <button type="button" class="btn btn-danger" 
                        onclick="deleteStudent(<?= $student['id'] ?>)">
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
                    <i class="bi bi-info-circle me-1"></i>Kayıtlı öğrenci bulunmuyor.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Öğrenci Detay Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Öğrenci Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="studentModalContent">
                <!-- AJAX ile doldurulacak -->
            </div>
        </div>
    </div>
</div>

<script>
function viewStudent(id) {
    const modal = new bootstrap.Modal(document.getElementById('studentModal'));
    
    fetch('get-student-details.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('studentModalContent').innerHTML = html;
            modal.show();
        });
}

function toggleStatus(id) {
    if (confirm('Öğrencinin durumunu değiştirmek istediğinizden emin misiniz?')) {
        window.location.href = 'toggle-student-status.php?id=' + id;
    }
}

function deleteStudent(id) {
    if (confirm('Bu öğrenciyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
        window.location.href = 'delete-student.php?id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>