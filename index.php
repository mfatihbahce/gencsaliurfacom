<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';



$db = Database::getInstance();
// Kodun en başına ekleyin
function e($value) {
    if (is_null($value)) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
// SEO için sayfa tanımlayıcısını belirle
$page_identifier = 'home';
$page_title = 'Ana Sayfa';

// Son 4 onaylı haberi getir (13. satır güncelleniyor)
$news = $db->query(
    "SELECT n.*, u.first_name, u.last_name 
     FROM news n 
     LEFT JOIN users u ON n.user_id = u.id 
     WHERE n.status = 'approved' 
     ORDER BY n.created_at DESC 
     LIMIT 4"
)->fetchAll();

// Son 4 onaylı projeyi getir (16. satır güncelleniyor)
$projects = $db->query(
    "SELECT p.*, u.first_name, u.last_name 
     FROM projects p 
     LEFT JOIN users u ON p.user_id = u.id 
     WHERE p.status = 'approved' 
     ORDER BY p.created_at DESC 
     LIMIT 4"
)->fetchAll();

// Son 4 onaylı gezilecek yeri getir
$places = $db->query(
    "SELECT p.*, d.name as district_name 
     FROM places p 
     LEFT JOIN districts d ON p.district_id = d.id 
     WHERE p.status = 'approved' 
     ORDER BY p.created_at DESC 
     LIMIT 4"
)->fetchAll();

// Galeri görsellerini getir
$gallery_images = $db->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 6")->fetchAll();



$page_title = 'Ana Sayfa';
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section mb-5">
    <div class="container h-100">
        <div class="row align-items-center justify-content-between min-vh-75">
            <div class="col-md-6 pe-md-5">
                <h2 class="display-4 fw-bold mb-4">🤖 Hayallerini Kodla!</h2>
                <p class="lead mb-4">Robotik kodlama ve yazılım eğitimlerine başvurunu yap, teknoloji dünyasına adım at! Hemen şimdi yerini ayırt!</p>
                <div class="text-center text-md-start">
                    <a href="contact.php" class="btn btn-primary btn-lg px-4">İletişime Geçin</a>
                </div>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <img src="https://education.vex.com/stemlabs/sites/default/files/inline-images/Overview%20Compeition%20Base%202.0%20%2B%20Claw%20Hero%20Robot_0.png" 
                     alt="Hero Image" 
                     class="hero-image img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Eğitimler Section -->
<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-2 rounded-pill">
            <i class="bi bi-mortarboard me-1"></i>Eğitimlerimiz
        </span>
        <h2 class="display-5 fw-bold bg-gradient-text">Aktif Eğitim Programları</h2>
        <p class="text-muted">Geleceğini şekillendirmek için hemen başvur!</p>
    </div>
    
    <div class="row g-4">
        <?php
        // Aktif eğitimleri getir
        $trainings = $db->query(
            "SELECT t.*, u.name as unit_name, u.address,
                    (SELECT COUNT(*) FROM training_applications WHERE training_id = t.id) as registered
             FROM trainings t 
             JOIN units u ON t.unit_id = u.id 
             WHERE t.end_date >= CURDATE()
             ORDER BY t.start_date ASC
             LIMIT 4"
        )->fetchAll();

        if ($trainings): foreach ($trainings as $training): 
            // Kontenjan durumu hesapla
            $capacity_percentage = ($training['registered']/$training['capacity'])*100;
            $days_left = (strtotime($training['deadline_date']) - time()) / (60 * 60 * 24);
        ?>
            <div class="col-md-6 col-lg-3" data-aos="fade-up">
                <div class="card h-100 training-card">
                    <!-- Eğitim Tipi Badge -->
                    <div class="card-badge">
                        <?php if (stripos($training['title'], 'Robotik') !== false): ?>
                            <span class="badge bg-dark-subtle text-dark">
                                <i class="bi bi-robot me-2"></i>Robotik
                            </span>
                        <?php elseif (stripos($training['title'], 'girişim') !== false): ?>
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-graph-up-arrow me-1"></i>Girişimcilik
                            </span>
                        <?php elseif (stripos($training['title'], 'web') !== false): ?>
                            <span class="badge bg-info-subtle text-info">
                                <i class="bi bi-code-slash me-1"></i>Web Geliştirme
                            </span>
                        <?php else: ?>
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="bi bi-stars me-1"></i>Yeni
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">
                            <?= htmlspecialchars($training['title']) ?>
                        </h5>
                        
                        <div class="info-list">
                            <div class="info-item">
                                <i class="bi bi-building text-primary"></i>
                                <span><?= htmlspecialchars($training['unit_name']) ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <span><?= date('d.m.Y', strtotime($training['start_date'])) ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="bi bi-clock text-primary"></i>
                                <span><?= ceil($days_left) ?> gün kaldı</span>
                            </div>
                        </div>

                        <!-- Kontenjan Progress -->
                        <div class="capacity-info mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Kontenjan Durumu</small>
                                <small class="text-primary fw-bold">
                                    <?= $training['registered'] ?>/<?= $training['capacity'] ?>
                                </small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar <?= $capacity_percentage >= 80 ? 'bg-danger' : 'bg-primary' ?>" 
                                     role="progressbar" 
                                     style="width: <?= $capacity_percentage ?>%">
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
        <?php 
            endforeach;
        else:
        ?>
            <div class="col-12">
                <div class="empty-state">
                    <img src="assets/img/empty-courses.svg" alt="Eğitim Yok" class="empty-image">
                    <h4>Şu Anda Açık Eğitim Bulunmuyor</h4>
                    <p class="text-muted">Yeni eğitimlerimiz çok yakında!</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (count($trainings) > 0): ?>
            <div class="col-12 text-center mt-5">
                <a href="trainings" class="btn btn-lg btn-primary rounded-pill btn-hover-elevate px-5">
                    <i class="bi bi-grid me-2"></i>Tüm Eğitimleri Gör
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Eğitimler Section -->
<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Okullar için Eğitimlerimiz</h6>
        <h2 class="display-5 fw-bold">Okullar için Ücretsiz Eğitimler</h2>
        <div class="divider mx-auto"></div>
    </div>
    
    <div class="row g-4">
        <?php
        // Son 4 eğitim türünü getir
        $training_types = $db->query("
            SELECT * FROM training_types 
            ORDER BY id DESC 
            LIMIT 4
        ")->fetchAll();

        foreach ($training_types as $type): 
        ?>
            <div class="col-md-3" data-aos="fade-up">
                <div class="card h-100 training-card">
					
					<!--
                    <div class="card-img-top tech-pattern p-4 text-center">
                        <i class="bi bi-mortarboard display-4 text-primary"></i>
                    </div>
					-->
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($type['name']) ?></h5>
                        
                        <p class="card-text mb-3">
                            <?= mb_substr(htmlspecialchars($type['description']), 0, 100) ?>...
                        </p>
                        
                        <ul class="list-unstyled mb-3">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-people me-2 text-primary"></i>
                                <span>Hedef Kitle: <?= htmlspecialchars($type['target_audience']) ?></span>
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="bi bi-clock me-2 text-primary"></i>
                                <span>Süre: <?= htmlspecialchars($type['duration']) ?></span>
                            </li>
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent border-0 p-4">
                        <a href="training-contents.php" class="btn btn-primary w-100 rounded-pill hover-scale">
                            <i class="bi bi-arrow-right me-1"></i>Detayları Gör
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12 text-center mt-4">
            <a href="training-contents" class="btn btn-outline-primary btn-lg rounded-pill hover-scale">
                <i class="bi bi-grid me-2"></i>Tüm Eğitimleri Gör
            </a>
        </div>
    </div>
</div>


<!-- Birimlerimiz Section -->
<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Birimlerimiz</h6>
        <h2 class="display-5 fw-bold">Eğitim Merkezlerimiz</h2>
        <div class="divider mx-auto"></div>
    </div>
    
    <div class="row g-4">
        <?php
        // Son 4 birimi getir
        $units = $db->query("SELECT * FROM units ORDER BY id DESC LIMIT 4")->fetchAll();

        foreach ($units as $unit): 
        ?>
            <div class="col-md-3" data-aos="fade-up">
                <div class="card h-100 unit-card">
					
					<!--
                    <div class="card-img-top tech-pattern p-4 text-center">
                        <i class="bi bi-building display-4 text-primary"></i>
                    </div>
					-->
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($unit['name']) ?></h5>
                        
                        <ul class="list-unstyled mb-3">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-geo-alt me-2 text-primary"></i>
                                <span><?= mb_substr(htmlspecialchars($unit['address']), 0, 50) ?>...</span>
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="bi bi-clock me-2 text-primary"></i>
                                <span>Çalışma Saatleri: <?= htmlspecialchars($unit['working_hours']) ?></span>
                            </li>
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent border-0 p-4">
                        <a href="unit-detail.php?id=<?= $unit['id'] ?>" class="btn btn-primary w-100 rounded-pill hover-scale">
                            <i class="bi bi-arrow-right me-1"></i>Detayları Gör
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12 text-center mt-4">
            <a href="units" class="btn btn-outline-primary btn-lg rounded-pill hover-scale">
                <i class="bi bi-grid me-2"></i>Tüm Merkezleri Gör
            </a>
        </div>
    </div>
</div>

<!-- Haberler Section -->
<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Etkinlikler</h6>
        <h2 class="display-5 fw-bold">Son Etkinliklerimiz</h2>
        <div class="divider mx-auto"></div>
    </div>
    
    <div class="row g-4">
        <?php foreach ($news as $item): ?>
            <div class="col-md-3" data-aos="fade-up">
                <div class="card h-100 news-card">
                    <?php if (!empty($item['image_path']) && file_exists('uploads/news/' . $item['image_path'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/news/<?= $item['image_path'] ?>" 
                             class="card-img-top" alt="<?= clean($item['title']) ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <img src="<?= SITE_URL ?>/assets/img/default-news.jpg" 
                             class="card-img-top" alt="Varsayılan görsel"
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold"><?= clean($item['title']) ?></h5>
                        <p class="card-text flex-grow-1">
                            <?= mb_substr(strip_tags($item['content']), 0, 100) ?>...
                        </p>
                        <div class="mt-auto">
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('d.m.Y', strtotime($item['created_at'])) ?>
                            </small>
                            <button type="button" class="btn btn-primary w-100 rounded-pill hover-scale" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#newsModal<?= $item['id'] ?>">
                                <i class="bi bi-arrow-right me-1"></i>Devamını Oku
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Haber Detay Modal -->
            <div class="modal fade" id="newsModal<?= $item['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold"><?= clean($item['title']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($item['image_path']) && file_exists('uploads/news/' . $item['image_path'])): ?>
                                <img src="<?= SITE_URL ?>/uploads/news/<?= $item['image_path'] ?>" 
                                     class="img-fluid rounded mb-3" alt="<?= clean($item['title']) ?>">
                            <?php else: ?>
                                <img src="<?= SITE_URL ?>/assets/img/default-news.jpg" 
                                     class="img-fluid rounded mb-3" alt="Varsayılan görsel">
                            <?php endif; ?>
                            <div class="content">
                                <?= $item['content'] ?>
                            </div>
                            <hr>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('d.m.Y H:i', strtotime($item['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col-12 text-center mt-4">
            <a href="news" class="btn btn-outline-primary btn-lg rounded-pill hover-scale">
                <i class="bi bi-grid me-2"></i>Tüm Son Gelişmeleri Gör
            </a>
        </div>
    </div>
</div>

<!-- Projeler Section -->
<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Duyurular</h6>
        <h2 class="display-5 fw-bold">Güncel Duyurular</h2>
        <div class="divider mx-auto"></div>
    </div>
    
    <div class="row g-4">
        <?php foreach ($projects as $project): ?>
            <div class="col-md-3" data-aos="fade-up">
                <div class="card h-100 project-card">
                    <?php if (!empty($project['image_path']) && file_exists('uploads/projects/' . $project['image_path'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/projects/<?= $project['image_path'] ?>" 
                             class="card-img-top" 
                             alt="<?= clean($project['title']) ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <img src="<?= SITE_URL ?>/assets/img/default-project.jpg" 
                             class="card-img-top" 
                             alt="Varsayılan görsel"
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold"><?= clean($project['title']) ?></h5>
                        <p class="card-text flex-grow-1">
                            <?= mb_substr(clean($project['description']), 0, 100) ?>...
                        </p>
                        <a href="projects.php" class="btn btn-primary rounded-pill hover-scale mt-auto">
                            <i class="bi bi-arrow-right me-1"></i>Detayları Gör
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- Gezilecek Yerler Section -->
<!-- 
<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Keşfedin</h6>
        <h2 class="display-5 fw-bold">Gezilecek Yerler</h2>
        <div class="divider mx-auto"></div>
    </div>
    
    <div class="row g-4">
        <?php if (!empty($places)): foreach ($places as $place): ?>
            <div class="col-md-3" data-aos="fade-up">
                <div class="card h-100 training-card">
                    <?php if (!empty($place['image_path']) && file_exists('uploads/places/' . $place['image_path'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/places/<?= htmlspecialchars($place['image_path'] ?? '') ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($place['title'] ?? '') ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <img src="<?= SITE_URL ?>/assets/img/default-place.jpg" 
                             class="card-img-top" 
                             alt="Varsayılan görsel"
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($place['title'] ?? '') ?></h5>
                        
                        <ul class="list-unstyled mb-3">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-geo-alt me-2 text-primary"></i>
                                <span><?= htmlspecialchars($place['district_name'] ?? '') ?></span>
                            </li>
                            <li class="mb-2">
                                <p class="card-text text-muted small">
                                    <?= mb_substr(strip_tags($place['description'] ?? ''), 0, 100) ?>...
                                </p>
                            </li>
                        </ul>
                    </div>

                    <div class="card-footer bg-transparent border-0">
                        <a href="places.php?id=<?= $place['id'] ?? '' ?>" class="btn btn-primary w-100 rounded-pill hover-scale">
                            <i class="bi bi-eye me-1"></i>Detayları Gör
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Henüz gezilecek yer eklenmemiş.
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($places)): ?>
            <div class="col-12 text-center mt-4">
                <a href="places.php" class="btn btn-outline-primary btn-lg rounded-pill hover-scale">
                    <i class="bi bi-grid me-2"></i>Tüm Yerleri Gör
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
-->


<!-- Galeri Section -->

<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Galeri</h6>
        <h2 class="display-5 fw-bold">Sınıflarımızdan Görseller</h2>
        <div class="divider mx-auto"></div>
    </div>
    
    <div class="row g-4">
        <?php foreach ($gallery_images as $item): ?>
            <div class="col-md-2" data-aos="fade-up">
                <div class="card h-100 gallery-card">
                    <div class="gallery-image-container">
                        <img src="<?= SITE_URL ?>/uploads/gallery/<?= $item['image_path'] ?>" 
                             class="card-img-top gallery-image" 
                             alt="<?= clean($item['title']) ?>"
                             data-bs-toggle="modal" 
                             data-bs-target="#galleryModal<?= $item['id'] ?>">
                        
                        <div class="gallery-overlay">
                            <button type="button" class="btn btn-light btn-sm rounded-circle" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#galleryModal<?= $item['id'] ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <h6 class="card-title text-truncate mb-0"><?= clean($item['title']) ?></h6>
                    </div>
                </div>
            </div>

            <!-- Görsel Modal -->

            <div class="modal fade" id="galleryModal<?= $item['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img src="<?= SITE_URL ?>/uploads/gallery/<?= $item['image_path'] ?>" 
                                 class="img-fluid" alt="<?= clean($item['title']) ?>">
                        </div>
                        <div class="modal-footer">
                            <div class="w-100">
                                <h5 class="modal-title fw-bold mb-2"><?= clean($item['title']) ?></h5>
                                <?php if (!empty($item['description'])): ?>
                                    <p class="text-muted mb-2"><?= nl2br(clean($item['description'])) ?></p>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date('d.m.Y', strtotime($item['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
		<div class="col-12 text-center mt-4">
            <a href="gallery" class="btn btn-outline-primary btn-lg rounded-pill hover-scale">
                <i class="bi bi-grid me-2"></i>Tüm Galeri Gör
            </a>
        </div>
    </div>
</div>


<!-- Stil eklemeleri -->
<style>
:root {
    --primary-color: #2b2d42;
    --secondary-color: #8d99ae;
    --accent-color: #ef233c;
    --light-color: #edf2f4;
}

<!-- Hero Section için stil -->

.hero-section {
    position: relative;
    margin-bottom: 3rem;
}

.carousel-item {
    height: 80vh;
}

.carousel-item img {
    height: 100%;
    object-fit: cover;
    filter: brightness(0.5);
}

.carousel-caption {
    top: 50%;
    transform: translateY(-50%);
    bottom: auto;
    padding: 0 15%;
}

.carousel-indicators {
    margin-bottom: 3rem;
}

.carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 6px;
}

@media (max-width: 768px) {
    .carousel-item {
        height: 60vh;
    }

    .carousel-caption {
        padding: 0 10%;
    }

    .carousel-caption h1 {
        font-size: 2.5rem;
    }

    .carousel-caption p {
        font-size: 1rem;
    }
}


/* Card Styles */
.training-card, .news-card, .project-card, .gallery-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.95);
    overflow: hidden;
}

.training-card:hover, .news-card:hover, .project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.tech-pattern {
    background: linear-gradient(135deg, #f8f9fa 25%, #e9ecef 25%) -50px 0,
                linear-gradient(225deg, #f8f9fa 25%, #e9ecef 25%) -50px 0,
                linear-gradient(315deg, #f8f9fa 25%, #e9ecef 25%),
                linear-gradient(45deg, #f8f9fa 25%, #e9ecef 25%);
    background-size: 100px 100px;
}

.hover-scale {
    transition: transform 0.3s ease;
}

.hover-scale:hover {
    transform: scale(1.05);
}

/* Section Header */
.section-header .divider {
    width: 50px;
    height: 3px;
    background: var(--accent-color);
    margin-top: 15px;
}

/* Progress Bar */
.progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.progress-bar {
    background: linear-gradient(45deg, var(--accent-color), #ff6b6b);
    border-radius: 10px;
}

/* Gallery */
.gallery-image-container {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-image-container:hover .gallery-overlay {
    opacity: 1;
}

.gallery-image-container:hover .gallery-image {
    transform: scale(1.1);
}

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    background: var(--primary-color);
    color: var(--light-color);
}

.modal-body {
    padding: 2rem;
}

/* Button Styles */
.btn-primary {
    background: linear-gradient(45deg, var(--accent-color), #ff6b6b);
    border: none;
    box-shadow: 0 4px 15px rgba(239, 35, 60, 0.2);
}

.btn-primary:hover {
    background: linear-gradient(45deg, #ff6b6b, var(--accent-color));
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 35, 60, 0.3);
}

/* Card Content Styles */
.card-title {
    color: var(--primary-color);
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.card-text {
    color: var(--secondary-color);
    font-size: 0.95rem;
    line-height: 1.6;
}

/* List Styles */
.list-unstyled li {
    margin-bottom: 0.75rem;
    color: var(--secondary-color);
}

.list-unstyled i {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Section Spacing */
.section-header {
    margin-bottom: 4rem;
}

.section-header h6 {
    letter-spacing: 2px;
    margin-bottom: 1rem;
}

.section-header h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
}

/* Contact Section */
.contact-section {
    background: linear-gradient(135deg, var(--primary-color), #1a1b2e);
    color: var(--light-color);
    padding: 5rem 0;
}

.contact-section .list-unstyled li {
    color: var(--light-color);
    opacity: 0.9;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .hero-section {
        padding: 50px 0;
        text-align: center;
    }
    
    .hero-image-container {
        margin-top: 50px;
    }

    .section-header {
        margin-bottom: 3rem;
    }

    .card {
        margin-bottom: 1.5rem;
    }

    .modal-body {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .hero-section h1 {
        font-size: 2.5rem;
    }

    .section-header h2 {
        font-size: 2rem;
    }

    .card-title {
        font-size: 1.1rem;
    }
}

/* Animation Styles */
.fade-up {
    animation: fadeUp 0.5s ease-out forwards;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: var(--light-color);
}

::-webkit-scrollbar-thumb {
    background: var(--secondary-color);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-color);
}
</style>

<!-- Script eklemeleri -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
particlesJS("particles-js", {
    particles: {
        number: { value: 80, density: { enable: true, value_area: 800 } },
        color: { value: "#ffffff" },
        shape: { type: "circle" },
        opacity: { value: 0.5, random: false },
        size: { value: 3, random: true },
        line_linked: {
            enable: true,
            distance: 150,
            color: "#ffffff",
            opacity: 0.4,
            width: 1
        },
        move: {
            enable: true,
            speed: 6,
            direction: "none",
            random: false,
            straight: false,
            out_mode: "out",
            bounce: false
        }
    }
});

AOS.init({
    duration: 1000,
    once: true
});
</script>

<?php include 'includes/footer.php'; ?>