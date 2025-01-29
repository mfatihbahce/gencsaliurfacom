<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

$training_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Eğitim bilgilerini getir
$training = $db->query("
    SELECT t.*, u.name as unit_name, u.address, u.latitude, u.longitude, u.working_hours,
           (SELECT COUNT(*) FROM training_applications WHERE training_id = t.id) as registered
    FROM trainings t 
    JOIN units u ON t.unit_id = u.id 
    WHERE t.id = ?", 
    [$training_id]
)->fetch();

if (!$training) {
    setError('Eğitim bulunamadı.');
    redirect('trainings.php');
}

// Kullanıcının başvuru durumunu kontrol et
$application_status = null;
if (isLoggedIn()) {
    $application = $db->query("
        SELECT status 
        FROM training_applications 
        WHERE training_id = ? AND user_id = ?
    ", [$training_id, $_SESSION['user_id']])->fetch();
    
    if ($application) {
        $application_status = $application['status'];
    }
}

$page_title = $training['title'];
include 'includes/header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
:root {
    --primary-color: #6C63FF;
    --secondary-color: #2EC4B6;
    --accent-color: #FF6B6B;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --light-color: #F8F9FA;
    --dark-color: #343A40;
}

.training-detail-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 0 30px rgba(108, 99, 255, 0.1);
    overflow: hidden;
    background: #fff;
    margin-bottom: 2rem;
}

.training-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    padding: 3rem 2rem;
    color: white;
    position: relative;
}

.training-stats {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    backdrop-filter: blur(10px);
}

.stat-item {
    text-align: center;
    color: white;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.content-section {
    padding: 2rem;
}

.module-card {
    background: var(--light-color);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.module-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(108, 99, 255, 0.1);
}

/* Harita Stilleri */
#map {
    height: 300px;
    border-radius: 15px;
    margin-bottom: 1rem;
    border: 2px solid rgba(108, 99, 255, 0.1);
}

.leaflet-popup-content-wrapper {
    border-radius: 10px;
    padding: 0;
}

.leaflet-popup-content {
    margin: 0;
    padding: 1rem;
}

/* Buton Stilleri */
.btn-apply {
    padding: 1rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(108, 99, 255, 0.2);
}

.btn-location {
    padding: 0.8rem 1rem;
    font-weight: 500;
    border-width: 2px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    height: 100%;
}

.btn-location i {
    font-size: 1.1rem;
}

