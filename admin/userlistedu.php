<?php
// Hata raporlamayı açalım
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Session kontrolü - eğer session başlamamışsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database bağlantısı
$db = Database::getInstance();

// Kullanıcı kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/login');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Kullanıcı bilgilerini al
    $user = $db->query("
        SELECT id, username, email, role 
        FROM users 
        WHERE id = ?
    ", [$user_id])->fetch();

    // İstatistikleri al
    $stats = $db->query("
        SELECT 
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count
        FROM training_applications
        WHERE user_id = ?
    ", [$user_id])->fetch();

    // Devam eden eğitimleri al
    $ongoing_trainings = $db->query("
        SELECT 
            t.id,
            t.title,
            t.start_date,
            t.end_date,
            u.name as unit_name,
            ta.status
        FROM trainings t 
        LEFT JOIN units u ON t.unit_id = u.id 
        LEFT JOIN training_applications ta ON t.id = ta.training_id AND ta.user_id = ?
        WHERE t.start_date <= CURRENT_DATE 
        AND t.end_date >= CURRENT_DATE
        AND t.is_active = 1
        ORDER BY t.end_date ASC
        LIMIT 5
    ", [$user_id])->fetchAll();

    // Duyuruları al
    $announcements = $db->query("
        SELECT 
            id,
            title,
            LEFT(description, 200) as content,
            image_path,
            created_at
        FROM projects 
        WHERE status = 'approved'
        ORDER BY created_at DESC
        LIMIT 4
    ")->fetchAll();

    // Başvuruları al
    $applications = $db->query("
        SELECT 
            ta.status, 
            t.title, 
            t.start_date, 
            t.end_date, 
            u.name as unit_name,
            ta.created_at as application_date
        FROM training_applications ta
        JOIN trainings t ON ta.training_id = t.id
        LEFT JOIN units u ON t.unit_id = u.id
        WHERE ta.user_id = ?
        ORDER BY ta.created_at DESC
        LIMIT 10
    ", [$user_id])->fetchAll();

    // Kullanıcının bildirimlerini al
    $submissions = $db->query("
        SELECT 
            sh.id,
            sh.submission_type,
            sh.status,
            sh.created_at,
            sh.points,
            CASE 
                WHEN sh.submission_type = 'announcement' THEN p.title
                WHEN sh.submission_type = 'event' THEN n.title
                WHEN sh.submission_type = 'place' THEN pl.title
            END as title,
            CASE 
                WHEN sh.submission_type = 'announcement' THEN 'Duyuru'
                WHEN sh.submission_type = 'event' THEN 'Etkinlik'
                WHEN sh.submission_type = 'place' THEN 'Gezilecek Yer'
            END as type_text
        FROM submission_history sh
        LEFT JOIN projects p ON sh.submission_type = 'announcement' AND sh.submission_id = p.id
        LEFT JOIN news n ON sh.submission_type = 'event' AND sh.submission_id = n.id
        LEFT JOIN places pl ON sh.submission_type = 'place' AND sh.submission_id = pl.id
        WHERE sh.user_id = ?
        ORDER BY sh.created_at DESC
        LIMIT 5
    ", [$user_id])->fetchAll();

} catch (Exception $e) {
    error_log('Database Error: ' . $e->getMessage());
    die("Veritabanı hatası oluştu. Lütfen daha sonra tekrar deneyiniz.");

    // Kullanıcının toplam puanını ve sıralamasını al
    $user_rank = $db->query("
        SELECT 
            up.points,
            (SELECT COUNT(*) + 1 
             FROM user_points up2 
             WHERE up2.points > up.points) as rank,
            (SELECT COUNT(*) FROM user_points) as total_users
        FROM user_points up
        WHERE up.user_id = ?
    ", [$user_id])->fetch();

} catch (Exception $e) {
    error_log('Database Error: ' . $e->getMessage());
    die("Veritabanı hatası oluştu. Lütfen daha sonra tekrar deneyiniz.");
}

$page_title = "Eğitim Portalı Dashboard";
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Üst Bilgi Kartı -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 text-dark">Hoş Geldiniz, <?= htmlspecialchars($user['username'] ?? 'Kullanıcı') ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-uppercase mb-1 text-warning fw-bold">Bekleyen Başvurular</div>
                            <div class="h2 mb-0 fw-bold"><?= $stats['pending_count'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-uppercase mb-1 text-success fw-bold">Onaylanan Başvurular</div>
                            <div class="h2 mb-0 fw-bold"><?= $stats['approved_count'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-uppercase mb-1 text-danger fw-bold">Reddedilen Başvurular</div>
                            <div class="h2 mb-0 fw-bold"><?= $stats['rejected_count'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Puan Kartı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-uppercase mb-1 text-info fw-bold">Yarışma Puanı</div>
                            <div class="h2 mb-0 fw-bold"><?= number_format($user_rank['points'] ?? 0) ?></div>
                            <small class="text-muted">
                                Sıralama: <?= number_format($user_rank['rank'] ?? 0) ?>/<?= number_format($user_rank['total_users'] ?? 0) ?>
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bildirimler ve Duyurular -->
    <div class="row mb-4">
        <!-- Son Bildirimler -->
<!-- Son Bildirimler Kartı -->
<div class="col-xl-6 col-md-12 mb-4">
    <div class="card shadow h-100">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Son Bildirimlerim</h6>
            <a href="<?= SITE_URL ?>/submissions" class="btn btn-light btn-sm">
                Tümünü Gör
                <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
            <?php if (!empty($submissions)): ?>
                <div class="list-group">
                    <?php foreach ($submissions as $submission): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <?= htmlspecialchars($submission['title'] ?? 'İsimsiz Bildirim') ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($submission['type_text']) ?> • 
                                        <?= date('d.m.Y H:i', strtotime($submission['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?= 
                                        $submission['status'] === 'approved' ? 'success' : 
                                        ($submission['status'] === 'pending' ? 'warning' : 'danger') 
                                    ?> me-2">
                                        <?= 
                                            $submission['status'] === 'approved' ? 'Onaylandı' : 
                                            ($submission['status'] === 'pending' ? 'Beklemede' : 'Reddedildi')
                                        ?>
                                    </span>
                                    <?php if($submission['status'] === 'approved' && $submission['points'] > 0): ?>
                                        <span class="badge bg-info">
                                            +<?= number_format($submission['points']) ?> Puan
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Henüz bildirim bulunmamaktadır.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

        <!-- Duyurular -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Duyurular</h6>
                    <a href="<?= SITE_URL ?>/projects" class="btn btn-light btn-sm">
                        Tümünü Gör
                        <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($announcements)): ?>
                        <div class="list-group">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 text-truncate" title="<?= htmlspecialchars($announcement['title']) ?>">
                                            <?= htmlspecialchars($announcement['title']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= date('d.m.Y', strtotime($announcement['created_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 text-truncate">
                                        <?= htmlspecialchars($announcement['content']) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Henüz duyuru bulunmamaktadır.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tüm Başvurular Tablosu -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Tüm Eğitim Başvurularım</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="applicationsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Eğitim Adı</th>
                                    <th>Birim</th>
                                    <th>Başvuru Tarihi</th>
                                    <th>Eğitim Tarihleri</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['title']) ?></td>
                                        <td><?= htmlspecialchars($app['unit_name']) ?></td>
                                        <td><?= date('d.m.Y', strtotime($app['application_date'])) ?></td>
                                        <td>
                                            <?= date('d.m.Y', strtotime($app['start_date'])) ?> - 
                                            <?= date('d.m.Y', strtotime($app['end_date'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $app['status'] === 'approved' ? 'success' : 
                                                ($app['status'] === 'pending' ? 'warning' : 'danger') 
                                            ?>">
                                                <?= 
                                                    $app['status'] === 'approved' ? 'Onaylandı' : 
                                                    ($app['status'] === 'pending' ? 'Beklemede' : 'Reddedildi')
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>