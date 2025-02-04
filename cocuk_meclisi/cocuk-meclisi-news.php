<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 8;
$offset = ($page - 1) * $per_page;

// Toplam onaylı haber sayısı
$total_count = $db->query(
    "SELECT COUNT(*) as count FROM cocuk_meclisi_news WHERE status = 'approved'"
)->fetch()['count'];
$total_pages = ceil($total_count / $per_page);

// Onaylı haberleri getir
$cocuk_meclisi_news = $db->query(
    "SELECT n.*, u.first_name, u.last_name 
     FROM cocuk_meclisi_news n 
     LEFT JOIN users u ON n.user_id = u.id 
     WHERE n.status = 'approved' 
     ORDER BY n.created_at DESC 
     LIMIT ? OFFSET ?",
    [$per_page, $offset]
)->fetchAll();

$page_title = 'Haberler';
include '../includes/header.php';
?>

<!-- Hero Section -->
<br><br>
<div class="section-header text-center mb-5" data-aos="fade-up">
    <h6 class="text-primary fw-bold text-uppercase">Etkinlikler</h6>
    <h2 class="display-5 fw-bold">Şehrin Nabzı: Etkinlikler</h2>
    <div class="divider mx-auto"></div>
</div>

<div class="container py-5">
    <?php if (empty($cocuk_meclisi_news)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> Henüz Ekinlik bulunmuyor.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($cocuk_meclisi_news as $item): ?>
                <div class="col-md-3">
                    <div class="card h-100 shadow-sm hover-card">
                        <?php if ($item['image_path']): ?>
                            <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($item['image_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($item['title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                            <p class="card-text flex-grow-1">
                                <?= mb_substr(strip_tags($item['content']), 0, 100) ?>...
                            </p>
                            <div class="mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> 
                                    <?= date('d.m.Y', strtotime($item['created_at'])) ?>
                                    <?php if ($item['first_name'] && $item['last_name']): ?>
                                        <br>
                                        <i class="fas fa-user"></i> 
                                        <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
                                    <?php endif; ?>
                                </small>
                                <button type="button" 
                                        class="btn btn-primary btn-sm float-end" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#newsModal<?= $item['id'] ?>">
                                    <i class="fas fa-eye"></i> Devamını Oku
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Haber Detay Modal -->
                <div class="modal fade" id="newsModal<?= $item['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if ($item['image_path']): ?>
                                    <img src="<?= SITE_URL ?>/uploads/news/<?= htmlspecialchars($item['image_path']) ?>" 
                                         class="img-fluid rounded mb-3" 
                                         alt="<?= htmlspecialchars($item['title']) ?>">
                                <?php endif; ?>
                                <?= $item['content'] ?>
                                <hr>
                                <div class="text-muted">
                                    <small>
                                        <i class="fas fa-calendar"></i> 
                                        Yayınlanma: <?= date('d.m.Y H:i', strtotime($item['created_at'])) ?>
                                        <?php if ($item['first_name'] && $item['last_name']): ?>
                                            <br>
                                            <i class="fas fa-user"></i> 
                                            Yazar: <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Sayfalama" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-5px);
}

.divider {
    width: 50px;
    height: 3px;
    background-color: var(--bs-primary);
    margin-top: 1rem;
}
</style>

<?php include '../includes/footer.php'; ?>