<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$registration_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$registration_id) {
    die('Geçersiz kayıt ID');
}

$db = Database::getInstance();

$registration = $db->query(
    "SELECT r.*, d.name as district_name 
     FROM training_registrations r 
     JOIN districts d ON r.district_id = d.id 
     WHERE r.id = ?",
    [$registration_id]
)->fetch();

if (!$registration) {
    die('Kayıt bulunamadı');
}
?>

<div class="table-responsive">
    <table class="table">
        <tr>
            <th>Ad Soyad</th>
            <td>
                <?= htmlspecialchars($registration['first_name']) ?> 
                <?= htmlspecialchars($registration['last_name']) ?>
            </td>
        </tr>
        <tr>
            <th>TC No</th>
            <td><?= htmlspecialchars($registration['tc_no']) ?></td>
        </tr>
        <tr>
            <th>Doğum Tarihi</th>
            <td><?= date('d.m.Y', strtotime($registration['birth_date'])) ?></td>
        </tr>
        <tr>
            <th>Uyruk</th>
            <td><?= htmlspecialchars($registration['nationality']) ?></td>
        </tr>
        <tr>
            <th>İlçe</th>
            <td><?= htmlspecialchars($registration['district_name']) ?></td>
        </tr>
        <tr>
            <th>Mahalle</th>
            <td><?= htmlspecialchars($registration['neighborhood']) ?></td>
        </tr>
        <tr>
            <th>Telefon</th>
            <td><?= htmlspecialchars($registration['phone']) ?></td>
        </tr>
        <tr>
            <th>Kayıt Tarihi</th>
            <td><?= date('d.m.Y H:i', strtotime($registration['created_at'])) ?></td>
        </tr>
    </table>
</div> 