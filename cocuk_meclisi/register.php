<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        setError('Şifreler eşleşmiyor!');
    } else {
        $db = Database::getInstance();
        
        $check = $db->query("SELECT COUNT(*) as count FROM users WHERE email = ?", [$email])->fetch();
        
        if ($check['count'] > 0) {
            setError('Bu e-posta adresi zaten kullanılıyor!');
        } else {
            $check = $db->query("SELECT COUNT(*) as count FROM users WHERE username = ?", [$username])->fetch();
            
            if ($check['count'] > 0) {
                setError('Bu kullanıcı adı zaten kullanılıyor!');
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $db->query(
                    "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')",
                    [$username, $email, $hashed_password]
                );

                if ($stmt) {
                    setSuccess('Kayıt başarılı! Şimdi giriş yapabilirsiniz.');
                    header('Location: login.php');
                    exit();
                } else {
                    setError('Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.');
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h4 class="mb-0">Kayıt Ol</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                    </form>
                    <hr>
                    <p class="mb-0">Zaten hesabınız var mı? <a href="login.php">Giriş Yapın</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 