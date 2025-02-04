<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Filtreleme parametreleri
$district_id = isset($_GET['district']) ? (int)$_GET['district'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// İlçeleri getir
$districts = $db->query("SELECT * FROM districts ORDER BY name")->fetchAll();

// Gezilecek yerleri getir
$query = "SELECT p.*, d.name as district_name, u.username, u.first_name, u.last_name 
          FROM places p 
          LEFT JOIN districts d ON p.district_id = d.id 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'approved'";
$params = [];

if ($district_id > 0) {
    $query .= " AND p.district_id = ?";
    $params[] = $district_id;
}

if ($search) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.created_at DESC";
$places = $db->query($query, $params)->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Başlık ve Filtreleme -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-map-marker-alt text-primary"></i> Gezilecek Yerler
            </h1>
            <p class="text-muted">Arkadaşlarınızın önerdiği güzel mekanları keşfedin</p>
        </div>
        <?php if (isLoggedIn()): ?>
            <div class="col-md-4 text-end">
                <a href="submit-place.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Yeni Mekan Öner
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Filtreleme Formu -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Mekan ara..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <select name="district" class="form-select">
                        <option value="">Tüm İlçeler</option>
                        <?php foreach ($districts as $district): ?>
                            <option value="<?= $district['id'] ?>" 
                                    <?= $district_id == $district['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($district['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrele
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mekanlar Listesi -->
    <?php if (empty($places)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Henüz mekan önerisi bulunmuyor.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($places as $place): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm hover-card">
                        <?php if ($place['image_path'] && $place['image_path'] !== 'default.jpg'): ?>
                            <img src="uploads/points/places/<?= htmlspecialchars($place['image_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($place['title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="assets/img/default-place.jpg" 
                                 class="card-img-top" 
                                 alt="Varsayılan mekan görseli"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($place['title']) ?>
                            </h5>
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?= htmlspecialchars($place['district_name']) ?>
                            </p>
                            <p class="card-text" style="height: 4.5rem; overflow: hidden;">
                                <?= nl2br(htmlspecialchars(substr($place['description'], 0, 100))) ?>...
                            </p>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> 
                                    <?= htmlspecialchars($place['first_name'] . ' ' . $place['last_name']) ?>
                                </small>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#placeModal<?= $place['id'] ?>">
                                    <i class="fas fa-info-circle"></i> Detaylar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mekan Detay Modal -->
                <div class="modal fade" id="placeModal<?= $place['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <?= htmlspecialchars($place['title']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if ($place['image_path'] && $place['image_path'] !== 'default.jpg'): ?>
                                    <img src="uploads/points/places/<?= htmlspecialchars($place['image_path']) ?>" 
                                         class="img-fluid rounded mb-3" 
                                         alt="<?= htmlspecialchars($place['title']) ?>">
                                <?php endif; ?>

                                <h6 class="text-primary">
                                    <i class="fas fa-map-marker-alt"></i> Konum
                                </h6>
                                <p class="mb-3">
                                    <strong>İlçe:</strong> <?= htmlspecialchars($place['district_name']) ?><br>
                                    <strong>Adres:</strong> <?= nl2br(htmlspecialchars($place['address'])) ?>
                                </p>

                                <h6 class="text-primary">
                                    <i class="fas fa-info-circle"></i> Açıklama
                                </h6>
                                <p class="mb-3">
                                    <?= nl2br(htmlspecialchars($place['description'])) ?>
                                </p>

                                <div class="alert alert-light">
                                    <small>
                                        <i class="fas fa-user"></i> 
                                        <strong>Öneren:</strong> 
                                        <?= htmlspecialchars($place['first_name'] . ' ' . $place['last_name']) ?>
                                        <br>
                                        <i class="fas fa-clock"></i> 
                                        <strong>Eklenme Tarihi:</strong> 
                                        <?= formatDateTime($place['created_at']) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-5px);
}
</style>

<?php include 'includes/footer.php'; ?> 