.btn-location:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.btn-outline-primary {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.btn-outline-primary:hover {
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    border-color: transparent;
    color: white;
}

.btn-outline-secondary {
    border-color: var(--dark-color);
    color: var(--dark-color);
}

.btn-outline-secondary:hover {
    background: linear-gradient(45deg, var(--dark-color), #6c757d);
    border-color: transparent;
    color: white;
}

.application-status {
    padding: 1rem;
    border-radius: 50px;
    text-align: center;
    font-weight: 500;
    margin-bottom: 1rem;
}

.status-pending {
    background: rgba(255, 193, 7, 0.1);
    color: var(--warning-color);
}

.status-approved {
    background: rgba(40, 167, 69, 0.1);
    color: var(--success-color);
}

.location-info i {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(108, 99, 255, 0.1);
    border-radius: 8px;
    margin-right: 1rem;
}
</style>
<div class="container py-5">
    <div class="row">
        <!-- Sol Kolon - Eğitim Detayları -->
        <div class="col-lg-8">
            <div class="training-detail-card">
                <!-- Eğitim Başlığı ve İstatistikler -->
                <div class="training-header">
                    <h1 class="display-6 fw-bold mb-4"><?= htmlspecialchars($training['title']) ?></h1>
                    
                    <div class="training-stats">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <?= date('d.m.Y', strtotime($training['start_date'])) ?>
                                    </div>
                                    <div>Başlangıç Tarihi</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <i class="bi bi-clock me-2"></i>
                                        <?= date('d.m.Y', strtotime($training['deadline_date'])) ?>
                                    </div>
                                    <div>Son Başvuru</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <i class="bi bi-people me-2"></i>
                                        <?= $training['registered'] ?>/<?= $training['capacity'] ?>
                                    </div>
                                    <div>Kontenjan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Eğitim İçeriği -->
                <div class="content-section">
                    <div class="description mb-5">
                        <?= nl2br(htmlspecialchars($training['description'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon - Başvuru ve Konum -->
        <div class="col-lg-4">
            <!-- Başvuru Kartı -->
            <div class="training-detail-card">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-clipboard-check me-2 text-primary"></i>
                        Başvuru Durumu
                    </h5>
                    
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?= ($training['registered']/$training['capacity'])*100 ?>%"></div>
                    </div>
                    
                    <p class="text-center mb-4">
                        <strong><?= $training['registered'] ?></strong> kişi başvurdu, 
                        <strong><?= $training['capacity'] - $training['registered'] ?></strong> kontenjan kaldı
                    </p>

                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary w-100 rounded-pill btn-apply">
                            <i class="bi bi-person-plus me-2"></i>Kayıt Ol ve Başvur
                        </a>
                    <?php elseif ($application_status === 'pending'): ?>
                        <div class="application-status status-pending">
                            <i class="bi bi-hourglass-split me-2"></i>Başvurunuz İnceleniyor
                        </div>
                    <?php elseif ($application_status === 'approved'): ?>
                        <div class="application-status status-approved">
                            <i class="bi bi-check-circle me-2"></i>Başvurunuz Onaylandı
                        </div>
                    <?php elseif (!isProfileComplete($_SESSION['user_id'])): ?>
                        <a href="admin/profile.php" class="btn btn-warning w-100 rounded-pill btn-apply">
                            <i class="bi bi-person-gear me-2"></i>Profili Tamamla
                        </a>
                    <?php elseif ($training['registered'] >= $training['capacity']): ?>
                        <div class="alert alert-secondary rounded-pill text-center">
                            <i class="bi bi-x-circle me-2"></i>Kontenjan Dolu
                        </div>
                    <?php else: ?>
                        <form action="training_actions.php" method="POST">
                            <input type="hidden" name="action" value="apply">
                            <input type="hidden" name="training_id" value="<?= $training['id'] ?>">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill btn-apply">
                                <i class="bi bi-send me-2"></i>Hemen Başvur
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Konum Kartı -->
            <div class="training-detail-card">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>
                        Eğitim Lokasyonu
                    </h5>

                    <!-- Harita -->
                    <div id="map"></div>

                    <!-- Lokasyon Bilgileri -->
                    <div class="location-info">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-building text-primary"></i>
                            <div>
                                <strong class="d-block"><?= htmlspecialchars($training['unit_name']) ?></strong>
                                <span class="text-muted"><?= htmlspecialchars($training['address']) ?></span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-clock text-primary"></i>
                            <div>
                                <strong class="d-block">Çalışma Saatleri</strong>
                                <span class="text-muted"><?= htmlspecialchars($training['working_hours']) ?></span>
                            </div>
                        </div>

                        <!-- Buton Grubu -->
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $training['latitude'] ?>,<?= $training['longitude'] ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-primary w-100 rounded-pill btn-location">
                                    <i class="bi bi-map me-2"></i>Yol Tarifi
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="unit-detail.php?id=<?= $training['unit_id'] ?>" 
                                   class="btn btn-outline-secondary w-100 rounded-pill btn-location">
                                    <i class="bi bi-building-up me-2"></i>Birim Detay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([<?= $training['latitude'] ?>, <?= $training['longitude'] ?>], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([<?= $training['latitude'] ?>, <?= $training['longitude'] ?>]).addTo(map);

    marker.bindPopup(`
        <div class="text-center">
            <strong class="d-block mb-1"><?= htmlspecialchars($training['unit_name']) ?></strong>
            <small class="text-muted"><?= htmlspecialchars($training['address']) ?></small>
        </div>
    `).openPopup();
});
</script>

<?php include 'includes/footer.php'; ?>