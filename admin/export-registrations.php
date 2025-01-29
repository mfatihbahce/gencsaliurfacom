<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$training_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$training_id) {
    die('Geçersiz eğitim ID');
}

$db = Database::getInstance();

// Eğitim bilgilerini getir
$training = $db->query(
    "SELECT t.*, u.name as unit_name 
     FROM trainings t 
     JOIN units u ON t.unit_id = u.id 
     WHERE t.id = ?",
    [$training_id]
)->fetch();

// Kayıtları getir
$registrations = $db->query(
    "SELECT r.*, d.name as district_name 
     FROM training_registrations r 
     JOIN districts d ON r.district_id = d.id 
     WHERE r.training_id = ? 
     ORDER BY r.created_at DESC",
    [$training_id]
)->fetchAll();

// Excel dosyası oluştur
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="egitim_kayitlari.xls"');
header('Cache-Control: max-age=0');

// Excel içeriği
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '</head>';
echo '<body>';
echo '<table border="1">';

// Başlık
echo '<tr>';
echo '<th colspan="8">' . $training['title'] . ' - ' . $training['unit_name'] . '</th>';
echo '</tr>';
echo '<tr>';
echo '<th colspan="8">Tarih: ' . date('d.m.Y', strtotime($training['start_date'])) . 
     ' - ' . date('d.m.Y', strtotime($training['end_date'])) . '</th>';
echo '</tr>';

// Tablo başlıkları
echo '<tr>';
echo '<th>Ad</th>';
echo '<th>Soyad</th>';
echo '<th>TC No</th>';
echo '<th>Doğum Tarihi</th>';
echo '<th>Uyruk</th>';
echo '<th>İlçe</th>';
echo '<th>Mahalle</th>';
echo '<th>Telefon</th>';
echo '</tr>';

// Kayıtlar
foreach ($registrations as $reg) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($reg['first_name']) . '</td>';
    echo '<td>' . htmlspecialchars($reg['last_name']) . '</td>';
    echo '<td>' . htmlspecialchars($reg['tc_no']) . '</td>';
    echo '<td>' . date('d.m.Y', strtotime($reg['birth_date'])) . '</td>';
    echo '<td>' . htmlspecialchars($reg['nationality']) . '</td>';
    echo '<td>' . htmlspecialchars($reg['district_name']) . '</td>';
    echo '<td>' . htmlspecialchars($reg['neighborhood']) . '</td>';
    echo '<td>' . htmlspecialchars($reg['phone']) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>'; 