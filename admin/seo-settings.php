<?php


// Hata gösterimi için
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantısı
$host = "localhost";
$dbname = "gencsanliurfa_com_pclab";
$username = "gencs_anliurfa";
$password = "Sekiztane1*";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}

// Silme işlemi
if (isset($_POST['delete']) && isset($_POST['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM seo_settings WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $message = "SEO ayarı başarıyla silindi!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Silme hatası: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    try {
        // XSS koruması için verileri temizle
        $page_id = strip_tags($_POST['page_identifier']);
        $title = strip_tags($_POST['title']);
        $description = strip_tags($_POST['description']);
        $keywords = strip_tags($_POST['keywords']);

        $stmt = $pdo->prepare("
            INSERT INTO seo_settings (page_identifier, title, description, keywords) 
            VALUES (:page_id, :title, :description, :keywords)
            ON DUPLICATE KEY UPDATE 
            title = VALUES(title),
            description = VALUES(description),
            keywords = VALUES(keywords)
        ");
        
        $stmt->execute([
            'page_id' => $page_id,
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords
        ]);

        $message = "SEO ayarları başarıyla kaydedildi!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Hata: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Mevcut ayarları getir
try {
    $seo_settings = $pdo->query("SELECT * FROM seo_settings ORDER BY page_identifier ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = "Veri çekme hatası: " . $e->getMessage();
    $messageType = "danger";
    $seo_settings = [];
}

// Admin header'ı dahil et
include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row">
       

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">SEO Ayarları</h1>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
            <?php endif; ?>

            <!-- SEO Ayarları Formu -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">SEO Ayarı Ekle/Düzenle</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Sayfa Seçin</label>
                            <select name="page_identifier" class="form-select" required>
                                <option value="">Sayfa Seçin</option>
                                <option value="home">Ana Sayfa</option>
                                <option value="about">Hakkımızda</option>
                                <option value="trainings">Eğitimler</option>
                                <option value="contact">İletişim</option>
                                <option value="news">Haberler</option>
                                <option value="gallery">Galeri</option>
                                <option value="projects">Projeler</option>
                                <option value="units">Birimler</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sayfa Başlığı (Title)</label>
                            <input type="text" name="title" class="form-control" maxlength="70" required>
                            <div class="form-text">Kalan karakter: 70</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Meta Açıklama (Description)</label>
                            <textarea name="description" class="form-control" maxlength="160" rows="3"></textarea>
                            <div class="form-text">Kalan karakter: 160</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Anahtar Kelimeler (Keywords)</label>
                            <input type="text" name="keywords" class="form-control">
                            <div class="form-text">Virgülle ayırarak yazın</div>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">Formu Temizle</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mevcut SEO Ayarları Tablosu -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Mevcut SEO Ayarları</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Sayfa</th>
                                    <th>Başlık</th>
                                    <th>Açıklama</th>
                                    <th>Anahtar Kelimeler</th>
                                    <th style="width: 150px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seo_settings as $setting): ?>
                                <tr>
                                    <td><?= htmlspecialchars($setting['page_identifier'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($setting['title'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($setting['description'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($setting['keywords'] ?? '') ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary edit-seo" 
                                                    data-id="<?= $setting['id'] ?>"
                                                    data-page="<?= htmlspecialchars($setting['page_identifier'] ?? '') ?>"
                                                    data-title="<?= htmlspecialchars($setting['title'] ?? '') ?>"
                                                    data-description="<?= htmlspecialchars($setting['description'] ?? '') ?>"
                                                    data-keywords="<?= htmlspecialchars($setting['keywords'] ?? '') ?>">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Bu SEO ayarını silmek istediğinizden emin misiniz?');">
                                                <input type="hidden" name="id" value="<?= $setting['id'] ?>">
                                                <input type="hidden" name="delete" value="1">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Sil
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Düzenleme butonuna tıklandığında formu doldur
document.querySelectorAll('.edit-seo').forEach(button => {
    button.addEventListener('click', function() {
        const form = document.querySelector('form');
        form.querySelector('[name="page_identifier"]').value = this.dataset.page;
        form.querySelector('[name="title"]').value = this.dataset.title;
        form.querySelector('[name="description"]').value = this.dataset.description;
        form.querySelector('[name="keywords"]').value = this.dataset.keywords;
        form.querySelector('button[type="submit"]').scrollIntoView({ behavior: 'smooth' });
        updateCharacterCounts();
    });
});

// Formu temizle
function clearForm() {
    const form = document.querySelector('form');
    form.reset();
    form.querySelector('[name="page_identifier"]').focus();
    updateCharacterCounts();
}

// Karakter sayısı kontrolü
function updateCharacterCounts() {
    const title = document.querySelector('[name="title"]');
    const description = document.querySelector('[name="description"]');
    
    title.nextElementSibling.textContent = `Kalan karakter: ${70 - title.value.length}`;
    description.nextElementSibling.textContent = `Kalan karakter: ${160 - description.value.length}`;
}

document.querySelector('[name="title"]').addEventListener('input', function() {
    this.nextElementSibling.textContent = `Kalan karakter: ${70 - this.value.length}`;
});

document.querySelector('[name="description"]').addEventListener('input', function() {
    this.nextElementSibling.textContent = `Kalan karakter: ${160 - this.value.length}`;
});

// Sayfa yüklendiğinde karakter sayılarını güncelle
document.addEventListener('DOMContentLoaded', updateCharacterCounts);
</script>

<?php include('includes/footer.php'); ?>