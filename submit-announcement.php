<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database instance'ı al
$db = Database::getInstance();

$title = "Duyuru Bildir";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Form doğrulama
    if (empty($title)) {
        $error = 'Başlık alanı zorunludur.';
    } elseif (empty($description)) {
        $error = 'Açıklama alanı zorunludur.';
    } else {
        // Varsayılan resim yolu
        $image_path = 'default-announcement.jpg'; // Varsayılan resim

        // Resim yükleme işlemi
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                $error = 'Geçersiz dosya formatı. Sadece jpg, jpeg, png ve gif dosyaları yüklenebilir.';
            } else {
                $newname = uniqid('announcement_') . '.' . $ext;
                $destination = 'uploads/points/announcements/' . $newname;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_path = $newname;
                } else {
                    $error = 'Dosya yükleme hatası!';
                }
            }
        }
        
        if (empty($error)) {
            try {
                // Duyuruyu veritabanına ekle
                $db->query(
                    "INSERT INTO projects (title, description, image_path, status, user_id, created_at) 
                     VALUES (?, ?, ?, 'pending', ?, NOW())",
                    [$title, $description, $image_path, $_SESSION['user_id']]
                );
                
                // Bildirim geçmişine ekle
                $submission_id = $db->lastInsertId();
                $db->query(
                    "INSERT INTO submission_history (user_id, submission_type, submission_id, status) 
                     VALUES (?, 'announcement', ?, 'pending')",
                    [$_SESSION['user_id'], $submission_id]
                );
                
                $success = 'Duyurunuz başarıyla gönderildi. Onaylandıktan sonra yayınlanacaktır.';
                
                // Formu temizle
                $title = '';
                $description = '';
            } catch (Exception $e) {
                $error = 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
                error_log($e->getMessage());
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bullhorn"></i> Duyuru Bildir
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Başlık</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($title ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="5" required><?= htmlspecialchars($description ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Görsel (İsteğe bağlı)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Desteklenen formatlar: JPG, JPEG, PNG, GIF</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Duyurunuz onaylandıktan sonra 50 puan kazanacaksınız!
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gönder
                            </button>
                            <a href="collect-points.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Geri Dön
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>