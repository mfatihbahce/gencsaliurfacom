<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Debug için
error_log("Katılım formu başlatıldı");
error_log("POST Verileri: " . print_r($_POST, true));

// POST kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: etkinlikler.php');
    exit;
}

$db = Database::getInstance();

// Form verilerini al ve temizle
$etkinlik_id = isset($_POST['etkinlik_id']) ? (int)$_POST['etkinlik_id'] : 0;
$cocuk_adi = isset($_POST['cocuk_adi']) ? trim(strip_tags($_POST['cocuk_adi'])) : '';
$dogum_tarihi = isset($_POST['dogum_tarihi']) ? trim($_POST['dogum_tarihi']) : '';
$veli_adi = isset($_POST['veli_adi']) ? trim(strip_tags($_POST['veli_adi'])) : '';
$veli_telefon = isset($_POST['veli_telefon']) ? trim(strip_tags($_POST['veli_telefon'])) : '';
$veli_email = isset($_POST['veli_email']) ? trim(strip_tags($_POST['veli_email'])) : '';
$onay = isset($_POST['onay']) ? 1 : 0;

// Hata kontrolü
$errors = [];

if (!$etkinlik_id) {
    $errors[] = "Etkinlik bilgisi bulunamadı.";
}

if (empty($cocuk_adi)) {
    $errors[] = "Çocuğun adı ve soyadı gereklidir.";
}

if (empty($dogum_tarihi)) {
    $errors[] = "Doğum tarihi gereklidir.";
} else {
    // Yaş kontrolü (6-14 yaş arası)
    $dogum = new DateTime($dogum_tarihi);
    $bugun = new DateTime();
    $yas = $bugun->diff($dogum)->y;
    
    if ($yas < 6 || $yas > 14) {
        $errors[] = "Etkinliğe sadece 6-14 yaş arası çocuklar katılabilir.";
    }
}

if (empty($veli_adi)) {
    $errors[] = "Veli adı ve soyadı gereklidir.";
}

if (empty($veli_telefon)) {
    $errors[] = "Veli telefon numarası gereklidir.";
} elseif (!preg_match("/^[0-9]{10,11}$/", preg_replace("/[^0-9]/", "", $veli_telefon))) {
    $errors[] = "Geçerli bir telefon numarası giriniz.";
}

if (empty($veli_email) || !filter_var($veli_email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Geçerli bir e-posta adresi giriniz.";
}

if (!$onay) {
    $errors[] = "Katılım koşullarını onaylamanız gerekmektedir.";
}

// Etkinliği kontrol et
try {
    $etkinlik = $db->query("SELECT * FROM cocuk_meclisi_etkinlikler WHERE id = $etkinlik_id AND durum = 'aktif'")->fetch();
    
    if (!$etkinlik) {
        $errors[] = "Etkinlik bulunamadı veya katılıma kapalı.";
    } else {
        // Katılımcı sayısı kontrolü
        $mevcut_katilimci = $db->query("SELECT COUNT(*) as sayi FROM cocuk_meclisi_katilimcilar WHERE etkinlik_id = $etkinlik_id AND durum != 'reddedildi'")->fetch();
        
        if ($mevcut_katilimci['sayi'] >= $etkinlik['katilimci_sayisi']) {
            $errors[] = "Üzgünüz, etkinlik kontenjanı dolmuştur.";
        }
        
        // Aynı çocuk için mükerrer başvuru kontrolü
        $mevcut_basvuru = $db->query("SELECT COUNT(*) as sayi FROM cocuk_meclisi_katilimcilar WHERE etkinlik_id = $etkinlik_id AND cocuk_adi = '$cocuk_adi' AND dogum_tarihi = '$dogum_tarihi'")->fetch();
        
        if ($mevcut_basvuru['sayi'] > 0) {
            $errors[] = "Bu çocuk için daha önce başvuru yapılmış.";
        }
    }
} catch (PDOException $e) {
    error_log("Etkinlik sorgulama hatası: " . $e->getMessage());
    $errors[] = "Sistem hatası oluştu. Lütfen daha sonra tekrar deneyiniz.";
}

// Hata varsa geri dön
if (!empty($errors)) {
    error_log("Form hataları: " . print_r($errors, true));
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: etkinlik-detay.php?id=$etkinlik_id");
    exit;
}

// Katılım kaydını ekle
try {
    // Telefon numarasını formatla
    $veli_telefon = preg_replace("/[^0-9]/", "", $veli_telefon);
    
    // Prepared statement kullan
    $sql = "INSERT INTO cocuk_meclisi_katilimcilar 
            (etkinlik_id, cocuk_adi, dogum_tarihi, veli_adi, veli_telefon, veli_email, durum) 
            VALUES 
            (?, ?, ?, ?, ?, ?, 'beklemede')";
    
    $stmt = $db->pdo->prepare($sql);
    $result = $stmt->execute([
        $etkinlik_id,
        $cocuk_adi,
        $dogum_tarihi,
        $veli_adi,
        $veli_telefon,
        $veli_email
    ]);
    
    if ($result) {
        // Katılımcı sayısını güncelle
        $update_sql = "UPDATE cocuk_meclisi_etkinlikler 
                      SET katilimci_sayisi = katilimci_sayisi + 1 
                      WHERE id = ?";
        $update_stmt = $db->pdo->prepare($update_sql);
        $update_stmt->execute([$etkinlik_id]);
        
        error_log("Kayıt başarılı - Etkinlik ID: $etkinlik_id, Çocuk: $cocuk_adi");
        $_SESSION['success_message'] = "Katılım başvurunuz başarıyla alınmıştır. En kısa sürede sizinle iletişime geçilecektir.";
    } else {
        throw new PDOException("Kayıt eklenemedi");
    }
    
} catch (PDOException $e) {
    error_log("Katılım kayıt hatası: " . $e->getMessage());
    $_SESSION['form_errors'] = ["Kayıt sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz."];
}

// Etkinlik sayfasına geri dön
header("Location: etkinlik-detay.php?id=$etkinlik_id");
exit;