<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

$training_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Eğitim bilgilerini getir
$training = $db->query(
    "SELECT t.*, u.name as unit_name 
     FROM trainings t 
     JOIN units u ON t.unit_id = u.id 
     WHERE t.id = ? AND t.is_active = 1 AND t.end_date >= CURDATE()",
    [$training_id]
)->fetch();

if (!$training) {
    setError('Eğitim bulunamadı veya aktif değil.');
    redirect('units.php');
}

// İlçeleri getir
$districts = $db->query("SELECT * FROM districts ORDER BY name")->fetchAll();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tc_no = filter_input(INPUT_POST, 'tc_no', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nationality = filter_input(INPUT_POST, 'nationality', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $district_id = filter_input(INPUT_POST, 'district_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $neighborhood = filter_input(INPUT_POST, 'neighborhood', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // TC No kontrolü
    if (!preg_match('/^[0-9]{11}$/', $tc_no)) {
        setError('Geçersiz TC Kimlik Numarası.');
    } else {
        // TC kimlik numarasıyla daha önce kayıt olup olmadığını kontrol et
        $existing_record = $db->query(
            "SELECT * FROM training_registrations WHERE tc_no = ? AND training_id = ?",
            [$tc_no, $training_id]
        )->fetch();

        if ($existing_record) {
            setError('Bu TC Kimlik Numarası ile zaten kayıt yapılmış.');
        } else {
            try {
                $db->query(
                    "INSERT INTO training_registrations 
                    (training_id, first_name, last_name, tc_no, birth_date, nationality, 
                    district_id, neighborhood, phone) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$training_id, $first_name, $last_name, $tc_no, $birth_date, 
                    $nationality, $district_id, $neighborhood, $phone]
                );
                
                setSuccess('Eğitim kaydınız başarıyla oluşturuldu.');
                redirect('unit-detail.php?id=' . $training['unit_id']);
            } catch (Exception $e) {
                setError('Kayıt oluşturulurken bir hata oluştu.');
            }
        }
    }
}

$page_title = "Eğitim Kaydı";
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Eğitim Kaydı</h4>
                </div>
                <div class="card-body">
                    <h5 class="mb-3"><?= htmlspecialchars($training['title']) ?></h5>
                    <p class="text-muted mb-4">
                        <?= htmlspecialchars($training['unit_name']) ?><br>
                        <?= date('d.m.Y', strtotime($training['start_date'])) ?> - 
                        <?= date('d.m.Y', strtotime($training['end_date'])) ?>
                    </p>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ad</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Soyad</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">TC Kimlik No</label>
                            <input type="text" name="tc_no" class="form-control" 
                                   pattern="[0-9]{11}" maxlength="11" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Doğum Tarihi</label>
                                <input type="date" name="birth_date" class="form-control" required>
                            </div>
                           
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Uyruk <span class="text-danger">*</span></label>
                                    <select name="nationality" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                        <option value="Türk">Türk</option>
                                        <option value="Suriyeli">Suriyeli</option>
                                        <option value="Diğer">Diğer</option>
                                    </select>
                                    <div id="otherNationalityDiv" class="mt-2" style="display: none;">
                                        <input type="text" id="otherNationality" class="form-control" 
                                               placeholder="Lütfen uyruğunuzu belirtiniz">
                                    </div>
                                </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">İlçe</label>
                                <select name="district_id" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($districts as $district): ?>
                                    <option value="<?= $district['id'] ?>">
                                        <?= htmlspecialchars($district['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mahalle</label>
                                <input type="text" name="neighborhood" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 