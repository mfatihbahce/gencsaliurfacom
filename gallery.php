<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Galeri öğelerini getir
$gallery = $db->query("SELECT * FROM gallery ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->


<br><br>
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">Galeri</h6>
        <h2 class="display-5 fw-bold">Etkinliklerimizden Görüntüler</h2>
        <div class="divider mx-auto"></div>
    </div>

<!-- Galeri Grid -->
<div class="container py-4">
    <div class="row g-4">
        <?php foreach ($gallery as $item): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 gallery-card">
                    <div class="gallery-image-container">
                        <img src="<?= SITE_URL ?>/uploads/gallery/<?= $item['image_path'] ?>" 
                             class="card-img-top gallery-image" 
                             alt="<?= clean($item['title']) ?>"
                             data-bs-toggle="modal" 
                             data-bs-target="#galleryModal<?= $item['id'] ?>">
                        
                        <!-- Hover Overlay -->
                        <div class="gallery-overlay">
                            <button type="button" class="btn btn-light" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#galleryModal<?= $item['id'] ?>">
                                <i class="bi bi-eye"></i> Görüntüle
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-truncate"><?= clean($item['title']) ?></h5>
                        <?php if (!empty($item['description'])): ?>
                            <p class="card-text small text-muted">
                                <?= mb_substr(clean($item['description']), 0, 50) ?>...
                            </p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <?= date('d.m.Y', strtotime($item['created_at'])) ?>
                        </small>
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
                                <h5 class="modal-title mb-2"><?= clean($item['title']) ?></h5>
                                <?php if (!empty($item['description'])): ?>
                                    <p class="text-muted mb-2"><?= nl2br(clean($item['description'])) ?></p>
                                <?php endif; ?>
                                <small class="text-muted">
                                    Eklenme Tarihi: <?= date('d.m.Y', strtotime($item['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Stil -->
<style>
.gallery-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: pointer;
}

.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.gallery-image-container {
    position: relative;
    height: 200px;
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

.card-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.card-text {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

/* Modal Stilleri */
.modal-content {
    border: none;
    border-radius: 10px;
    overflow: hidden;
}

.modal-header .btn-close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    z-index: 1;
    background-color: white;
    border-radius: 50%;
    padding: 0.5rem;
}

.modal-body img {
    max-height: 80vh;
    object-fit: contain;
}

/* Responsive */
@media (max-width: 992px) {
    .col-lg-3 {
        width: 33.333%;
    }
}

@media (max-width: 768px) {
    .col-lg-3 {
        width: 50%;
    }
}

@media (max-width: 576px) {
    .col-lg-3 {
        width: 100%;
    }
    
    .gallery-image-container {
        height: 250px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>