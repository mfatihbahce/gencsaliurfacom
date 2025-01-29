<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();

// Durum güncelleme işlemi
if (isset($_POST['update_status'])) {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    try {
        $db->query(
            "UPDATE training_requests SET status = ?, notes = ? WHERE id = ?",
            [$status, $notes, $request_id]
        );
        setSuccess("Talep durumu güncellendi.");
    } catch (Exception $e) {
        setError("Güncelleme başarısız oldu: " . $e->getMessage());
    }
    header("Location: training-requests.php" . (isset($_GET['status']) ? '?status=' . $_GET['status'] : ''));
    exit();
}

// Kategorileri getir
$categories = $db->query("SELECT DISTINCT category FROM training_types WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Filtreleme
$status_filter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$category_filter = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$where_clause = "";
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'approved', 'rejected'])) {
    $where_clause = " WHERE r.status = ?";
    $params[] = $status_filter;
}

if ($category_filter) {
    $where_clause .= $where_clause ? " AND t.category = ?" : " WHERE t.category = ?";
    $params[] = $category_filter;
}

// Eğitim taleplerini getir
$requests = $db->query(
    "SELECT r.*, t.name as training_name, t.category 
     FROM training_requests r 
     JOIN training_types t ON r.training_type_id = t.id"
     . $where_clause .
     " ORDER BY r.created_at DESC",
    $params
)->fetchAll();

// Talep sayılarını getir
$counts = $db->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
     FROM training_requests"
)->fetch();

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Eğitim Talepleri</h4>
                <a href="training-types.php" class="btn btn-primary">Eğitim Türlerini Yönet</a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filtre butonları -->
            <div class="mb-4">
                <!-- Durum Filtreleri -->
                <div class="btn-group mb-2">
                    <a href="training-requests.php" 
                       class="btn btn-outline-secondary <?= !$status_filter ? 'active' : '' ?>">
                        Tümü (<?= $counts['total'] ?>)
                    </a>
                    <a href="?status=pending<?= $category_filter ? '&category='.$category_filter : '' ?>" 
                       class="btn btn-outline-warning <?= $status_filter === 'pending' ? 'active' : '' ?>">
                        Bekleyenler (<?= $counts['pending'] ?>)
                    </a>
                    <a href="?status=approved<?= $category_filter ? '&category='.$category_filter : '' ?>" 
                       class="btn btn-outline-success <?= $status_filter === 'approved' ? 'active' : '' ?>">
                        Onaylananlar (<?= $counts['approved'] ?>)
                    </a>
                    <a href="?status=rejected<?= $category_filter ? '&category='.$category_filter : '' ?>" 
                       class="btn btn-outline-danger <?= $status_filter === 'rejected' ? 'active' : '' ?>">
                        Reddedilenler (<?= $counts['rejected'] ?>)
                    </a>
                </div>

                <!-- Kategori Filtreleri -->

            </div>

            <?php if (empty($requests)): ?>
                <div class="alert alert-info">
                    <?php 
                    if ($status_filter || $category_filter) {
                        $message = "Seçilen ";
                        if ($category_filter) {
                            $message .= "kategoride ";
                        }
                        if ($status_filter) {
                            $status_text = [
                                'pending' => 'bekleyen',
                                'approved' => 'onaylanmış',
                                'rejected' => 'reddedilmiş'
                            ][$status_filter];
                            $message .= $status_text;
                        }
                        echo $message . " eğitim talebi bulunmuyor.";
                    } else {
                        echo "Henüz eğitim talebi bulunmuyor.";
                    }
                    ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Okul Adı</th>
                                <th>Kategori</th>
                                <th>Eğitim</th>
                                <th>Talep Tarihi</th>
                                <th>Öğrenci Sayısı</th>
                                <th>Yetkili Kişi</th>
                                <th>Telefon</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= $request['id'] ?></td>
                                    <td><?= htmlspecialchars($request['school_name']) ?></td>
                                    <td><?= htmlspecialchars($request['category']) ?></td>
                                    <td><?= htmlspecialchars($request['training_name']) ?></td>
                                    <td><?= date('d.m.Y', strtotime($request['requested_date'])) ?></td>
                                    <td><?= number_format($request['student_count']) ?></td>
                                    <td><?= htmlspecialchars($request['contact_person']) ?></td>
                                    <td><?= htmlspecialchars($request['phone']) ?></td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        $status_text = [
                                            'pending' => 'Beklemede',
                                            'approved' => 'Onaylandı',
                                            'rejected' => 'Reddedildi'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $status_class[$request['status']] ?>">
                                            <?= $status_text[$request['status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#statusModal<?= $request['id'] ?>">
                                            Düzenle
                                        </button>
                                    </td>
                                </tr>

                                <!-- Durum Güncelleme Modal -->
                                <div class="modal fade" id="statusModal<?= $request['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Talep Durumu Güncelle #<?= $request['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Kategori</label>
                                                        <input type="text" class="form-control" 
                                                               value="<?= htmlspecialchars($request['category']) ?>" readonly>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Eğitim</label>
                                                        <input type="text" class="form-control" 
                                                               value="<?= htmlspecialchars($request['training_name']) ?>" readonly>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Durum</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="pending" <?= $request['status'] == 'pending' ? 'selected' : '' ?>>
                                                                Beklemede
                                                            </option>
                                                            <option value="approved" <?= $request['status'] == 'approved' ? 'selected' : '' ?>>
                                                                Onayla
                                                            </option>
                                                            <option value="rejected" <?= $request['status'] == 'rejected' ? 'selected' : '' ?>>
                                                                Reddet
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Notlar</label>
                                                        <textarea name="notes" class="form-control" rows="3" 
                                                                placeholder="Talep ile ilgili notlarınızı buraya yazabilirsiniz..."
                                                        ><?= htmlspecialchars($request['notes'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Güncelle</button>
                                                </div>
                                            </form>
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

<style>
.btn-group {
    flex-wrap: wrap;
    gap: 0.25rem;
}

.table th {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        flex: 1;
        white-space: nowrap;
    }
}
</style>

<?php include 'includes/footer.php'; ?>