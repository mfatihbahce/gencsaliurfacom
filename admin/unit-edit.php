<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

$unit_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$unit = $db->query("SELECT * FROM units WHERE id = ?", [$unit_id])->fetch();

if (!$unit) {
    setError('Birim bulunamadı.');
    redirect('unit-manage.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $working_hours = filter_input(INPUT_POST, 'working_hours', FILTER_SANITIZE_STRING);
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    try {
        $db->query(
            "UPDATE units SET name = ?, address = ?, working_hours = ?, 
             latitude = ?, longitude = ?, description = ? WHERE id = ?",
            [$name, $address, $working_hours, $latitude, $longitude, $description, $unit_id]
        );
        
        setSuccess('Birim başarıyla güncellendi.');
        redirect('unit-manage.php');
    } catch (Exception $e) {
        setError('Güncelleme sırasında bir hata oluştu.');
    }
}

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Birim Düzenle</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Birim Adı</label>
                <input type="text" name="name" class="form-control" 
                       value="<?= htmlspecialchars($unit['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Adres</label>
                <textarea name="address" class="form-control" required><?= htmlspecialchars($unit['address']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Çalışma Saatleri</label>
                <input type="text" name="working_hours" class="form-control" 
                       value="<?= htmlspecialchars($unit['working_hours']) ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Enlem</label>
                    <input type="number" name="latitude" class="form-control" step="any" 
                           value="<?= $unit['latitude'] ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Boylam</label>
                    <input type="number" name="longitude" class="form-control" step="any" 
                           value="<?= $unit['longitude'] ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Açıklama</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($unit['description']) ?></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <a href="unit-manage.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 