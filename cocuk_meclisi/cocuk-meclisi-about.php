<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

// Hakkımızda içeriğini getir
$cocuk_meclisi_about = $db->query("SELECT * FROM cocuk_meclisi_about WHERE id = 1")->fetch();

$page_title = "Hakkımızda";
include '../includes/header.php';
?>


<br><br>
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Hakkımızda</h6>
        <h2 class="display-5 fw-bold">Misyon ve Vizyonumuz</h2>
        <div class="divider mx-auto"></div>
    </div>





<!-- About Content Section -->
<div class="about-content py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Ana İçerik -->
            <div class="col-lg-8">
                <div class="content-wrapper">
                    <?php if ($cocuk_meclisi_about['image_path']): ?>
                        <div class="about-image mb-4">
                            <img src="<?= SITE_URL ?>/uploads/about/<?= $cocuk_meclisi_about['image_path'] ?>" 
                                 alt="<?= clean($cocuk_meclisi_about['title']) ?>" 
                                 class="img-fluid rounded shadow">
                        </div>
                    <?php endif; ?>
                    
                    <div class="about-text">
                        <?= $cocuk_meclisi_about['content'] ?>
                    </div>
                </div>
            </div>

            <!-- Misyon ve Vizyon -->
            <div class="col-lg-4">
                <!-- Misyon -->
                <div class="card mission-card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="bi bi-bullseye text-primary me-2"></i>
                            Misyonumuz
                        </h3>
                        <p class="card-text"><?= nl2br(clean($cocuk_meclisi_about['mission'])) ?></p>
                    </div>
                </div>

                <!-- Vizyon -->
                <div class="card vision-card">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="bi bi-eye text-primary me-2"></i>
                            Vizyonumuz
                        </h3>
                        <p class="card-text"><?= nl2br(clean($cocuk_meclisi_about['vision'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Hero Section */
.about-hero {
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                url('assets/img/about-hero-bg.jpg') center/cover;
    color: white;
    padding: 5rem 0;
    margin-bottom: 3rem;
}

.about-hero .breadcrumb {
    background: transparent;
}

.about-hero .breadcrumb-item a {
    color: white;
    text-decoration: none;
}

.about-hero .breadcrumb-item.active {
    color: rgba(255,255,255,0.8);
}

/* Content Section */
.content-wrapper {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.about-image {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
}

.about-image img {
    width: 100%;
    transition: transform 0.3s ease;
}

.about-image:hover img {
    transform: scale(1.02);
}

.about-text {
    line-height: 1.8;
    color: #444;
}

/* Cards */
.mission-card, .vision-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.mission-card:hover, .vision-card:hover {
    transform: translateY(-5px);
}

.card-title {
    font-size: 1.5rem;
    color: #333;
}

.card-text {
    color: #666;
    line-height: 1.7;
}

/* Responsive */
@media (max-width: 991px) {
    .about-hero {
        padding: 3rem 0;
    }
    
    .content-wrapper {
        padding: 1.5rem;
    }
    
    .mission-card, .vision-card {
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 2rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>