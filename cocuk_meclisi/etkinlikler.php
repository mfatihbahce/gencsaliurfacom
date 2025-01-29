<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Türkçe tarih ayarları
setlocale(LC_TIME, 'tr_TR.UTF-8');
date_default_timezone_set('Europe/Istanbul');

// Türkçe ay isimleri için yardımcı fonksiyon
function getTurkishMonth($month) {
    $aylar = [
        1 => 'Ocak',
        2 => 'Şubat',
        3 => 'Mart',
        4 => 'Nisan',
        5 => 'Mayıs',
        6 => 'Haziran',
        7 => 'Temmuz',
        8 => 'Ağustos',
        9 => 'Eylül',
        10 => 'Ekim',
        11 => 'Kasım',
        12 => 'Aralık'
    ];
    return $aylar[(int)$month];
}

$db = Database::getInstance();

// Filtreleme için parametreleri al
$durum = isset($_GET['durum']) ? $_GET['durum'] : 'aktif';
$ay = isset($_GET['ay']) ? $_GET['ay'] : date('m');
$yil = isset($_GET['yil']) ? $_GET['yil'] : date('Y');

// SQL sorgusunu oluştur
$where_conditions = [];
$params = [];

if ($durum !== 'hepsi') {
    $where_conditions[] = "durum = '$durum'";
}

if ($ay !== 'hepsi') {
    $where_conditions[] = "MONTH(tarih) = '$ay'";
}

