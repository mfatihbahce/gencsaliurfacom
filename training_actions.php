<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Giriş kontrolü
if (!isLoggedIn()) {
    setError("Bu işlem için giriş yapmanız gerekiyor.");
    redirect('login.php');
}

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'apply') {
        $training_id = (int)$_POST['training_id'];
        
        try {
            // Eğitimin var olup olmadığını ve aktif olduğunu kontrol et
            $training = $db->query("
                SELECT * FROM trainings 
                WHERE id = ? AND end_date >= CURDATE()
            ", [$training_id])->fetch();

            if (!$training) {
                setError("Eğitim bulunamadı veya başvuru süresi dolmuş.");
                redirect('trainings.php');
            }

            // Daha önce başvuru yapılıp yapılmadığını kontrol et
            $existing = $db->query("
                SELECT id FROM training_applications 
                WHERE training_id = ? AND user_id = ?
            ", [$training_id, $_SESSION['user_id']])->fetch();

            if ($existing) {
                setError("Bu eğitime daha önce başvuru yaptınız.");
                redirect('trainings.php');
            }

            // Profil bilgilerinin tam olduğunu kontrol et
            if (!isProfileComplete($_SESSION['user_id'])) {
                setError("Lütfen önce profil bilgilerinizi tamamlayın.");
                redirect('profile.php');
            }

            // Başvuruyu kaydet
            $db->query("
                INSERT INTO training_applications (
                    training_id, 
                    user_id, 
                    status,
                    created_at
                ) VALUES (?, ?, 'pending', NOW())
            ", [$training_id, $_SESSION['user_id']]);

            setSuccess("Eğitime başarıyla başvurdunuz.");

        } catch (Exception $e) {
            setError("Başvuru yapılırken bir hata oluştu: " . $e->getMessage());
        }
        
        redirect('trainings.php');
    }
}

// Geçersiz istek
redirect('trainings.php');