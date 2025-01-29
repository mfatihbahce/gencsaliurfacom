<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Database instance'ı al
$db = Database::getInstance();

// En yüksek puanlı 10 kullanıcıyı getir
$topUsers = $db->query(
    "SELECT u.username, u.first_name, u.last_name, COALESCE(up.points, 0) as points,
            (SELECT COUNT(*) FROM submission_history sh 
             WHERE sh.user_id = u.id AND sh.status = 'approved') as total_submissions
    FROM users u 
    LEFT JOIN user_points up ON u.id = up.user_id 
    ORDER BY up.points DESC 
    LIMIT 10"
)->fetchAll();

// Mevcut kullanıcının puanını ve sıralamasını getir
$userPoints = 0;
$userRank = 0;
$totalSubmissions = 0;
if (isset($_SESSION['user_id'])) {
    $result = $db->query(
        "SELECT COALESCE(points, 0) as points 
        FROM user_points 
        WHERE user_id = ?",
        [$_SESSION['user_id']]
    )->fetch();
    
    $userPoints = $result ? $result['points'] : 0;

    // Kullanıcının sıralamasını bul
    $rankResult = $db->query(
        "SELECT COUNT(*) + 1 as rank 
        FROM user_points 
        WHERE points > ?",
        [$userPoints]
    )->fetch();
    
    $userRank = $rankResult['rank'];

    // Kullanıcının toplam onaylı paylaşım sayısını getir
    $submissionResult = $db->query(
        "SELECT COUNT(*) as total 
        FROM submission_history 
        WHERE user_id = ? AND status = 'approved'",
        [$_SESSION['user_id']]
    )->fetch();
    
    $totalSubmissions = $submissionResult['total'];
}

include 'includes/header.php';
?>

<!-- Puan Sistemi için özel CSS -->
<style>
.points-system-alert {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid #e9ecef;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.points-system-alert .icon-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.points-system-alert .icon-wrapper i {
    color: white;
}

@keyframes gentleFloat {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
    100% { transform: translateY(0px); }
}

.points-system-alert .floating-icon {
    animation: gentleFloat 3s ease-in-out infinite;
}

.points-card {
    transition: all 0.3s ease;
    border: none !important;
}

.points-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.points-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.points-pattern {
    background-image: radial-gradient(#0001 2px, transparent 2px);
    background-size: 20px 20px;
}

.rank-card {
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    color: white;
}

.stats-card {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}
</style>

<div class="container py-5">
    <div class="row">
        <!-- Sol Kolon: Puan Sıralaması -->
        <div class="col-md-4">
            <div class="card points-card mb-4">
                <div class="card-header points-header">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i> 
                        Liderlik Tablosu
                        <span class="badge bg-warning text-dark float-end">Bu Ay</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($topUsers as $index => $user): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <?php if ($index < 3): ?>
                                        <div class="me-2">
                                            <?php if ($index === 0): ?>
                                                <i class="fas fa-crown text-warning"></i>
                                            <?php elseif ($index === 1): ?>
                                                <i class="fas fa-medal text-secondary"></i>
                                            <?php else: ?>
                                                <i class="fas fa-award text-bronze"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill me-2"><?= $index + 1 ?></span>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                        <small class="text-muted">
                                            @<?= htmlspecialchars($user['username']) ?>
                                            <span class="ms-2">
                                                <i class="fas fa-share-alt"></i> <?= $user['total_submissions'] ?>
                                            </span>
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    <?= number_format($user['points']) ?> <i class="fas fa-star"></i>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="card points-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Senin Durumun</h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 rounded rank-card text-center">
                                    <h6 class="mb-2">Sıralama</h6>
                                    <h3 class="mb-0">#<?= number_format($userRank) ?></h3>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded stats-card text-center">
                                    <h6 class="mb-2">Toplam Puan</h6>
                                    <h3 class="mb-0"><?= number_format($userPoints) ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Seviye İlerlemesi</span>
                                <span class="badge bg-primary">
                                    <?php
                                    $nextLevel = ceil($userPoints / 1000) * 1000;
                                    echo number_format($userPoints) . ' / ' . number_format($nextLevel);
                                    ?>
                                </span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <?php $progress = ($userPoints % 1000) / 1000 * 100; ?>
                                <div class="progress-bar bg-primary" 
                                     role="progressbar" 
                                     style="width: <?= $progress ?>%"
                                     aria-valuenow="<?= $progress ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                Sonraki seviyeye <?= number_format($nextLevel - $userPoints) ?> puan kaldı
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-md-8">
            <!-- Puan Kazanma Kartları -->
            <div class="card points-card">
                <div class="card-header points-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star-half-alt me-2"></i>
                        Puan Kazan
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Açıklama Alanı -->
                    <div class="points-system-alert p-4">
                        <div class="d-flex align-items-start">

                            <div>
                                <h6 class="fw-bold mb-2">Nasıl Katkıda Bulunabilirsin?</h6>
                                <p class="mb-0">
                                    Genç Şanlıurfa platformuna katkıda bulunarak hem topluluğumuzu geliştirebilir hem de puan kazanabilirsin! 
                                    Şehrimizdeki önemli duyuruları, etkinlikleri ve gezilecek yerleri paylaşarak puanlar kazanabilir, 
                                    sıralamada yükselebilir ve çeşitli ödüllerin sahibi olabilirsin. Her onaylanan paylaşımın için 
                                    50 puan kazanırsın. Haydi sen de katıl ve şehrimizi birlikte geliştirelim! 🌟
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Duyuru Kartı -->
                        <div class="col-md-4">
                            <div class="card points-card h-100">
                                <div class="card-body text-center points-pattern">
                                    <div class="display-4 text-primary mb-3">
                                        <i class="fas fa-bullhorn floating-icon"></i>
                                    </div>
                                    <h5>Duyuru Paylaş</h5>
                                    <p class="text-muted small">
                                        Önemli haberleri paylaş, topluma katkıda bulun
                                    </p>
                                    <a href="submit-announcement.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-plus-circle"></i> Ekle
                                    </a>
                                </div>
                                <div class="card-footer bg-primary text-white text-center">
                                    50 <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Etkinlik Kartı -->
                        <div class="col-md-4">
                            <div class="card points-card h-100">
                                <div class="card-body text-center points-pattern">
                                    <div class="display-4 text-success mb-3">
                                        <i class="fas fa-calendar-alt floating-icon"></i>
                                    </div>
                                    <h5>Etkinlik Ekle</h5>
                                    <p class="text-muted small">
                                        Etkinlikleri paylaş, herkesi haberdar et
                                    </p>
                                    <a href="submit-event.php" class="btn btn-outline-success w-100">
                                        <i class="fas fa-plus-circle"></i> Ekle
                                    </a>
                                </div>
                                <div class="card-footer bg-success text-white text-center">
                                    50 <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Mekan Kartı -->
                        <div class="col-md-4">
                            <div class="card points-card h-100">
                                <div class="card-body text-center points-pattern">
                                    <div class="display-4 text-info mb-3">
                                        <i class="fas fa-map-marker-alt floating-icon"></i>
                                    </div>
                                    <h5>Mekan Öner</h5>
                                    <p class="text-muted small">
                                        Güzel mekanları paylaş, keşfetmeye yardım et
                                    </p>
                                    <a href="submit-place.php" class="btn btn-outline-info w-100">
                                        <i class="fas fa-plus-circle"></i> Ekle
                                    </a>
                                </div>
                                <div class="card-footer bg-info text-white text-center">
                                    50 <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>