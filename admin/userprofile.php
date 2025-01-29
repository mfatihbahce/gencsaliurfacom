<?php
// Hata raporlamayı açalım
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Session kontrolü
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database bağlantısı
$db = Database::getInstance();

// Kullanıcı kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Debug için session bilgilerini göster
echo "<pre>SESSION: ";
print_r($_SESSION);
echo "</pre>";

try {
    // SQL sorgusunu debug et
    $sql = "SELECT * FROM users WHERE id = ?";
    echo "<!-- SQL Query: " . $sql . " -->";
    echo "<!-- User ID: " . $user_id . " -->";
    
    // Kullanıcı bilgilerini veritabanından çek
    $user = $db->query($sql, [$user_id])->fetch();
    
    // Sonucu debug et
    echo "<pre>User Data: ";
    print_r($user);
    echo "</pre>";

    if (!$user) {
        die("Kullanıcı bulunamadı! User ID: {$user_id}");
    }

    // İlçeleri getir
    $districts = $db->query("SELECT * FROM districts ORDER BY name")->fetchAll();
    
    // İlçeleri debug et
    echo "<pre>Districts: ";
    print_r($districts);
    echo "</pre>";

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tc_no = filter_input(INPUT_POST, 'tc_no', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nationality = filter_input(INPUT_POST, 'nationality', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $district_id = filter_input(INPUT_POST, 'district_id', FILTER_SANITIZE_NUMBER_INT);
    $neighborhood = filter_input(INPUT_POST, 'neighborhood', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // TC No kontrolü
    if (!preg_match('/^[0-9]{11}$/', $tc_no)) {
        $errors[] = 'Geçersiz TC Kimlik Numarası.';
    }

    // E-posta kontrolü
    if ($email !== $user['email']) {
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Bu e-posta adresi zaten kullanılıyor.";
        }
    }

    // Şifre değişikliği kontrolü
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Mevcut şifre yanlış.";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "Yeni şifre en az 6 karakter olmalıdır.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Yeni şifreler eşleşmiyor.";
        }
    }

    // Hata yoksa güncelle
    if (empty($errors)) {
        try {
            $sql = "UPDATE users SET 
                    email = ?, 
                    first_name = ?, 
                    last_name = ?, 
                    tc_no = ?, 
                    gender = ?, 
                    birth_date = ?, 
                    nationality = ?, 
                    district_id = ?, 
                    neighborhood = ?, 
                    phone = ?";
            
            $params = [
                $email,
                $first_name,
                $last_name,
                $tc_no,
                $gender,
                $birth_date,
                $nationality,
                $district_id,
                $neighborhood,
                $phone
            ];

            // Şifre değişikliği varsa
            if (!empty($new_password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $user_id;

            $db->query($sql, $params);
            $_SESSION['success'] = 'Profil başarıyla güncellendi.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $errors[] = "Güncelleme sırasında bir hata oluştu: " . $e->getMessage();
        }
    }
}

$page_title = "Profil Düzenle";
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0">Profil Bilgileri</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <?php foreach ($errors as $error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly>
                            <small class="text-muted">Kullanıcı adı değiştirilemez.</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ad</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Soyad</label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">TC Kimlik No</label>
                                <input type="text" name="tc_no" class="form-control" 
                                       pattern="[0-9]{11}" maxlength="11" 
                                       value="<?= htmlspecialchars($user['tc_no'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Cinsiyet</label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    <option value="Kadın" <?= ($user['gender'] ?? '') == 'Kadın' ? 'selected' : '' ?>>Kadın</option>
                                    <option value="Erkek" <?= ($user['gender'] ?? '') == 'Erkek' ? 'selected' : '' ?>>Erkek</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Doğum Tarihi</label>
                                <input type="date" name="birth_date" class="form-control" 
                                       value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Uyruk</label>
                                <select name="nationality" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    <option value="Turk" <?= ($user['nationality'] ?? '') == 'Turk' ? 'selected' : '' ?>>T.C.</option>
                                    <option value="Suriyeli" <?= ($user['nationality'] ?? '') == 'Suriyeli' ? 'selected' : '' ?>>Suriyeli</option>
                                    <option value="Diger" <?= ($user['nationality'] ?? '') == 'Diger' ? 'selected' : '' ?>>Diğer</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">İlçe</label>
                                <select name="district_id" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($districts as $district): ?>
                                        <option value="<?= $district['id'] ?>" 
                                                <?= ($user['district_id'] ?? '') == $district['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($district['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mahalle</label>
                                <input type="text" name="neighborhood" class="form-control" 
                                       value="<?= htmlspecialchars($user['neighborhood'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>

                        <hr>

                        <h5 class="mb-3">Şifre Değiştir (İsteğe bağlı)</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mevcut Şifre</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Profili Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>