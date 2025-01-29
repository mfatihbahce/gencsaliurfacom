<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

$unit_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Birim bilgilerini getir
$unit = $db->query("SELECT * FROM units WHERE id = ?", [$unit_id])->fetch();

if (!$unit) {
    setError('Birim bulunamadı.');
    redirect('units.php');
}

// Birim görsellerini getir
$gallery = $db->query("SELECT * FROM unit_gallery WHERE unit_id = ?", [$unit_id])->fetchAll();

// Aktif eğitimleri getir
$trainings = $db->query(
    "SELECT t.*, 
            (SELECT COUNT(*) FROM training_applications WHERE training_id = t.id) as registered
     FROM trainings t 
     WHERE t.unit_id = ? AND t.end_date >= CURDATE()
     ORDER BY t.start_date ASC",
    [$unit_id]
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

$page_title = $unit['name'];
include 'includes/header.php';
?>

<!-- Leaflet CSS ve JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.unit-detail-card {
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.gallery-img {
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.gallery-img:hover {
    transform: scale(1.05);
}

.training-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.training-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}

.progress {
    background-color: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(45deg, var(--bs-primary), #4e73df);
    border-radius: 10px;
}

.btn-sm.rounded-pill {
    padding: 0.4rem 1rem;
}

.hover-scale {
    transition: transform 0.3s ease;
}

.hover-scale:hover {
    transform: scale(1.05);
}

/* Harita kontrolleri için stil */
.leaflet-control-zoom a {
    background-color: rgba(255, 255, 255, 0.8) !important;
    color: #333 !important;
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
    padding: 5px;
}

.leaflet-popup-content {
    margin: 10px;
    text-align: center;
}
</style>

<div class="container my-4">
    <div class="row">
        <!-- Birim Detayları -->
        <div class="col-md-8 mb-4">
            <div class="card unit-detail-card">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <i class="bi bi-building me-2"></i><?= htmlspecialchars($unit['name']) ?>
                    </h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="bi bi-geo-alt me-2"></i>Adres</h5>
                            <p><?= nl2br(htmlspecialchars($unit['address'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="bi bi-clock me-2"></i>Çalışma Saatleri</h5>
                            <p><?= htmlspecialchars($unit['working_hours']) ?></p>
                        </div>
                    </div>

                    <?php if ($unit['description']): ?>
                    <div class="mb-4">
                        <h5><i class="bi bi-info-circle me-2"></i>Açıklama</h5>
                        <p><?= nl2br(htmlspecialchars($unit['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Harita -->
                    <div class="mb-4">
                        <h5><i class="bi bi-map me-2"></i>Konum</h5>
                        <div id="map" style="height: 400px; border-radius: 8px;"></div>
                    </div>

                    <!-- Görseller -->
                    <?php if ($gallery): ?>
                    <div class="mb-4">
                        <h5><i class="bi bi-images me-2"></i>Görseller</h5>
                        <div class="row g-3">
                            <?php foreach ($gallery as $image): ?>
                            <div class="col-md-4">
                                <img src="<?= SITE_URL ?>/uploads/units/<?= $image['image_path'] ?>" 
                                     class="img-fluid gallery-img" 
                                     alt="<?= htmlspecialchars($unit['name']) ?>"
                                     onclick="showImage(this.src)">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Aktif Eğitimler -->
        <div class="col-md-4">
            <div class="card unit-detail-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-mortarboard me-2"></i>Aktif Eğitimler
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($trainings): foreach ($trainings as $training): ?>
                        <div class="card training-card">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($training['title']) ?></h6>
                                
                                <div class="small mb-3">
                                    <div class="mb-2">
                                        <i class="bi bi-calendar3 me-2 text-primary"></i>
                                        <?= date('d.m.Y', strtotime($training['start_date'])) ?> - 
                                        <?= date('d.m.Y', strtotime($training['end_date'])) ?>
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-clock me-2 text-primary"></i>
                                        Son Başvuru: <?= date('d.m.Y', strtotime($training['deadline_date'])) ?>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people me-2 text-primary"></i>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= ($training['registered']/$training['capacity'])*100 ?>%"></div>
                                        </div>
                                        <span class="ms-2"><?= $training['registered'] ?>/<?= $training['capacity'] ?></span>
                                    </div>
                                </div>

                                <?php if (!isLoggedIn()): ?>
                                    <a href="register.php" class="btn btn-primary btn-sm w-100 rounded-pill">
                                        <i class="bi bi-person-plus me-1"></i>Kayıt Ol ve Başvur
                                    </a>
                                <?php elseif (in_array($training['id'], $user_applications)): ?>
                                    <button class="btn btn-success btn-sm w-100 rounded-pill" disabled>
                                        <i class="bi bi-check-circle me-1"></i>Başvuru Yaptınız
                                    </button>
                                <?php elseif (!isProfileComplete($_SESSION['user_id'])): ?>
                                    <a href="admin/profile.php" class="btn btn-warning btn-sm w-100 rounded-pill">
                                        <i class="bi bi-person-gear me-1"></i>Profili Tamamla
                                    </a>
                                <?php elseif ($training['registered'] >= $training['capacity']): ?>
                                    <button class="btn btn-secondary btn-sm w-100 rounded-pill" disabled>
                                        <i class="bi bi-x-circle me-1"></i>Kontenjan Dolu
                                    </button>
                                <?php else: ?>
                                    <form action="training_actions.php" method="POST">
                                        <input type="hidden" name="action" value="apply">
                                        <input type="hidden" name="training_id" value="<?= $training['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">
                                            <i class="bi bi-send me-1"></i>Başvur
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="text-center text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Aktif eğitim bulunmuyor.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Görsel Önizleme Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute end-0 top-0 m-2" 
                        data-bs-dismiss="modal"></button>
                <img src="" class="img-fluid" id="modalImage">
            </div>
        </div>
    </div>
</div>

<script>
// Harita başlatma
function initMap() {
    const lat = <?= $unit['latitude'] ?>;
    const lng = <?= $unit['longitude'] ?>;
    
    // Haritayı oluştur
    const map = L.map('map').setView([lat, lng], 20);
    
    // Google Uydu görüntüsü ekle
    L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '© Google'
    }).addTo(map);

    // Marker ekle
    const marker = L.marker([lat, lng], {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map);

    // Popup ekle
    marker.bindPopup(`
        <div class="text-center">
            <h6 class="mb-2"><?= htmlspecialchars($unit['name']) ?></h6>
            <p class="small mb-2"><?= htmlspecialchars($unit['address']) ?></p>
            <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" 
               target="_blank" class="btn btn-sm btn-dark">
                <i class="bi bi-map"></i> Yol Tarifi Al
            </a>
        </div>
    `).openPopup();
}

// Görsel önizleme fonksiyonu
function showImage(src) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    document.getElementById('modalImage').src = src;
    modal.show();
}

// Haritayı başlat
initMap();
</script>

<?php include 'includes/footer.php'; ?>