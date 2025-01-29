<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

checkLogin();

$db = Database::getInstance();

// İlçeleri getir
$districts = $db->query("SELECT * FROM districts ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district_id = (int)($_POST['district_id'] ?? 0);

    $errors = [];

    // Validasyonlar
    if (empty($title)) {
        $errors[] = "Başlık alanı zorunludur.";
    }

    if (empty($description)) {
        $errors[] = "Açıklama alanı zorunludur.";
    }

    if (empty($address)) {
        $errors[] = "Adres alanı zorunludur.";
    }

    if ($district_id <= 0) {
        $errors[] = "Lütfen bir ilçe seçin.";
    }

    // Dosya yükleme kontrolü
    $image_path = 'default-place.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Sadece JPG ve PNG dosyaları yükleyebilirsiniz.";
        }

        if ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Dosya boyutu 5MB'dan büyük olamaz.";
        }

        if (empty($errors)) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $image_path = generateRandomString(20) . '.' . $ext;
            $target_path = 'uploads/points/places/' . $image_path;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $errors[] = "Dosya yüklenirken bir hata oluştu.";
                $image_path = 'default-place.jpg';
            }
        }
    }

    if (empty($errors)) {
        try {
            $db->query(
                "INSERT INTO places (title, description, address, district_id, image_path, user_id, status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'pending')",
                [$title, $description, $address, $district_id, $image_path, $_SESSION['user_id']]
            );

            // Başarılı mesajı göster ve yönlendir
            setSuccess("Mekan öneriniz başarıyla alındı. Onaylandıktan sonra yayınlanacaktır.");
            header("Location: places.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Bir hata oluştu, lütfen tekrar deneyin.";
            // Yüklenen dosyayı sil
            if ($image_path != 'default-place.jpg') {
                @unlink('uploads/points/places/' . $image_path);
            }
        }
    }

    setError($errors);
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt"></i> Yeni Mekan Öner
                    </h5>
                </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Mekan Adı <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="district_id" class="form-label">İlçe <span class="text-danger">*</span></label>
                            <select class="form-select" id="district_id" name="district_id" required>
                                <option value="">İlçe Seçin</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?= $district['id'] ?>" 
                                            <?= (isset($_POST['district_id']) && $_POST['district_id'] == $district['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($district['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Açık Adres <span class="text-danger">*</span></label>
                            <textarea class="form-control" 
                                      id="address" 
                                      name="address" 
                                      rows="2" 
                                      required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                            <div class="form-text">Mekanın tam adresini yazın.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama <span class="text-danger">*</span></label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                            <div class="form-text">Mekan hakkında detaylı bilgi verin.</div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Görsel</label>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <input type="file" 
                                           class="form-control" 
                                           id="image" 
                                           name="image" 
                                           accept="image/jpeg,image/png"
                                           onchange="previewImage(this);">
                                    <div class="form-text">
                                        Maksimum 5MB, JPG veya PNG formatında.<br>
                                        Görsel yüklemezseniz varsayılan görsel kullanılacaktır.
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="image-preview mt-2">
                                        <img src="assets/img/default-place.jpg" 
                                             id="imagePreview" 
                                             class="img-fluid rounded" 
                                             alt="Görsel önizleme"
                                             style="max-height: 150px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Gönder
                            </button>
                            <a href="places.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Geri Dön
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const file = input.files[0];
    
    if (file) {
        // Dosya boyutu kontrolü (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Dosya boyutu 5MB\'dan büyük olamaz!');
            input.value = '';
            return;
        }

        // Dosya tipi kontrolü
        if (!file.type.match('image/jpeg') && !file.type.match('image/png')) {
            alert('Sadece JPG ve PNG dosyaları yükleyebilirsiniz!');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(file);
    } else {
        preview.src = 'assets/img/default-place.jpg';
    }
}

// Sayfa yüklendiğinde mevcut görseli göster (düzenleme sayfası için)
document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('imagePreview');
    const currentImage = '<?= isset($place) && $place['image_path'] ? "uploads/points/places/" . htmlspecialchars($place['image_path']) : "assets/img/default-place.jpg" ?>';
    preview.src = currentImage;
});
</script>

<style>
.image-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.25rem;
    background: #f8f9fa;
}

.image-preview img {
    display: block;
    margin: 0 auto;
}
</style>

<?php include 'includes/footer.php'; ?> 