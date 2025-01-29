<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// İlçeleri getir
$districts = $db->query("SELECT * FROM districts ORDER BY name")->fetchAll();

// Kullanıcı bilgilerini veritabanından çek
$stmt = $db->query("SELECT * FROM users WHERE id = ?", [$user_id]);
$user = $stmt->fetch();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tc_no = filter_input(INPUT_POST, 'tc_no', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nationality = filter_input(INPUT_POST, 'nationality', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $district_id = filter_input(INPUT_POST, 'district_id', FILTER_SANITIZE_NUMBER_INT);
    $neighborhood = filter_input(INPUT_POST, 'neighborhood', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Diğer uyruk kontrolü
    if ($nationality === 'Diğer') {
        $other_nationality = filter_input(INPUT_POST, 'other_nationality', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!empty($other_nationality)) {
            $nationality = $other_nationality;
        }
    }

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
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Mevcut şifreniz yanlış.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Yeni şifreler eşleşmiyor.";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "Şifre en az 6 karakter olmalıdır.";
        }
    }

    // Hata yoksa güncelleme yap
    if (empty($errors)) {
        try {
            // Users tablosunu güncelle
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $db->query(
                    "UPDATE users SET 
                    username = ?, 
                    email = ?, 
                    password = ?,
                    first_name = ?,
                    last_name = ?,
                    tc_no = ?,
					gender = ?,
                    birth_date = ?,
                    nationality = ?,
                    district_id = ?,
                    neighborhood = ?,
                    phone = ?
                    WHERE id = ?",
                    [
                        $username, $email, $hashed_password,
                        $first_name, $last_name, $tc_no, $gender,
                        $birth_date, $nationality, $district_id,
                        $neighborhood, $phone, $user_id
                    ]
                );
            } else {
                $db->query(
                    "UPDATE users SET 
                    username = ?, 
                    email = ?,
                    first_name = ?,
                    last_name = ?,
                    tc_no = ?,
					gender = ?,
                    birth_date = ?,
                    nationality = ?,
                    district_id = ?,
                    neighborhood = ?,
                    phone = ?
                    WHERE id = ?",
                    [
                        $username, $email,
                        $first_name, $last_name, $tc_no, $gender,
                        $birth_date, $nationality, $district_id,
                        $neighborhood, $phone, $user_id
                    ]
                );
            }

            $_SESSION['username'] = $username;
            setSuccess('Profiliniz başarıyla güncellendi!');
            header('Location: profile.php');
            exit();
        } catch (Exception $e) {
            setError('Bir hata oluştu: ' . $e->getMessage());
        }
    } else {
        foreach ($errors as $error) {
            setError($error);
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mt-5">
                <div class="card-header">
                    <h4 class="mb-0">Profil Düzenle</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <h5 class="mb-3">Hesap Bilgileri</h5>
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <h5 class="mb-3 mt-4">Kişisel Bilgiler</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ad</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Soyad</label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
					 <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TC Kimlik No</label>
                            <input type="text" name="tc_no" class="form-control" 
                                   pattern="[0-9]{11}" maxlength="11" 
                                   value="<?php echo htmlspecialchars($user['tc_no'] ?? ''); ?>" required>
                        </div>					
						
						<div class="col-md-6 mb-3">
						    <label>Cinsiyet</label>
						    <select name="gender" class="form-control">
						        <option value="">Seçiniz</option>
						        <option value="Kadın" <?php echo ($user['gender'] == 'Kadın') ? 'selected' : ''; ?>>Kadın</option>
						        <option value="Erkek" <?php echo ($user['gender'] == 'Erkek') ? 'selected' : ''; ?>>Erkek</option>
						    </select>
						</div>
 					</div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Doğum Tarihi</label>
                                <input type="date" name="birth_date" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>" required>
                            </div>
<div class="col-md-6 mb-3">
    <label class="form-label">Uyruk</label>
    <select name="nationality" class="form-select" required>
        <option value="">Seçiniz</option>
        <?php
        $nationalities = ['Turk', 'Suriyeli', 'Diger'];
        foreach ($nationalities as $nat) {
            $selected = ($user['nationality'] == $nat) ? 'selected' : '';
            echo "<option value=\"{$nat}\" {$selected}>" . htmlspecialchars($nat) . "</option>";
        }
        ?>
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
                                            <?php echo ($user['district_id'] ?? '') == $district['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($district['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mahalle</label>
                                <input type="text" name="neighborhood" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['neighborhood'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>

                        <h5 class="mb-3 mt-4">Şifre Değiştir (İsteğe bağlı)</h5>
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

                        <button type="submit" class="btn btn-primary">Profili Güncelle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nationalitySelect = document.querySelector('select[name="nationality"]');
    const otherNationalityDiv = document.getElementById('otherNationalityDiv');
    const otherNationalityInput = document.getElementById('otherNationality');

    function toggleOtherNationality() {
        if (nationalitySelect.value === 'Diğer') {
            otherNationalityDiv.style.display = 'block';
            otherNationalityInput.required = true;
        } else {
            otherNationalityDiv.style.display = 'none';
            otherNationalityInput.required = false;
            otherNationalityInput.value = '';
        }
    }

    nationalitySelect.addEventListener('change', toggleOtherNationality);
    toggleOtherNationality(); // Sayfa yüklendiğinde kontrol et
});
</script>

<?php include 'includes/footer.php'; ?>