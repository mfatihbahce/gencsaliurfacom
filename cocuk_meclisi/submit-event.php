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

$title = "Etkinlik Bildir";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    
    // Form doğrulama
    if (empty($title)) {
        $error = 'Başlık alanı zorunludur.';
    } elseif (empty($description)) {
        $error = 'Açıklama alanı zorunludur.';
    } elseif (empty($event_date)) {
        $error = 'Etkinlik tarihi zorunludur.';
    } else {
        // Varsayılan resim yolu
        $image_path = 'default-event.jpg';

        // Resim yükleme işlemi
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                $error = 'Geçersiz dosya formatı. Sadece jpg, jpeg, png ve gif dosyaları yüklenebilir.';
            } else {
                $newname = uniqid('event_') . '.' . $ext;
                $destination = 'uploads/points/events/' . $newname;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_path = $newname;
                } else {
                    $error = 'Dosya yükleme hatası!';
                }
            }
        }
        
        if (empty($error)) {
            try {
                // Etkinliği veritabanına ekle
                $db->query(
                    "INSERT INTO news (title, description, image_path, event_date, status, user_id, created_at) 
                     VALUES (?, ?, ?, ?, 'pending', ?, NOW())",
                    [$title, $description, $image_path, $event_date, $_SESSION['user_id']]
                );
                
                // Bildirim geçmişine ekle
                $submission_id = $db->lastInsertId();
                $db->query(
                    "INSERT INTO submission_history (user_id, submission_type, submission_id, status) 
                     VALUES (?, 'event', ?, 'pending')",
                    [$_SESSION['user_id'], $submission_id]
                );
                
                $success = 'Etkinliğiniz başarıyla gönderildi. Onaylandıktan sonra yayınlanacaktır.';
                
                // Formu temizle
                $title = '';
                $description = '';
                $event_date = '';
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
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Etkinlik Bildir
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
                            <label for="title" class="form-label">Etkinlik Adı</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($title ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Etkinlik Açıklaması</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="5" required><?= htmlspecialchars($description ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="event_date" class="form-label">Etkinlik Tarihi</label>
                            <input type="datetime-local" class="form-control" id="event_date" name="event_date" 
                                   value="<?= htmlspecialchars($event_date ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Etkinlik Görseli (İsteğe bağlı)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Desteklenen formatlar: JPG, JPEG, PNG, GIF</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Etkinliğiniz onaylandıktan sonra 50 puan kazanacaksınız!
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
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