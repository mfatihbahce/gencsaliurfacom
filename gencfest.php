<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// SEO için sayfa tanımlayıcısını belirle
$page_identifier = 'gencfest';
$page_title = 'GençFest 2025 | Şanlıurfa';

// Başvuru durumunu kontrol et
$has_application = false;
$user_application = null;
if (isset($_SESSION['user_id'])) {
    $user_application = $db->query(
        "SELECT * FROM gencfest_applications WHERE user_id = ?", 
        [$_SESSION['user_id']]
    )->fetch();
    $has_application = !empty($user_application);
}

// Onaylanan projeleri getir
$approved_projects = $db->query(
    "SELECT ga.*, u.first_name, u.last_name 
     FROM gencfest_applications ga 
     JOIN users u ON ga.user_id = u.id 
     WHERE ga.status = 'approved' 
     ORDER BY ga.created_at DESC 
     LIMIT 6"
)->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="festival-hero position-relative overflow-hidden text-white">
    <div class="container py-5">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="badge bg-danger rounded-pill mb-3 px-3 py-2">
                    15-16 Haziran 2025
                </div>
                <h1 class="display-4 fw-bold mb-4">GençFest'25</h1>
                <p class="lead mb-4">
                    Şanlıurfa'nın en büyük gençlik festivali! Teknoloji, sanat, sosyal projeler ve 
                    daha fazlası için projeni hazırla, başvurunu yap, fikirlerini binlerce kişiyle paylaş!
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn btn-primary btn-lg rounded-pill">
                            <i class="bi bi-person-fill me-2"></i>Giriş Yap
                        </a>
                        <a href="register.php" class="btn btn-outline-light btn-lg rounded-pill">
                            <i class="bi bi-person-plus-fill me-2"></i>Kayıt Ol
                        </a>
                    <?php elseif (!$has_application): ?>
                        <a href="#apply" class="btn btn-primary btn-lg rounded-pill">
                            <i class="bi bi-send-fill me-2"></i>Hemen Başvur
                        </a>
                    <?php endif; ?>
                    <a href="#schedule" class="btn btn-outline-light btn-lg rounded-pill">
                        <i class="bi bi-calendar-event me-2"></i>Program Akışı
                    </a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0" data-aos="fade-left">
                <div class="position-relative">
                    <img src="https://png.pngtree.com/png-vector/20221016/ourmid/pngtree-software-development-planning-isolated-concept-vector-illustration-set-png-image_6299948.png" alt="GençFest" class="img-fluid rounded-4">

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Festival Özellikleri -->
<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-3" data-aos="fade-up">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3 class="h5 mb-3">Son Başvuru</h3>
                    <p class="text-muted mb-0">15 Mayıs 2025</p>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h3 class="h5 mb-3">Festival Alanı</h3>
                    <p class="text-muted mb-0">Göbeklitepe Festival Alanı</p>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h3 class="h5 mb-3">Ödüller</h3>
                    <p class="text-muted mb-0">Toplam 30.000₺</p>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="h5 mb-3">Katılımcılar</h3>
                    <p class="text-muted mb-0">5000+ Ziyaretçi</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Program Akışı -->
<div id="schedule" class="bg-light py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold">Program Akışı</h2>
            <p class="text-muted">İki günlük festival programı</p>
        </div>
        
        <div class="row g-4">
            <!-- 1. Gün -->
            <div class="col-md-6" data-aos="fade-right">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0">15 Haziran 2025</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="time">09:00</div>
                                <div class="content">
                                    <h4 class="h6">Açılış Konuşmaları</h4>
                                    <p class="small text-muted mb-0">Festival alanı ana sahne</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">10:00</div>
                                <div class="content">
                                    <h4 class="h6">Proje Sunumları</h4>
                                    <p class="small text-muted mb-0">Teknoloji ve Sanat kategorileri</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">14:00</div>
                                <div class="content">
                                    <h4 class="h6">Workshop Etkinlikleri</h4>
                                    <p class="small text-muted mb-0">Paralel oturumlar</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">19:00</div>
                                <div class="content">
                                    <h4 class="h6">Konser</h4>
                                    <p class="small text-muted mb-0">Yerel sanatçılar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 2. Gün -->
            <div class="col-md-6" data-aos="fade-left">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0">16 Haziran 2025</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="time">10:00</div>
                                <div class="content">
                                    <h4 class="h6">Proje Sunumları</h4>
                                    <p class="small text-muted mb-0">Sosyal ve Çevre kategorileri</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">14:00</div>
                                <div class="content">
                                    <h4 class="h6">Panel: Gençlik ve İnovasyon</h4>
                                    <p class="small text-muted mb-0">Konuk konuşmacılar</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">16:00</div>
                                <div class="content">
                                    <h4 class="h6">Ödül Töreni</h4>
                                    <p class="small text-muted mb-0">Kazanan projelerin açıklanması</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">20:00</div>
                                <div class="content">
                                    <h4 class="h6">Kapanış Konseri</h4>
                                    <p class="small text-muted mb-0">Sürpriz sanatçı</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Onaylanan Projeler -->
