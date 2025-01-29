<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
        alert('Geçersiz eğitim ID\'si!');
        window.location.href = 'training-manage.php';
    </script>";
    exit;
}

$training_id = $_GET['id'];

try {
    // Önce bu eğitime ait kayıtları kontrol et
    $registrationCount = $db->query(
        "SELECT COUNT(*) as count FROM training_registrations WHERE training_id = ?",
        [$training_id]
    )->fetch()['count'];

    if ($registrationCount > 0) {
        echo "<script>
            alert('Bu eğitime kayıtlı kullanıcılar olduğu için silinemez!');
            window.location.href = 'training-manage.php';
        </script>";
        exit;
    }

    // Eğitimi sil
    $db->query(
        "DELETE FROM trainings WHERE id = ?",
        [$training_id]
    );

    echo "<script>
        alert('Eğitim başarıyla silindi.');
        window.location.href = 'training-manage.php';
    </script>";
    
} catch (Exception $e) {
    echo "<script>
        alert('Eğitim silinirken bir hata oluştu!');
        window.location.href = 'training-manage.php';
    </script>";
}
?> 