<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();

// Eğitim türü silme işlemi
if (isset($_GET['delete'])) {
    $id = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);
    try {
        $db->query("DELETE FROM training_types WHERE id = ?", [$id]);
        setSuccess("Eğitim türü başarıyla silindi.");
    } catch (Exception $e) {
        setError("Silme işlemi başarısız oldu. Bu eğitim türü kullanımda olabilir.");
    }
    header("Location: training-types");
    exit();
}

// Kategorileri veritabanından çek
$categories = $db->query("SELECT DISTINCT category FROM training_types WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars(trim($_POST['category']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $target_audience = htmlspecialchars(trim($_POST['target_audience']), ENT_QUOTES, 'UTF-8');
    $duration = htmlspecialchars(trim($_POST['duration']), ENT_QUOTES, 'UTF-8');

    if (!empty($name) && !empty($category)) {
        try {
            $db->query(
                "INSERT INTO training_types (name, category, description, target_audience, duration) 
                 VALUES (?, ?, ?, ?, ?)",
                [$name, $category, $description, $target_audience, $duration]
            );
            setSuccess("Eğitim türü başarıyla eklendi.");
            header("Location: training-types.php");
            exit();
        } catch (Exception $e) {
            setError("Bir hata oluştu: " . $e->getMessage());
        }
    } else {
        setError("Eğitim türü adı ve kategori gereklidir.");
    }
}

// Filtreleme için kategori parametresini kontrol et
$category_filter = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$where_clause = "";
$params = [];

if ($category_filter) {
    $where_clause = " WHERE category = ?";
    $params[] = $category_filter;
}

// Eğitim türlerini getir
$training_types = $db->query(
    "SELECT * FROM training_types" . $where_clause . " ORDER BY category, name",
    $params
)->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Yeni Eğitim Türü Ekleme Formu -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Yeni Eğitim Türü Ekle</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="" class="row">
                <div class="col-md-3 mb-3">
                    <label for="category" class="form-label">Kategori</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="category" name="category" 
                               list="categoryList" required placeholder="Kategori seçin veya yazın">
                        <datalist id="categoryList">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="name" class="form-label">Eğitim Adı</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="target_audience" class="form-label">Hedef Kitle</label>
                    <input type="text" class="form-control" id="target_audience" name="target_audience">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="duration" class="form-label">Eğitim Süresi</label>
                    <input type="text" class="form-control" id="duration" name="duration">
                </div>

                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Eğitim Türleri Listesi -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Eğitim Türleri</h4>

        </div>
        <div class="card-body">
            <?php if (empty($training_types)): ?>
                <div class="alert alert-info">
                    <?= $category_filter ? 
                        "Bu kategoride henüz eğitim türü bulunmuyor." : 
                        "Henüz eğitim türü eklenmemiş." ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Eğitim Adı</th>
                                <th>Açıklama</th>
                                <th>Hedef Kitle</th>
                                <th>Süre</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_category = '';
                            foreach ($training_types as $type): 
                                if ($current_category !== $type['category']):
                                    $current_category = $type['category'];
                            ?>

                            <?php endif; ?>
                            <tr>
                                <td><?= htmlspecialchars($type['category']) ?></td>
                                <td><?= htmlspecialchars($type['name']) ?></td>
                                <td><?= htmlspecialchars($type['description'] ?? '') ?></td>
                                <td><?= htmlspecialchars($type['target_audience'] ?? '') ?></td>
                                <td><?= htmlspecialchars($type['duration'] ?? '') ?></td>
                                <td>
                                    <a href="?delete=<?= $type['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Bu eğitim türünü silmek istediğinizden emin misiniz?')">
                                        <i class="bi bi-trash me-1"></i>Sil
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.table-secondary td {
    background-color: #f8f9fa !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
}

.table th {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .dropdown {
        width: 100%;
    }
    
    .dropdown-toggle {
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>