<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php'; // functions.php'yi ekledik

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // FILTER_SANITIZE_STRING yerine htmlspecialchars kullanıyoruz
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');

    if ($name && $email && $subject && $message) {
        $db = Database::getInstance();
        
        $stmt = $db->query(
            "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)",
            [$name, $email, $subject, $message]
        );

        if ($stmt) {
            setSuccess('Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.');
            $success = true;
        } else {
            setError('Mesaj gönderilirken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    } else {
        setError('Lütfen tüm alanları doldurun.');
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<br><br>
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <h6 class="text-primary fw-bold text-uppercase">İletişim</h6>
        <h2 class="display-5 fw-bold">Bizimle İletişime Geçin</h2>
        <div class="divider mx-auto"></div>
    </div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">İletişim Formu</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" class="contact-form">
                            <div class="mb-3">
                                <label for="name" class="form-label">Adınız Soyadınız</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresiniz</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Konu</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Mesajınız</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Gönder</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">İletişim Bilgileri</h4>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-geo-alt"></i> Adres: Bamyasuyu Mahallesi 154. Sokak No:2 Haliliye / Şanlıurfa</p>
                    <p><i class="bi bi-telephone"></i> Telefon: +90 533 317 8197</p>
                    <p><i class="bi bi-envelope"></i> E-posta: info@gencsanliurfa.com</p>
                    <div class="mt-4">
                        <h5>Sosyal Medya</h5>
                        <div class="social-links">
                            <a href="#" class="me-2"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="me-2"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="me-2"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="me-2"><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>