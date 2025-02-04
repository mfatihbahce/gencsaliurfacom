<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

// Kullanıcı girişi kontrolü
checkAdmin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Debug için
error_log("User ID: " . $user_id);

// Kullanıcı bilgilerini getir
try {
    $user = $db->query(
        "SELECT u.*, d.name as district_name
         FROM users u 
         LEFT JOIN districts d ON u.district_id = d.id
         WHERE u.id = ?", 
        [$user_id]
    )->fetch();

    // Debug için
    error_log("User data: " . print_r($user, true));

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $user = null;
}

// Kullanıcı bulunamadıysa varsayılan değerler ata
if (!$user) {
    $user = [
        'id' => $user_id,
        'first_name' => '',
        'last_name' => '',
        'tc_no' => '',
        'email' => '',
        'phone' => '',
        'birth_date' => '',
        'gender' => '',
        'district_name' => '',
        'total_points' => 0
    ];
}

// Eğitim başvurularını getir
try {
    $applications = $db->query(
        "SELECT ta.*, t.title as training_title, t.start_date, t.end_date
         FROM training_applications ta
         JOIN trainings t ON ta.training_id = t.id
         WHERE ta.user_id = ?
         ORDER BY ta.created_at DESC",
        [$user_id]
    )->fetchAll();
} catch (Exception $e) {
    error_log("Applications error: " . $e->getMessage());
    $applications = [];
}

$page_title = "Profilim";
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <?php showMessages(); ?>
    
    <div class="row">
        <!-- Sol Menü -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="<?= !empty($user['image_path']) ? 'uploads/profile/' . htmlspecialchars($user['image_path']) : 'assets/img/default-avatar.png' ?>" 
                             class="rounded-circle" width="100" height="100" alt="Profil">
                    </div>
                    <h5 class="card-title">
                        <?= !empty($user['first_name']) && !empty($user['last_name']) 
                            ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])
                            : 'Kullanıcı' ?>
                    </h5>
                    <p class="text-muted mb-0">Toplam Puan: <?= isset($user['total_points']) ? (int)$user['total_points'] : 0 ?></p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="bi bi-person me-2"></i>Profil Bilgileri
                    </a>
                    <a href="#applications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-mortarboard me-2"></i>Eğitim Başvurularım
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-shield-lock me-2"></i>Güvenlik
                    </a>
                </div>
            </div>
        </div>

        <!-- Sağ İçerik -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Profil Bilgileri -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Profil Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <form action="update-profile.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ad</label>
                                        <input type="text" class="form-control" name="first_name" 
                                               value="<?= isset($user['first_name']) ? htmlspecialchars($user['first_name']) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Soyad</label>
                                        <input type="text" class="form-control" name="last_name" 
                                               value="<?= isset($user['last_name']) ? htmlspecialchars($user['last_name']) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">TC Kimlik No</label>
                                        <input type="text" class="form-control" name="tc_no" 
                                               value="<?= isset($user['tc_no']) ? htmlspecialchars($user['tc_no']) : '' ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">E-posta</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?= isset($user['email']) ? htmlspecialchars($user['email']) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Telefon</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?= isset($user['phone']) ? htmlspecialchars($user['phone']) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Doğum Tarihi</label>
                                        <input type="date" class="form-control" name="birth_date" 
                                               value="<?= isset($user['birth_date']) && $user['birth_date'] ? date('Y-m-d', strtotime($user['birth_date'])) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cinsiyet</label>
                                        <select class="form-select" name="gender">
                                            <option value="">Seçiniz</option>
                                            <option value="Erkek" <?= isset($user['gender']) && $user['gender'] == 'Erkek' ? 'selected' : '' ?>>Erkek</option>
                                            <option value="Kadın" <?= isset($user['gender']) && $user['gender'] == 'Kadın' ? 'selected' : '' ?>>Kadın</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">İlçe</label>
                                        <input type="text" class="form-control" 
                                               value="<?= isset($user['district_name']) ? htmlspecialchars($user['district_name']) : '' ?>" readonly>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i>Değişiklikleri Kaydet
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Diğer tablar aynı kalacak -->
                
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var triggerTabList = [].slice.call(document.querySelectorAll('.list-group-item'))
    triggerTabList.forEach(function(triggerEl) {
        new bootstrap.Tab(triggerEl)
    });
});
</script>

<?php include 'includes/footer.php'; ?>