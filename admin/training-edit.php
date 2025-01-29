<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: training-manage.php');
    exit;
}

$training_id = $_GET['id'];

// Eğitim bilgilerini getir
$training = $db->query(
    "SELECT * FROM trainings WHERE id = ?", 
    [$training_id]
)->fetch();

if (!$training) {
    header('Location: training-manage.php');
    exit;
}

// Birimleri getir
$units = $db->query("SELECT * FROM units ORDER BY name")->fetchAll();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'unit_id' => $_POST['unit_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'deadline_date' => $_POST['deadline_date'],
            'capacity' => $_POST['capacity'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'id' => $training_id
        ];

        $db->query(
            "UPDATE trainings SET 
                unit_id = ?,
                title = ?,
                description = ?,
                start_date = ?,
                end_date = ?,
                deadline_date = ?,
                capacity = ?,
                is_active = ?
            WHERE id = ?",
            [
                $data['unit_id'],
                $data['title'],
                $data['description'],
                $data['start_date'],
                $data['end_date'],
                $data['deadline_date'],
                $data['capacity'],
                $data['is_active'],
                $data['id']
            ]
        );

        echo "<script>
            alert('Eğitim başarıyla güncellendi.');
            window.location.href = 'training-manage.php';
        </script>";
        exit;
    } catch (Exception $e) {
        echo "<script>
            alert('Eğitim güncellenirken bir hata oluştu.');
        </script>";
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Eğitim Düzenle</h2>
    <a href="training-manage.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Birim</label>
                <select name="unit_id" class="form-select" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($units as $unit): ?>
                    <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $training['unit_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($unit['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Eğitim Adı</label>
                <input type="text" name="title" class="form-control" 
                       value="<?= htmlspecialchars($training['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Açıklama</label>
                <textarea name="description" class="form-control" 
                          rows="3"><?= htmlspecialchars($training['description']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Başlangıç Tarihi</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="<?= $training['start_date'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bitiş Tarihi</label>
                    <input type="date" name="end_date" class="form-control" 
                           value="<?= $training['end_date'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Son Başvuru Tarihi</label>
                    <input type="date" name="deadline_date" class="form-control" 
                           value="<?= $training['deadline_date'] ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Kapasite</label>
                <input type="number" name="capacity" class="form-control" 
                       value="<?= $training['capacity'] ?>" required>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" 
                           id="isActive" value="1" <?= $training['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isActive">Aktif</label>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="training-manage.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tarih kontrolü için JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const deadlineDate = document.querySelector('input[name="deadline_date"]');

    // Son başvuru tarihi, başlangıç tarihinden sonra olamaz
    deadlineDate.addEventListener('change', function() {
        if (this.value > startDate.value) {
            alert('Son başvuru tarihi, eğitim başlangıç tarihinden sonra olamaz!');
            this.value = startDate.value;
        }
    });

    // Bitiş tarihi, başlangıç tarihinden önce olamaz
    endDate.addEventListener('change', function() {
        if (this.value < startDate.value) {
            alert('Bitiş tarihi, başlangıç tarihinden önce olamaz!');
            this.value = startDate.value;
        }
    });

    // Başlangıç tarihi, bitiş tarihinden sonra olamaz
    startDate.addEventListener('change', function() {
        if (this.value > endDate.value) {
            endDate.value = this.value;
        }
        // Son başvuru tarihini kontrol et
        if (deadlineDate.value > this.value) {
            deadlineDate.value = this.value;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 