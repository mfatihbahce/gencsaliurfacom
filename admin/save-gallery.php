<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Yüklenen dosyayı kontrol et
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $title = $_POST['title'][0] ?? '';
        $description = $_POST['description'] ?? '';
        
        // Yükleme klasörünü kontrol et ve oluştur
        $upload_dir = '../uploads/gallery/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file = $_FILES['images'];
        
        // Dosya uzantısını kontrol et
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        $file_ext = strtolower(pathinfo($file['name'][0], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            setError("Desteklenmeyen dosya formatı");
            header("Location: gallery-manage.php");
            exit;
        }

        // Dosya boyutunu kontrol et (5MB)
        if ($file['size'][0] > 5 * 1024 * 1024) {
            setError("Dosya boyutu çok büyük");
            header("Location: gallery-manage.php");
            exit;
        }

        // Benzersiz dosya adı oluştur
        $new_filename = uniqid('gallery_') . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;

        // Dosyayı yükle
        if (move_uploaded_file($file['tmp_name'][0], $upload_path)) {
            // Görsel boyutlarını al
            list($width, $height) = getimagesize($upload_path);

            // Veritabanına kaydet
            $db = Database::getInstance();
            $insert = $db->query(
                "INSERT INTO gallery (image_path, title, created_at) 
                VALUES (?, ?, NOW())",
                [$new_filename, $title]
            );

            if ($insert) {
                setSuccess("Görsel başarıyla yüklendi.");
            } else {
                unlink($upload_path); // Dosyayı sil
                setError("Veritabanına kayıt yapılamadı.");
            }
        } else {
            setError("Dosya yüklenemedi.");
        }
    } else {
        setError("Lütfen görsel seçin.");
    }
    
    header("Location: gallery-manage.php");
    exit;
}

$page_title = 'Galeri Yükleme';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Galeri Yükleme</h2>
        <a href="gallery-manage.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="galleryForm">
                <div class="mb-3">
                    <label for="images" class="form-label">Görseller</label>
                    <input type="file" class="form-control" id="images" name="images[]" 
                           accept="image/jpeg,image/png,image/webp" multiple required>
                    <div class="form-text">
                        Desteklenen formatlar: JPG, PNG, WEBP<br>
                        Maksimum dosya boyutu: 5MB
                    </div>
                </div>

                <div id="imagePreview" class="row g-3 mb-3"></div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload"></i> Yükle
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.preview-item {
    position: relative;
}

.preview-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}

.preview-title {
    margin-top: 0.5rem;
}

.remove-preview {
    position: absolute;
    top: 10px;
    right: 25px;
    background: rgba(255,255,255,0.9);
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-preview:hover {
    background: #dc3545;
    color: white;
}
</style>

<script>
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-3 preview-item';
            
            col.innerHTML = `
                <img src="${e.target.result}" class="preview-image" alt="Preview">
                <input type="text" class="form-control preview-title" 
                       name="title[]" placeholder="Görsel başlığı">
                <button type="button" class="remove-preview" 
                        onclick="this.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            `;
            
            preview.appendChild(col);
        }
        reader.readAsDataURL(file);
    });
});
</script>

<?php include 'includes/footer.php'; ?>