<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

// Debug için sorguyu yazdıralım
try {
    // Önce tabloyu kontrol edelim
    $check_table = $db->query("SHOW TABLES LIKE 'cocuk_meclisi_ayarlar'")->fetch();
    
    if (!$check_table) {
        // Tablo yoksa oluşturalım ve örnek verileri ekleyelim
        $db->query("
            CREATE TABLE IF NOT EXISTS `cocuk_meclisi_ayarlar` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `baslik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `deger` text COLLATE utf8mb4_unicode_ci NOT NULL,
                `tip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'text',
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Örnek verileri ekleyelim
        $db->query("
            INSERT INTO `cocuk_meclisi_ayarlar` (`baslik`, `deger`, `tip`) VALUES
            ('hero_text', 'Hayallerimizi birlikte gerçekleştiriyoruz!', 'text'),
            ('dreams_text', 'Her çocuğun sesi duyulsun, fikirleri değer görsün istiyoruz!', 'text'),
            ('values_text', 'Sevgi, saygı ve eşitlik bizim en önemli değerlerimiz!', 'text'),
            ('goals_text', 'Daha güzel bir gelecek için fikirlerimizi paylaşıyoruz!', 'text')
        ");
    }

    // Şimdi verileri çekelim
    $settings_query = $db->query("SELECT * FROM cocuk_meclisi_ayarlar");
    $settings_data = $settings_query->fetchAll(PDO::FETCH_ASSOC);
    
    // Verileri düzenleyelim
    $settings = [];
    foreach ($settings_data as $row) {
        $settings[$row['baslik']] = $row['deger'];
    }

} catch (PDOException $e) {
    // Hata durumunda varsayılan değerleri kullanalım
    error_log("Veritabanı hatası: " . $e->getMessage());
    $settings = [];
}

// Varsayılan değerler
$default_settings = [
    'hero_text' => 'Hayallerimizi birlikte gerçekleştiriyoruz!',
    'dreams_text' => 'Her çocuğun sesi duyulsun, fikirleri değer görsün istiyoruz!',
    'values_text' => 'Sevgi, saygı ve eşitlik bizim en önemli değerlerimiz!',
    'goals_text' => 'Daha güzel bir gelecek için fikirlerimizi paylaşıyoruz!'
];

// Veritabanından gelen değerleri varsayılan değerlerle birleştir
$settings = array_merge($default_settings, $settings);

$page_title = "Çocuk Meclisi - Hakkımızda";
include '../includes/header.php';
?>

<div class="council-page">
    <!-- Hero Section -->
    <div class="council-hero">
        <!-- Animasyonlu Arka Plan Elementleri -->
        <div class="floating-elements">
            <div class="element kite">
                <i class="fas fa-kite fa-3x"></i>
            </div>
            <div class="element butterfly">
                <i class="fas fa-butterfly fa-2x"></i>
            </div>
            <div class="element cloud cloud-1">
                <i class="fas fa-cloud fa-3x"></i>
            </div>
            <div class="element cloud cloud-2">
                <i class="fas fa-cloud fa-2x"></i>
            </div>
            <div class="element flower flower-1">
                <i class="fas fa-flower fa-2x"></i>
            </div>
            <div class="element flower flower-2">
                <i class="fas fa-flower fa-2x"></i>
            </div>
        </div>

        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="hero-title">Çocuk Meclisi</h1>
                    <p class="hero-text"><?= htmlspecialchars($settings['hero_text']) ?></p>
                    <div class="hero-buttons">
                        <a href="<?= SITE_URL ?>/cocuk_meclisi/basvuru" class="btn btn-primary btn-lg join-btn">
                            <i class="fa-solid fa-hands-clapping me-2"></i>Bize Katıl
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div class="council-about py-5">
        <div class="container">
            <div class="section-title text-center mb-5">
                <h2>Biz Kimiz?</h2>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="card-icon">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <h3>Hayallerimiz</h3>
                        <p><?= htmlspecialchars($settings['dreams_text']) ?></p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="card-icon">
                            <i class="fa-solid fa-heart"></i>
                        </div>
                        <h3>Değerlerimiz</h3>
                        <p><?= htmlspecialchars($settings['values_text']) ?></p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="card-icon">
                            <i class="fa-solid fa-lightbulb"></i>
                        </div>
                        <h3>Hedeflerimiz</h3>
                        <p><?= htmlspecialchars($settings['goals_text']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Preview -->
    <div class="council-activities py-5 bg-light">
        <div class="container">
            <div class="section-title text-center mb-5">
                <h2>Neler Yapıyoruz?</h2>
            </div>

            <div class="row g-4">
                <?php
                // Son 3 etkinliği getir
                $activities = $db->query("
                    SELECT * FROM cocuk_meclisi_etkinlikler 
                    WHERE durum = 'aktif' 
                    ORDER BY tarih DESC 
                    LIMIT 3
                ")->fetchAll();

                foreach ($activities as $activity):
                ?>
                <div class="col-md-4">
                    <div class="activity-card">
                        <div class="card-icon">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <h4><?= htmlspecialchars($activity['baslik']) ?></h4>
                        <p><?= htmlspecialchars(substr($activity['aciklama'], 0, 100)) ?>...</p>
                        <div class="activity-date">
                            <i class="fa-regular fa-clock me-2"></i>
                            <?= date('d.m.Y', strtotime($activity['tarih'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?= SITE_URL ?>/cocuk_meclisi/etkinlikler" class="btn btn-outline-primary btn-lg">
                    <i class="fa-solid fa-calendar-days me-2"></i>Tüm Etkinlikler
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Genel Stiller */
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

/* Hero Section */
.council-hero {
    background: linear-gradient(135deg, var(--soft-pink), var(--soft-blue));
    min-height: 600px;
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

/* Floating Elements */
.floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
}

.element {
    position: absolute;
    opacity: 0.8;
    color: rgba(255, 255, 255, 0.8);
}

/* Uçurtma Animasyonu */
.kite {
    top: 10%;
    right: 15%;
    animation: float 8s ease-in-out infinite;
}

/* Kelebek Animasyonu */
.butterfly {
    top: 30%;
    left: 10%;
    animation: flutter 12s linear infinite;
}

/* Bulut Animasyonları */
.cloud {
    opacity: 0.6;
}

.cloud-1 {
    top: 15%;
    left: 20%;
    animation: drift 20s linear infinite;
}

.cloud-2 {
    top: 25%;
    right: 25%;
    animation: drift 25s linear infinite reverse;
}

/* Çiçek Animasyonları */
.flower-1 {
    bottom: 20%;
    left: 10%;
    animation: sway 6s ease-in-out infinite;
}

.flower-2 {
    bottom: 15%;
    right: 15%;
    animation: sway 8s ease-in-out infinite reverse;
}

/* Hero İçerik */
.hero-title {
    color: var(--text-color);
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 4px rgba(255,255,255,0.5);
}

.hero-text {
    color: var(--text-color);
    font-size: 1.3rem;
    margin-bottom: 2rem;
    font-weight: 500;
}

/* Özel Buton Tasarımı */
.join-btn {
    background: var(--soft-pink);
    border: none;
    color: var(--text-color);
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(255,154,162,0.3);
}

.join-btn:hover {
    transform: translateY(-3px);
    background: var(--primary-color);
    box-shadow: 0 8px 20px rgba(255,154,162,0.4);
}

/* Feature Cards */
.feature-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 30px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(107, 91, 149, 0.1);
    transition: transform 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.5);
    height: 100%;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.card-icon {
    width: 60px;
    height: 60px;
    background: var(--soft-pink);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    color: white;
}

/* Activity Cards */
.activity-card {
    background: white;
    border-radius: 30px;
    padding: 2rem;
    height: 100%;
    box-shadow: 0 10px 30px rgba(107, 91, 149, 0.1);
    transition: transform 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.activity-card:hover {
    transform: translateY(-5px);
}

.activity-date {
    margin-top: 1rem;
    color: var(--primary-color);
    font-weight: 600;
}

/* Section Titles */
.section-title h2 {
    color: var(--text-color);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.section-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 4px;
    background: var(--soft-pink);
    margin: 1rem auto;
    border-radius: 2px;
}

/* Animasyonlar */
@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(10px, -15px) rotate(5deg); }
    50% { transform: translate(0, -25px) rotate(0deg); }
    75% { transform: translate(-10px, -15px) rotate(-5deg); }
}

@keyframes flutter {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(15px, 15px) rotate(10deg); }
    50% { transform: translate(30px, 0px) rotate(-5deg); }
    75% { transform: translate(15px, -15px) rotate(10deg); }
}

@keyframes drift {
    from { transform: translateX(-100%); }
    to { transform: translateX(100vw); }
}

@keyframes sway {
    0%, 100% { transform: rotate(-5deg); }
    50% { transform: rotate(5deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-text {
        font-size: 1.1rem;
    }
    
    .floating-elements {
        display: none;
    }
}
</style>

<?php include '../includes/footer.php'; ?>