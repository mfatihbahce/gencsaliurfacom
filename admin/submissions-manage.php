<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

// Admin kontrolü
requireAdmin();

// Database instance'ı al
$db = Database::getInstance();

$type = $_GET['type'] ?? 'announcement';
$allowed_types = ['announcement', 'event', 'place'];
if (!in_array($type, $allowed_types)) {
    $type = 'announcement';
}

// Başlık ve tablo ayarları
$titles = [
    'announcement' => 'Duyuru Bildirimleri',
    'event' => 'Etkinlik Bildirimleri',
    'place' => 'Gezilecek Yer Bildirimleri'
];

$tables = [
    'announcement' => 'projects',
    'event' => 'news',
    'place' => 'places'
];

// Bildirim onaylama/reddetme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    try {
        // Bildirimi güncelle
        $db->query(
            "UPDATE {$tables[$type]} SET status = ? WHERE id = ?",
            [$status, $id]
        );

        // Submission history güncelle
        $db->query(
            "UPDATE submission_history 
             SET status = ?, updated_at = NOW() 
             WHERE submission_type = ? AND submission_id = ?",
            [$status, $type, $id]
        );

        // Onaylanırsa puan ekle
        if ($action === 'approve') {
            // Kullanıcıyı bul
            $submission = $db->query(
                "SELECT user_id FROM {$tables[$type]} WHERE id = ?",
                [$id]
            )->fetch();

            if ($submission) {
                // Kullanıcının mevcut puanını kontrol et
                $userPoints = $db->query(
                    "SELECT id, points FROM user_points WHERE user_id = ?",
                    [$submission['user_id']]
                )->fetch();

                if ($userPoints) {
                    // Mevcut puanı güncelle
                    $db->query(
                        "UPDATE user_points SET points = points + 50 WHERE id = ?",
                        [$userPoints['id']]
                    );
                } else {
                    // Yeni puan kaydı oluştur
                    $db->query(
                        "INSERT INTO user_points (user_id, points) VALUES (?, 50)",
                        [$submission['user_id']]
                    );
                }
            }
        }

        setSuccess($action === 'approve' ? 'Bildirim onaylandı.' : 'Bildirim reddedildi.');
    } catch (Exception $e) {
        setError('Bir hata oluştu: ' . $e->getMessage());
    }
    
    // Sayfayı yenile
    header("Location: submissions-manage.php?type=" . $type);
    exit;
}

// Bildirimleri getir
$submissions = $db->query(
    "SELECT s.*, u.first_name, u.last_name 
     FROM {$tables[$type]} s 
     LEFT JOIN users u ON s.user_id = u.id 
     WHERE s.status = 'pending' 
     ORDER BY s.created_at DESC"
)->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> <?= $titles[$type] ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Bildirim türü seçimi -->
                    <div class="mb-4">
                        <div class="btn-group" role="group">
                            <a href="?type=announcement" class="btn btn-<?= $type === 'announcement' ? 'primary' : 'outline-primary' ?>">
                                <i class="fas fa-bullhorn"></i> Duyurular
                            </a>
                            <a href="?type=event" class="btn btn-<?= $type === 'event' ? 'primary' : 'outline-primary' ?>">
                                <i class="fas fa-calendar-alt"></i> Etkinlikler
                            </a>
                            <a href="?type=place" class="btn btn-<?= $type === 'place' ? 'primary' : 'outline-primary' ?>">
                                <i class="fas fa-map-marker-alt"></i> Gezilecek Yerler
                            </a>
                        </div>
                    </div>

                    <?= showMessages() ?>

                    <!-- Bildirimler tablosu -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Başlık</th>
                                    <th>Gönderen</th>
                                    <th>Tarih</th>
                                    <th>Detay</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($submissions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Bekleyen bildirim bulunmuyor.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($submissions as $item): ?>
                                        <tr>
                                            <td><?= $item['id'] ?></td>
                                            <td><?= htmlspecialchars($item['title']) ?></td>
                                            <td><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></td>
                                            <td><?= formatDateTime($item['created_at']) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal<?= $item['id'] ?>">
                                                    <i class="fas fa-eye"></i> Detay
                                                </button>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <button type="submit" name="action" value="approve" 
                                                            class="btn btn-sm btn-success"
                                                            onclick="return confirm('Bu bildirimi onaylamak istediğinize emin misiniz?')">
                                                        <i class="fas fa-check"></i> Onayla
                                                    </button>
                                                    <button type="submit" name="action" value="reject" 
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Bu bildirimi reddetmek istediğinize emin misiniz?')">
                                                        <i class="fas fa-times"></i> Reddet
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Detay Modal -->
                                        <div class="modal fade" id="detailModal<?= $item['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Bildirim Detayı</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h6>Başlık:</h6>
                                                        <p><?= htmlspecialchars($item['title']) ?></p>
                                                        
                                                        <h6>Açıklama:</h6>
                                                        <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                                        
                                                        <?php if (isset($item['address'])): ?>
                                                            <h6>Adres:</h6>
                                                            <p><?= nl2br(htmlspecialchars($item['address'])) ?></p>
                                                        <?php endif; ?>

                                                        <?php if (isset($item['event_date'])): ?>
                                                            <h6>Etkinlik Tarihi:</h6>
                                                            <p><?= formatDateTime($item['event_date']) ?></p>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($item['image_path'] && $item['image_path'] !== 'default.jpg'): ?>
                                                            <h6>Görsel:</h6>
                                                            <img src="../uploads/points/<?= $type ?>s/<?= htmlspecialchars($item['image_path']) ?>" 
                                                                 class="img-fluid rounded" 
                                                                 alt="Bildirim görseli">
                                                        <?php endif; ?>
                                                        
                                                        <hr>
                                                        <small class="text-muted">
                                                            Gönderen: <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?><br>
                                                            Tarih: <?= formatDateTime($item['created_at']) ?>
                                                        </small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 