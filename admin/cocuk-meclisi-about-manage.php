<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Mevcut about içeriğini getir
$cocuk_meclisi_about = $db->query("SELECT * FROM cocuk_meclisi_about WHERE id = 1")->fetch();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $content = $_POST['content']; // CKEditor kullanıldığı için HTML içerik korunmalı
    $mission = htmlspecialchars($_POST['mission'], ENT_QUOTES, 'UTF-8');
    $vision = htmlspecialchars($_POST['vision'], ENT_QUOTES, 'UTF-8');

    // Resim yükleme işlemi
    $image_path = $cocuk_meclisi_about['image_path']; // Varsayılan olarak mevcut resmi kullan
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'about_' . time() . '.' . $ext;
            $upload_path = '../uploads/about/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Eski resmi sil
                if ($cocuk_meclisi_about['image_path'] && file_exists('../uploads/about/' . $cocuk_meclisi_about['image_path'])) {
                    unlink('../uploads/about/' . $cocuk_meclisi_about['image_path']);
                }
                $image_path = $new_filename;
            }
        }
    }

    // Güncelleme sorgusu
    $update = $db->query(
        "UPDATE cocuk_meclisi_about SET 
            title = ?, 
            content = ?, 
            mission = ?, 
            vision = ?, 
            image_path = ?
        WHERE id = 1",
        [$title, $content, $mission, $vision, $image_path]
    );

    if ($update) {
        $_SESSION['success'] = 'Hakkımızda sayfası başarıyla güncellendi.';
        header("Location: cocuk-meclisi-about-manage.php");
        exit;
    } else {
        $_SESSION['error'] = 'Güncelleme sırasında bir hata oluştu.';
    }
}

$page_title = 'Hakkımızda Sayfası Yönetimi';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Hakkımızda Sayfası Yönetimi</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Başlık</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= clean($cocuk_meclisi_about['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">İçerik</label>
                    <textarea class="form-control editor" id="content" name="content" 
                              rows="10"><?= $cocuk_meclisi_about['content'] ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="mission" class="form-label">Misyonumuz</label>
                            <textarea class="form-control" id="mission" name="mission" 
                                      rows="4"><?= clean($cocuk_meclisi_about['mission']) ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vision" class="form-label">Vizyonumuz</label>
                            <textarea class="form-control" id="vision" name="vision" 
                                      rows="4"><?= clean($cocuk_meclisi_about['vision']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Görsel</label>
                    <?php if ($cocuk_meclisi_about['image_path']): ?>
                        <div class="mb-2">
                            <img src="<?= SITE_URL ?>/uploads/about/<?= $about['image_path'] ?>" 
                                 alt="Mevcut görsel" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="text-muted">Yeni bir görsel yüklemezseniz mevcut görsel korunacaktır.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Kaydet
                </button>
            </form>
        </div>
    </div>
</div>

<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
// CKEditor başlatma
ClassicEditor
    .create(document.querySelector('.editor'), {
        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
            ]
        }
    })
    .catch(error => {
        console.error(error);
    });
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.ck-editor__editable {
    min-height: 300px;
}
</style>

<?php include 'includes/footer.php'; ?>