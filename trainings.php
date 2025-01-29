<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Aktif eğitimleri getir
$trainings = $db->query(
    "SELECT t.*, u.name as unit_name, u.address,
            (SELECT COUNT(*) FROM training_applications WHERE training_id = t.id) as registered
     FROM trainings t 
     JOIN units u ON t.unit_id = u.id 
     WHERE t.end_date >= CURDATE()
     ORDER BY t.start_date ASC"
)->fetchAll();

// Kullanıcının başvurduğu eğitimleri getir
$user_applications = [];
if (isLoggedIn()) {
    $user_applications = $db->query("
        SELECT training_id 
        FROM training_applications 
        WHERE user_id = ?
    ", [$_SESSION['user_id']])->fetchAll(PDO::FETCH_COLUMN);
}

$page_title = 'Eğitimler';
include 'includes/header.php';
?>

<style>
.training-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 0 30px rgba(108, 99, 255, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    background: #fff;
}

.training-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(108, 99, 255, 0.2);
}

.card-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 2;
}

.badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 50px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
}

.info-item i {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(108, 99, 255, 0.1);
    border-radius: 8px;
    color: var(--primary-color);
    font-size: 1rem;
}

.capacity-info {
    padding-top: 1rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.hover-scale {
    transition: transform 0.3s ease;
}

.hover-scale:hover {
    transform: translateY(-2px);
}

/* Özel Badge Renkleri */
.bg-purple-subtle { background: rgba(111, 66, 193, 0.1) !important; }
.text-purple { color: #6f42c1 !important; }
.bg-indigo-subtle { background: rgba(102, 16, 242, 0.1) !important; }
.text-indigo { color: #6610f2 !important; }

.section-header {
    margin-bottom: 3rem;
}

.section-header h6 {
    font-size: 0.9rem;
    letter-spacing: 2px;
}

.section-header h2 {
    margin-bottom: 1rem;
}

.divider {
    width: 50px;
    height: 3px;
    background: var(--primary-color);
    margin: 0 auto;
}

.empty-state {
    text-align: center;
    padding: 3rem;
}

.empty-state i {
    font-size: 3rem;
    color: var(--primary-color);
    opacity: 0.5;
    margin-bottom: 1rem;
}
</style>

<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <span class="badge bg-primary-subtle text-primary mb-2">
            <i class="bi bi-mortarboard me-2"></i>Eğitimlerimiz
        </span>
        <h2 class="display-5 fw-bold">Aktif Eğitim Programları</h2>
        <div class="divider"></div>
    </div>

    <?= showMessages() ?>

    <div class="row g-4">
        <?php if ($trainings): foreach ($trainings as $training): ?>
            <div class="col-md-6 col-lg-3" data-aos="fade-up">
                <div class="card h-100 training-card">
                    <!-- Eğitim Tipi Badge -->

                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">
                            <?= htmlspecialchars($training['title']) ?>
                        </h5>
                        
                        <div class="info-list">
                            <div class="info-item">
                                <i class="bi bi-building"></i>
                                <span><?= htmlspecialchars($training['unit_name']) ?></span>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-calendar-event"></i>
                                <span><?= date('d.m.Y', strtotime($training['start_date'])) ?></span>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-clock"></i>
                                <span>Son: <?= date('d.m.Y', strtotime($training['deadline_date'])) ?></span>
                            </div>
                        </div>

                        <!-- Kontenjan Progress -->
                        <div class="capacity-info">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Kontenjan Durumu</small>
                                <small class="fw-bold <?= ($training['registered']/$training['capacity'])*100 >= 80 ? 'text-danger' : 'text-primary' ?>">
                                    <?= $training['registered'] ?>/<?= $training['capacity'] ?>
                                </small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar <?= ($training['registered']/$training['capacity'])*100 >= 80 ? 'bg-danger' : 'bg-primary' ?>" 
                                     role="progressbar" 
                                     style="width: <?= ($training['registered']/$training['capacity'])*100 ?>%">
                                </div>
                            </div>
                        </div>
                    </div>

					                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="training-detail.php?id=<?= $training['id'] ?>" 
                           class="btn btn-primary w-100 rounded-pill btn-hover-elevate">
                            <i class="bi bi-info-circle me-2"></i>Detayları Gör
                        </a>
                    </div>
					
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-calendar2-x"></i>
                    <h4>Şu Anda Açık Eğitim Bulunmuyor</h4>
                    <p class="text-muted">Yeni eğitimlerimiz çok yakında sizlerle olacak!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>