<?php if (!empty($approved_projects)): ?>
<div class="container py-5">
    <div class="section-header text-center mb-5">
        <h2 class="display-5 fw-bold">Kabul Edilen Projeler</h2>
        <p class="text-muted">Festival'de yer alacak projeler</p>
    </div>
    
    <div class="swiper projects-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($approved_projects as $project): ?>
            <div class="swiper-slide">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-<?php 
                                echo match($project['project_category']) {
                                    'teknoloji' => 'primary',
                                    'sanat' => 'success',
                                    'sosyal' => 'info',
                                    'çevre' => 'warning',
                                    'eğitim' => 'danger',
                                    default => 'secondary'
                                };
                            ?> rounded-pill px-3"><?= ucfirst($project['project_category']) ?></span>
                            <small class="text-muted"><?= date('d.m.Y', strtotime($project['created_at'])) ?></small>
                        </div>
                        <h4 class="card-title h5"><?= clean($project['project_title']) ?></h4>
                        <p class="card-text small text-muted">
                            <?= substr(clean($project['project_description']), 0, 100) ?>...
                        </p>
                        <div class="border-top pt-3 mt-3">
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>
                                <?= clean($project['first_name']) ?> <?= clean($project['last_name']) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</div>
<?php endif; ?>

<!-- Başvuru Formu -->
<?php if (isset($_SESSION['user_id']) && !$has_application): ?>
<div id="apply" class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-center mb-4">Proje Başvuru Formu</h2>
                        
                        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label">Proje Adı</label>
                                <input type="text" name="project_title" class="form-control" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Proje Kategorisi</label>
                                <select name="project_category" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    <option value="teknoloji">Teknoloji</option>
                                    <option value="sanat">Sanat</option>
                                    <option value="sosyal">Sosyal</option>
                                    <option value="çevre">Çevre</option>
                                    <option value="eğitim">Eğitim</option>
                                    <option value="diğer">Diğer</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Proje Açıklaması</label>
                                <textarea name="project_description" class="form-control" rows="4" required></textarea>
                                <div class="form-text">Projenizi detaylı bir şekilde anlatın.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Projenin Amacı</label>
                                <textarea name="project_goal" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Beklenen Etki</label>
                                <textarea name="project_impact" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Takım Üyeleri</label>
                                <textarea name="team_members" class="form-control" rows="2"></textarea>
                                <div class="form-text">Varsa diğer takım üyelerinin adlarını yazınız</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Sunum İhtiyaçları</label>
                                <textarea name="presentation_needs" class="form-control" rows="2"></textarea>
                                <div class="form-text">Sunum için ihtiyaç duyacağınız ekipmanları belirtiniz</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Proje Dosyası</label>
                                <input type="file" name="project_file" class="form-control">
                                <div class="form-text">PDF, DOC, DOCX, PPT veya PPTX (Max 10MB)</div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">
                                    <i class="bi bi-send me-2"></i>Başvuruyu Gönder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SSS -->
<div class="container py-5">
    <div class="section-header text-center mb-5">
        <h2 class="display-5 fw-bold">Sıkça Sorulan Sorular</h2>
        <p class="text-muted">Festival hakkında merak edilenler</p>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Kimler başvurabilir?
                        </button>
                    </h3>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            15-29 yaş arası, Şanlıurfa'da ikamet eden tüm gençler başvurabilir.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Başvuru için son tarih nedir?
                        </button>
                    </h3>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Başvurular 15 Mayıs 2025 tarihine kadar devam edecektir.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Değerlendirme kriterleri nelerdir?
                        </button>
                    </h3>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <ul class="mb-0">
                                <li>Projenin özgünlüğü</li>
                                <li>Uygulanabilirlik</li>
                                <li>Toplumsal fayda</li>
                                <li>Sürdürülebilirlik</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stil Tanımlamaları -->
<style>
.festival-hero {
    background: linear-gradient(135deg, #2b2d42 0%, #1a1b2e 100%);
    padding: 80px 0;
}

.festival-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('assets/img/pattern.png');
    opacity: 0.1;
}

.timeline {
    position: relative;
    padding-left: 50px;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -33px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item::after {
    content: '';
    position: absolute;
    left: -39px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--bs-primary);
}

.timeline-item .time {
    position: absolute;
    left: -110px;
    top: -3px;
    font-weight: 500;
    color: var(--bs-primary);
}

.swiper {
    padding: 20px 5px;
}

.swiper-slide {
    height: auto;
}

@media (min-width: 768px) {
    .swiper-slide {
        width: 300px;
        margin-right: 20px;
    }
}

@media (max-width: 767px) {
    .festival-hero {
        padding: 40px 0;
        text-align: center;
    }
    
    .timeline {
        padding-left: 30px;
    }
    
    .timeline-item .time {
        position: relative;
        left: 0;
        margin-bottom: 0.5rem;
    }
    
    .swiper-slide {
        width: 85%;
    }
}
</style>

<!-- Script Tanımlamaları -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.projects-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 20,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 20,
            }
        }
    });
    
    // Form validasyonu
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php include 'includes/footer.php'; ?>