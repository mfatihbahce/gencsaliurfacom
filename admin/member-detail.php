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

$page_title = 'Üye Detayı';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Üye Detayı</h2>
        <a href="member-list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title mb-4">Kişisel Bilgiler</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Üye No</th>
                            <td><?= clean($member['member_no']) ?></td>
                        </tr>
                        <tr>
                            <th>Ad Soyad</th>
                            <td><?= clean($member['first_name'] . ' ' . $member['last_name']) ?></td>
                        </tr>
                        <tr>
                            <th>TC Kimlik No</th>
                            <td><?= clean($member['tc_no']) ?></td>
                        </tr>

                        <tr>
                            <th>Adres</th>
                            <td><?= clean($member['address']) ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h4 class="card-title mb-4">Üyelik Bilgileri</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Üyelik Tarihi</th>
                            <td><?= date('d.m.Y', strtotime($member['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Peşinat Tutarı</th>
                            <td>₺<?= number_format($member['deposit_amount'], 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Durum</th>
                            <td>
                                <span class="badge bg-<?= $member['status'] ? 'success' : 'danger' ?>">
                                    <?= $member['status'] ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </td>
                        </tr>
                    </table>

                    <?php if (!empty($member['notes'])): ?>
                        <h4 class="card-title mb-3 mt-4">Notlar</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <?= nl2br(clean($member['notes'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4">
                <a href="member-edit.php?id=<?= $member['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Düzenle
                </a>
                <!--
                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $member['id'] ?>)">
                    <i class="bi bi-trash"></i> Sil
                </button>
                -->
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

@media (max-width: 768px) {
    .col-md-6:first-child {
        margin-bottom: 2rem;
    }
}
</style>

<script>
function confirmDelete(id) {
    if (confirm('Bu üyeyi silmek istediğinizden emin misiniz?')) {
        window.location.href = 'member-delete.php?id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>