if ($yil !== 'hepsi') {
    $where_conditions[] = "YEAR(tarih) = '$yil'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$sql = "SELECT * FROM cocuk_meclisi_etkinlikler $where_clause ORDER BY tarih DESC";

// Sorguyu çalıştır
try {
    $etkinlikler = $db->query($sql)->fetchAll();
} catch (PDOException $e) {
    error_log("Etkinlikler sorgu hatası: " . $e->getMessage());
    $etkinlikler = [];
}

$page_title = "Çocuk Meclisi - Etkinlikler";
include '../includes/header.php';
?>

<div class="council-page">
    <!-- Hero Section -->
    <div class="council-hero-mini">
        <div class="floating-elements">
            <div class="element balloon balloon-1">
                <i class="fas fa-paper-plane fa-2x"></i>
            </div>
            <div class="element balloon balloon-2">
                <i class="fas fa-star fa-3x"></i>
            </div>
            <div class="element star star-1">
                <i class="fas fa-sun fa-2x"></i>
            </div>
            <div class="element star star-2">
                <i class="fas fa-cloud fa-2x"></i>
            </div>
        </div>
        
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="hero-title">Etkinliklerimiz</h1>
                    <p class="hero-text">Birlikte öğreniyor, eğleniyor ve büyüyoruz!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="filters-section py-4">
        <div class="container">
            <form action="" method="GET" class="filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Durum</label>
                        <select name="durum" class="form-select">
                            <option value="hepsi" <?= $durum == 'hepsi' ? 'selected' : '' ?>>Hepsi</option>
                            <option value="aktif" <?= $durum == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="tamamlandi" <?= $durum == 'tamamlandi' ? 'selected' : '' ?>>Tamamlandı</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Ay</label>
                        <select name="ay" class="form-select">
                            <option value="hepsi">Tüm Aylar</option>
                            <?php
                            $aylar = [
                                '01' => 'Ocak', '02' => 'Şubat', '03' => 'Mart',
                                '04' => 'Nisan', '05' => 'Mayıs', '06' => 'Haziran',
                                '07' => 'Temmuz', '08' => 'Ağustos', '09' => 'Eylül',
                                '10' => 'Ekim', '11' => 'Kasım', '12' => 'Aralık'
                            ];
                            foreach ($aylar as $key => $value) {
                                echo '<option value="'.$key.'" '.($ay == $key ? 'selected' : '').'>'.$value.'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Yıl</label>
                        <select name="yil" class="form-select">
                            <?php
                            $current_year = date('Y');
                            for ($y = $current_year + 1; $y >= $current_year - 2; $y--) {
                                echo '<option value="'.$y.'" '.($yil == $y ? 'selected' : '').'>'.$y.'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filtrele
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Etkinlikler Listesi -->
    <div class="events-section py-5">
        <div class="container">
            <?php if (empty($etkinlikler)): ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-calendar-xmark fa-4x mb-3"></i>
                    <h3>Etkinlik Bulunamadı</h3>
                    <p>Seçilen kriterlere uygun etkinlik bulunmamaktadır.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($etkinlikler as $etkinlik): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="event-card">
                        <div class="event-status <?= $etkinlik['durum'] ?>">
                            <?= $etkinlik['durum'] == 'aktif' ? 'Yaklaşan' : 'Tamamlandı' ?>
                        </div>
                        
                        <div class="event-date">
                            <span class="day"><?= date('d', strtotime($etkinlik['tarih'])) ?></span>
                            <span class="month"><?= getTurkishMonth(date('n', strtotime($etkinlik['tarih']))) ?></span>
                        </div>
                        
                        <h3 class="event-title"><?= htmlspecialchars($etkinlik['baslik']) ?></h3>
                        
                        <div class="event-details">
                            <div class="detail">
                                <i class="fas fa-location-dot"></i>
                                <?= htmlspecialchars($etkinlik['konum']) ?>
                            </div>
                            <div class="detail">
                                <i class="fas fa-clock"></i>
                                <?= date('H:i', strtotime($etkinlik['tarih'])) ?>
                            </div>
                            <div class="detail">
                                <i class="fas fa-users"></i>
                                <?= $etkinlik['katilimci_sayisi'] ?> Katılımcı
                            </div>
                        </div>
                        
                        <p class="event-description">
                            <?= htmlspecialchars(substr($etkinlik['aciklama'], 0, 150)) ?>...
                        </p>
                        
                        <?php if ($etkinlik['durum'] == 'aktif'): ?>
                        <a href="<?= SITE_URL ?>/cocuk_meclisi/etkinlik-detay?id=<?= $etkinlik['id'] ?>" 
                           class="btn btn-primary btn-sm w-100 mt-3">
                            <i class="fas fa-calendar-check me-2"></i>Detayları Gör
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Etkinlikler Sayfası Stilleri */
.council-page {
    --primary-color: #FF9AA2;
    --secondary-color: #B5EAD7;
    --accent-color: #FFDAC1;
    --text-color: #6B5B95;
    --soft-pink: #FFB7B2;
    --soft-yellow: #FFFFD8;
    --soft-blue: #C7CEEA;
    font-family: 'Comfortaa', 'Nunito', sans-serif;
}

.council-hero-mini {
    background: linear-gradient(135deg, var(--soft-pink), var(--soft-blue));
    padding: 60px 0;
    position: relative;
    overflow: hidden;
}

.council-hero-mini .hero-title {
    font-size: 2.5rem;
    color: var(--text-color);
    margin-bottom: 1rem;
    font-weight: 700;
}

.council-hero-mini .hero-text {
    color: var(--text-color);
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Floating Elements */
.floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
}

.element {
    position: absolute;
    color: rgba(255, 255, 255, 0.6);
    animation: float 6s ease-in-out infinite;
}

.balloon-1 { top: 20%; left: 10%; }
.balloon-2 { top: 30%; right: 15%; animation-delay: -2s; }
.star-1 { bottom: 20%; left: 20%; animation-delay: -1s; }
.star-2 { top: 40%; right: 25%; animation-delay: -3s; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

/* Filtreler */
.filters-section {
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.filter-form {
    background: white;
    padding: 20px;
    border-radius: 15px;
}

.form-label {
    color: var(--text-color);
    font-weight: 600;
    font-size: 0.9rem;
}

.form-select {
    border: 2px solid rgba(107, 91, 149, 0.1);
    border-radius: 10px;
    padding: 10px;
    font-size: 0.9rem;
}

/* Event Cards */
.event-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(107, 91, 149, 0.1);
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.5);
    height: 100%;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(107, 91, 149, 0.15);
}

.event-status {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.event-status.aktif {
    background: var(--secondary-color);
    color: var(--text-color);
}

.event-status.tamamlandi {
    background: var(--soft-pink);
    color: var(--text-color);
}

.event-date {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    background: var(--soft-yellow);
    padding: 0.5rem 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.event-date .day {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-color);
    line-height: 1;
}

.event-date .month {
    font-size: 0.9rem;
    color: var(--text-color);
    opacity: 0.8;
}

.event-title {
    font-size: 1.2rem;
    color: var(--text-color);
    margin-bottom: 1rem;
    font-weight: 600;
    line-height: 1.4;
}

.event-details {
    margin: 1rem 0;
}

.detail {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-color);
    opacity: 0.8;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.event-description {
    color: var(--text-color);
    font-size: 0.9rem;
    line-height: 1.6;
    opacity: 0.8;
}

/* Empty State */
.empty-state {
    color: var(--text-color);
    opacity: 0.5;
    padding: 3rem;
}

.empty-state i {
    color: var(--soft-pink);
}

/* Buttons */
.btn-primary {
    background: var(--primary-color);
    border: none;
    color: white;
    font-weight: 600;
    padding: 0.8rem 1.5rem;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: var(--text-color);
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .council-hero-mini .hero-title {
        font-size: 2rem;
    }
    
    .council-hero-mini .hero-text {
        font-size: 1rem;
    }
    
    .filter-form {
        padding: 15px;
    }
    
    .event-card {
        margin-bottom: 1rem;
    }
    
    .floating-elements {
        display: none;
    }
}
</style>

<?php include '../includes/footer.php'; ?>