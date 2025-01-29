<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// URL'den üye ID'sini al
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Üye bilgilerini getir
$member = $db->query("SELECT * FROM members WHERE id = ?", [$member_id])->fetch();

// Üye bulunamadıysa ana sayfaya yönlendir
if (!$member) {
    setError('Üye bulunamadı.');
    redirect('member-list.php');
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars($_POST['address'], ENT_QUOTES, 'UTF-8');
    $deposit_amount = (float)$_POST['deposit_amount'];
    $status = isset($_POST['status']) ? 1 : 0;
    $notes = htmlspecialchars($_POST['notes'], ENT_QUOTES, 'UTF-8');

    // Validasyon
    if (empty($first_name) || empty($last_name) || empty($phone)) {
        setError('Lütfen gerekli alanları doldurun.');
    } else {
        // Güncelleme sorgusu
        $update = $db->query(
            "UPDATE members SET 
                first_name = ?, 
                last_name = ?, 
                phone = ?, 
                email = ?, 
                address = ?, 
                deposit_amount = ?,
                status = ?,
                notes = ?
            WHERE id = ?",
            [$first_name, $last_name, $phone, $email, $address, $deposit_amount, $status, $notes, $member_id]
        );

        if ($update) {
            setSuccess('Üye bilgileri başarıyla güncellendi.');
            redirect('member-list.php');
        } else {
            setError('Güncelleme sırasında bir hata oluştu.');
        }
    }
}

$page_title = 'Üye Düzenle';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Üye Düzenle</h2>
        <a href="member-list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="card-title mb-4">Kişisel Bilgiler</h4>
                        
                        <div class="mb-3">
                            <label for="member_no" class="form-label">Üye No</label>
                            <input type="text" class="form-control" id="member_no" 
                                   value="<?= clean($member['member_no']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="first_name" class="form-label">Ad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= clean($member['first_name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Soyad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= clean($member['last_name']) ?>" required>
                        </div>





                        <div class="mb-3">
                            <label for="address" class="form-label">Adres</label>
                            <textarea class="form-control" id="address" name="address" 
                                      rows="3"><?= clean($member['address']) ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="card-title mb-4">Üyelik Bilgileri</h4>

                        <div class="mb-3">
                            <label for="deposit_amount" class="form-label">Peşinat Tutarı</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input type="number" class="form-control" id="deposit_amount" 
                                       name="deposit_amount" step="0.01" 
                                       value="<?= $member['deposit_amount'] ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="created_at" class="form-label">Üyelik Tarihi</label>
                            <input type="text" class="form-control" id="created_at" 
                                   value="<?= date('d.m.Y', strtotime($member['created_at'])) ?>" readonly>
                        </div>




                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Kaydet
                    </button>
                    <a href="member-list.php" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.form-label {
    font-weight: 500;
}

.input-group-text {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .col-md-6:first-child {
        margin-bottom: 2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>