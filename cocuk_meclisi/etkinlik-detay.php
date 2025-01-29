<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Türkçe tarih ayarları
setlocale(LC_TIME, 'tr_TR.UTF-8');
date_default_timezone_set('Europe/Istanbul');

// Türkçe ay isimleri için yardımcı fonksiyon
function getTurkishMonth($month) {
    $aylar = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
    ];
    return $aylar[(int)$month];
}

$db = Database::getInstance();

// Etkinlik ID'sini al
$etkinlik_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$etkinlik_id) {
    header('Location: etkinlikler.php');
    exit;
}

// Etkinlik bilgilerini getir
try {
    $etkinlik = $db->query("SELECT * FROM cocuk_meclisi_etkinlikler WHERE id = " . (int)$etkinlik_id)->fetch();
    
    if (!$etkinlik) {
        header('Location: etkinlikler.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Etkinlik detay sorgu hatası: " . $e->getMessage());
    header('Location: etkinlikler.php');
    exit;
}

$page_title = "Etkinlik Detayı - " . $etkinlik['baslik'];
include '../includes/header.php';
?>

<link rel="stylesheet" href="<?= SITE_URL ?>/cocuk_meclisi/assets/css/council-event-detail.css">

<div class="council-page">
    <!-- Hero Section -->
    <div class="event-hero">
        <div class="floating-elements">
            <div class="element balloon balloon-1">
                <i class="fas fa-paper-plane fa-2x"></i>
            </div>
            <div class="element star star-1">
                <i class="fas fa-star fa-2x"></i>
            </div>
            <div class="element cloud cloud-1">
                <i class="fas fa-cloud fa-3x"></i>
            </div>
        </div>
        
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <a href="etkinlikler.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Etkinliklere Dön
                    </a>
                    <div class="event-status-badge <?= $etkinlik['durum'] ?>">
                        <?= $etkinlik['durum'] == 'aktif' ? 'Yaklaşan Etkinlik' : 'Tamamlandı' ?>
                    </div>
                    <h1 class="event-title"><?= htmlspecialchars($etkinlik['baslik']) ?></h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Section -->
    <div class="event-details-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="event-card">
                        <!-- Etkinlik Bilgileri -->
                        <div class="event-info">
                            <div class="info-item">
                                <div class="icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="text">
                                    <strong>Tarih</strong>
                                    <span><?= date('d', strtotime($etkinlik['tarih'])) ?> <?= getTurkishMonth(date('n', strtotime($etkinlik['tarih']))) ?> <?= date('Y', strtotime($etkinlik['tarih'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="text">
                                    <strong>Saat</strong>
                                    <span><?= date('H:i', strtotime($etkinlik['tarih'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="icon">
                                    <i class="fas fa-location-dot"></i>
                                </div>
                                <div class="text">
                                    <strong>Konum</strong>
                                    <span><?= htmlspecialchars($etkinlik['konum']) ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="text">
                                    <strong>Katılımcı</strong>
                                    <span><?= $etkinlik['katilimci_sayisi'] ?> Kişi</span>
                                </div>
                            </div>
                        </div>

                        <div class="event-description">
                            <h3>Etkinlik Hakkında</h3>
                            <div class="description-content">
                                <?= nl2br(htmlspecialchars($etkinlik['aciklama'])) ?>
                            </div>
                        </div>

                        <?php if ($etkinlik['durum'] == 'aktif'): ?>
                        <div class="event-action">
                            <button type="button" class="btn btn-primary btn-lg join-btn" data-bs-toggle="modal" data-bs-target="#katilimModal">
                                <i class="fas fa-calendar-check me-2"></i>Etkinliğe Katıl
                            </button>
                            <p class="action-note">
                                <i class="fas fa-info-circle me-2"></i>
                                Katılım için velinin onayı gerekmektedir
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Katılım Modalı -->
<?php if ($etkinlik['durum'] == 'aktif'): ?>
<div class="modal fade" id="katilimModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Etkinliğe Katılım Formu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['form_errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['form_errors']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success_message'] ?>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <form id="eventJoinForm" action="etkinlik-katilim.php" method="POST">
                    <input type="hidden" name="etkinlik_id" value="<?= $etkinlik_id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Çocuğun Adı Soyadı</label>
                        <input type="text" class="form-control" name="cocuk_adi" required
                               value="<?= isset($_SESSION['form_data']['cocuk_adi']) ? htmlspecialchars($_SESSION['form_data']['cocuk_adi']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Doğum Tarihi</label>
                        <input type="date" class="form-control" name="dogum_tarihi" required
                               value="<?= isset($_SESSION['form_data']['dogum_tarihi']) ? htmlspecialchars($_SESSION['form_data']['dogum_tarihi']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Veli Adı Soyadı</label>
                        <input type="text" class="form-control" name="veli_adi" required
                               value="<?= isset($_SESSION['form_data']['veli_adi']) ? htmlspecialchars($_SESSION['form_data']['veli_adi']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Veli Telefon</label>
                        <input type="tel" class="form-control" name="veli_telefon" required
                               placeholder="05XX XXX XX XX"
                               value="<?= isset($_SESSION['form_data']['veli_telefon']) ? htmlspecialchars($_SESSION['form_data']['veli_telefon']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Veli E-posta</label>
                        <input type="email" class="form-control" name="veli_email" required
                               value="<?= isset($_SESSION['form_data']['veli_email']) ? htmlspecialchars($_SESSION['form_data']['veli_email']) : '' ?>">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="onay" id="onay" required>
                        <label class="form-check-label" for="onay">
                            Çocuğumun etkinliğe katılmasına izin veriyorum
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i>Başvuruyu Gönder
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Kontrol Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['success_message']) || isset($_SESSION['form_errors'])): ?>
        var katilimModal = new bootstrap.Modal(document.getElementById('katilimModal'));
        katilimModal.show();
    <?php endif; ?>
});
</script>

<?php 
unset($_SESSION['form_data']); 
include '../includes/footer.php'; 
?>