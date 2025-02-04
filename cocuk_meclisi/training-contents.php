<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Kategorileri getir
$categories = $db->query("SELECT DISTINCT category FROM training_types WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// ... existing code ...

// Seçilen kategori varsa filtrele
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$where_clause = $selected_category ? "WHERE tt.category = ?" : "";
$params = $selected_category ? [$selected_category] : [];

// Eğitim türlerini getir
$training_types = $db->query(
    "SELECT tt.*, 
           COUNT(DISTINCT t.id) as total_trainings,
           (SELECT COUNT(DISTINCT ta.user_id) 
            FROM training_requests tr 
            LEFT JOIN training_applications ta ON tr.id = ta.training_id 
            WHERE tr.training_type_id = tt.id) as total_students
    FROM training_types tt
    LEFT JOIN training_requests t ON tt.id = t.training_type_id
    {$where_clause}
    GROUP BY tt.id
    ORDER BY tt.name",
    $params
)->fetchAll();

// ... existing code ...

$page_title = 'Eğitim İçerikleri';
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid bg-light py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h2 fw-bold mb-2">Okullar için Ücretsiz Eğitim İçerikleri</h1>
                <p class="text-muted mb-0">Okulunuz için Ücetsiz Robotik kodlama ve yazılım eğitimlerimiz için talepte bulunabilirsiniz.</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="training-request" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-2"></i>Ücretsiz Eğitim Talebi Oluştur
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Kategori Filtreleme -->
<div class="container mb-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <a href="?" class="btn btn-sm <?= empty($selected_category) ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Tümü
                </a>
                <?php foreach ($categories as $category): ?>
                <a href="?category=<?= urlencode($category) ?>" 
                   class="btn btn-sm <?= $selected_category === $category ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <?= htmlspecialchars($category) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- İçerik -->
<div class="container py-4">
    <div class="row g-4">
        <?php foreach ($training_types as $type): ?>
            <div class="col-md-6" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <!-- Başlık -->
                        <h3 class="h5 fw-bold mb-3"><?= htmlspecialchars($type['name']) ?></h3>
                        
                        <!-- Eğitim Detayları -->
                        <div class="d-flex gap-3 mb-3 text-muted small">
                            <?php if (!empty($type['duration'])): ?>
                            <div>
                                <i class="bi bi-clock me-1"></i>
                                <?= htmlspecialchars($type['duration']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($type['target_audience'])): ?>
                            <div>
                                <i class="bi bi-people me-1"></i>
                                <?= htmlspecialchars($type['target_audience']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($type['category'])): ?>
                            <div>
                                <i class="bi bi-tag me-1"></i>Kategori: 
                                <?= htmlspecialchars($type['category']) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Eğitim İçeriği -->
                        <div class="mb-3">
                            <p class="card-text small mb-3"><?= nl2br(htmlspecialchars($type['description'])) ?></p>
                            
                            <?php 
                            $content = !empty($type['content']) ? json_decode($type['content'], true) : null;
                            if ($content && is_array($content) && !empty($content)): 
                            ?>
                                <ul class="list-unstyled mb-0 small">
                                    <?php foreach ($content as $item): ?>
                                        <li class="mb-1">
                                            <i class="bi bi-check2 text-primary me-2"></i>
                                            <?= htmlspecialchars($item) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                        <a href="training-request" class="btn btn-sm btn-outline-primary">
                            Eğitim Talep Et
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($training_types)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Bu kategoride henüz eğitim bulunmamaktadır.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Yukarı Çık Butonu -->
<button id="backToTop" class="btn btn-primary btn-sm rounded-circle position-fixed bottom-0 end-0 m-3" 
        style="display: none; width: 40px; height: 40px;">
    <i class="bi bi-arrow-up"></i>
</button>

<style>
/* Kart Stilleri */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 8px;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}

/* İçerik Stilleri */
.card-text {
    color: #6c757d;
    line-height: 1.6;
}

.list-unstyled li {
    color: #6c757d;
}

/* Detay İkonları */
.d-flex.gap-3 > div {
    display: inline-flex;
    align-items: center;
}

.bi {
    font-size: 0.9rem;
}

/* Kategori Butonları */
.btn-outline-primary:hover {
    background-color: var(--bs-primary);
    color: white;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

/* Yukarı Çık Butonu */
#backToTop {
    opacity: 0;
    transition: opacity 0.2s ease;
    z-index: 1000;
}

#backToTop.visible {
    opacity: 1;
}

/* Responsive Düzenlemeler */
@media (max-width: 768px) {
    .d-flex.gap-3 {
        flex-wrap: wrap;
        gap: 0.5rem !important;
    }
    
    .d-flex.gap-3 > div {
        margin-right: 1rem;
    }
    
    .card-body {
        padding: 1rem !important;
    }
}
</style>

<script>
// Yukarı Çık Butonu İşlevselliği
window.onscroll = function() {
    const backToTop = document.getElementById("backToTop");
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        backToTop.classList.add("visible");
        backToTop.style.display = "block";
    } else {
        backToTop.classList.remove("visible");
        setTimeout(() => {
            backToTop.style.display = "none";
        }, 200);
    }
};

document.getElementById("backToTop").onclick = function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

// Kart Animasyonları
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('[data-aos]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.animation = 'fadeIn 0.3s ease forwards';
                }, 100);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    cards.forEach(card => observer.observe(card));
});
</script>

<?php include 'includes/footer.php'; ?>