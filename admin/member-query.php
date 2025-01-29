<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkLogin(); // User veya Admin olabilir

$db = Database::getInstance();
$member = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_type = $_POST['search_type'];
    $search_value = filter_input(INPUT_POST, 'search_value', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($search_value) {
        if ($search_type == 'tc') {
            $member = $db->query("SELECT * FROM members WHERE tc_no = ?", [$search_value])->fetch();
        } else {
            $member = $db->query("SELECT * FROM members WHERE member_no = ?", [$search_value])->fetch();
        }

        if (!$member) {
            setError('Üye bulunamadı!');
        }
    } else {
        setError('Lütfen arama değeri giriniz.');
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Üye Sorgulama</h2>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="search_type" class="form-label">Arama Türü</label>
                        <select class="form-control" id="search_type" name="search_type" required>
                            <option value="tc">TC Kimlik No</option>
                            <option value="member">Üye No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="search_value" class="form-label">Arama Değeri</label>
                        <input type="text" class="form-control" id="search_value" name="search_value" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Sorgula</button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($member): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Üye Bilgileri</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Üye No</th>
                            <td><?= clean($member['member_no']) ?></td>
                        </tr>
                        <tr>
                            <th>TC Kimlik No</th>
                            <td><?= clean($member['tc_no']) ?></td>
                        </tr>
                        <tr>
                            <th>Ad Soyad</th>
                            <td><?= clean($member['first_name'] . ' ' . $member['last_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Adres</th>
                            <td><?= clean($member['address']) ?></td>
                        </tr>
                        <tr>
                            <th>Vergi Levhası</th>
                            <td>
                                <?= $member['tax_paper'] ?>
                                <?php if ($member['tax_paper'] == 'var' && $member['tax_paper_file']): ?>
                                    <br>
                                    <a href="<?= SITE_URL ?>/uploads/tax_papers/<?= $member['tax_paper_file'] ?>" 
                                       target="_blank" class="btn btn-sm btn-info mt-1">
                                        Dosyayı Görüntüle
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Oda Kaydı</th>
                            <td>
                                <?= $member['room_registration'] ?>
                                <?php if ($member['room_registration'] == 'var' && $member['room_registration_file']): ?>
                                    <br>
                                    <a href="<?= SITE_URL ?>/uploads/room_registrations/<?= $member['room_registration_file'] ?>" 
                                       target="_blank" class="btn btn-sm btn-info mt-1">
                                        Dosyayı Görüntüle
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Peşinat Tutarı</th>
                            <td><?= number_format($member['deposit_amount'], 2, ',', '.') ?> TL</td>
                        </tr>
                        <tr>
                            <th>Kayıt Tarihi</th>
                            <td><?= date('d.m.Y H:i', strtotime($member['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchType = document.getElementById('search_type');
    const searchValue = document.getElementById('search_value');

    searchType.addEventListener('change', function() {
        if (this.value === 'tc') {
            searchValue.setAttribute('maxlength', '11');
            searchValue.setAttribute('pattern', '[0-9]{11}');
            searchValue.placeholder = 'TC Kimlik No giriniz';
        } else {
            searchValue.removeAttribute('maxlength');
            searchValue.removeAttribute('pattern');
            searchValue.placeholder = 'Üye No giriniz';
        }
    });

    // Sayfa yüklendiğinde TC seçili ise
    if (searchType.value === 'tc') {
        searchValue.setAttribute('maxlength', '11');
        searchValue.setAttribute('pattern', '[0-9]{11}');
        searchValue.placeholder = 'TC Kimlik No giriniz';
    }
});
</script>

<?php include 'includes/footer.php'; ?> 