<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Onaylı projeleri getir
$projects = $db->query(
    "SELECT p.*, u.first_name, u.last_name 
     FROM projects p 
     LEFT JOIN users u ON p.user_id = u.id 
     WHERE p.status = 'approved' 
     ORDER BY p.created_at DESC"
)->fetchAll();

include 'includes/header.php';
?>

<br><br>
<div class="section-header text-center mb-5" data-aos="fade-up">
    <h6 class="text-primary fw-bold text-uppercase">Duyurularımız</h6>
    <h2 class="display-5 fw-bold">Bizden Duyurular</h2>
    <div class="divider mx-auto"></div>
</div>

<!-- Projeler Grid -->
<div class="container py-4">
    <?php if (empty($projects)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> Henüz duyuru bulunmuyor.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 project-card">
                        <div class="project-image-container">
                            <?php if (!empty($project['image_path']) && file_exists('uploads/projects/' . $project['image_path'])): ?>
                                <img src="<?= SITE_URL ?>/uploads/projects/<?= htmlspecialchars($project['image_path']) ?>" 
                                     class="project-image" 
                                     alt="<?= htmlspecialchars($project['title']) ?>">
                            <?php else: ?>
                                <img src="<?= SITE_URL ?>/assets/img/default-project.jpg" 
                                     class="project-image" 
                                     alt="Varsayılan duyuru görseli">
                            <?php endif; ?>
                            
                            <!-- Hover Overlay -->
                            <div class="project-overlay">
                                <button type="button" class="btn btn-light" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#projectModal<?= $project['id'] ?>">
                                    <i class="fas fa-eye"></i> Detayları Gör
                                </button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate">
                                <?= htmlspecialchars($project['title']) ?>
                            </h5>
                            <p class="card-text flex-grow-1">
                                <?= mb_substr(strip_tags($project['description']), 0, 100) ?>...
                            </p>
                            <div class="mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> 
                                    <?= date('d.m.Y', strtotime($project['created_at'])) ?>
                                </small>
                                <button type="button" class="btn btn-primary btn-sm float-end" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#projectModal<?= $project['id'] ?>">
                                    <i class="fas fa-eye"></i> Detayları Gör
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proje Modal -->
                <div class="modal fade" id="projectModal<?= $project['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <?= htmlspecialchars($project['title']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($project['image_path']) && file_exists('uploads/projects/' . $project['image_path'])): ?>
                                    <img src="<?= SITE_URL ?>/uploads/projects/<?= htmlspecialchars($project['image_path']) ?>" 
                                         class="img-fluid rounded mb-4" 
                                         alt="<?= htmlspecialchars($project['title']) ?>">
                                <?php endif; ?>
                                
                                <h6 class="fw-bold mb-3">Duyuru Detayları</h6>
                                <div class="text-muted">
                                    <?= nl2br(htmlspecialchars($project['description'])) ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="text-muted small">
                                        <i class="fas fa-calendar"></i> 
                                        Eklenme: <?= date('d.m.Y H:i', strtotime($project['created_at'])) ?>
                                        <?php if ($project['first_name'] && $project['last_name']): ?>
                                            <br>
                                            <i class="fas fa-user"></i> 
                                            Ekleyen: <?= htmlspecialchars($project['first_name'] . ' ' . $project['last_name']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($project['url'])): ?>
                                        <a href="<?= htmlspecialchars($project['url']) ?>" 
                                           class="btn btn-primary" 
                                           target="_blank">
                                            <i class="fas fa-external-link-alt"></i> Bağlantıya Git
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.project-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.project-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.project-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.project-overlay {
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

.project-image-container:hover .project-overlay {
    opacity: 1;
}

.project-image-container:hover .project-image {
    transform: scale(1.1);
}

.divider {
    width: 50px;
    height: 3px;
    background-color: var(--bs-primary);
    margin-top: 1rem;
}

/* Responsive */
@media (max-width: 992px) {
    .project-image-container {
        height: 180px;
    }
}

@media (max-width: 768px) {
    .project-image-container {
        height: 200px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>