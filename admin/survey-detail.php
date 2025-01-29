<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Admin kontrolü
checkAdmin();

if (!isset($_GET['id'])) {
    echo "Geçersiz istek";
    exit;
}

$db = Database::getInstance();
$survey = $db->query("SELECT * FROM youth_surveys WHERE id = ?", [$_GET['id']])->fetch();

if (!$survey) {
    echo "Anket bulunamadı";
    exit;
}

// Etiketleri hazırla
$genderLabels = [
    'female' => 'Kadın',
    'male' => 'Erkek',
    'prefer_not_to_say' => 'Belirtilmemiş'
];

$ratingLabels = [
    'very_good' => 'Çok İyi',
    'good' => 'İyi',
    'average' => 'Orta',
    'needs_improvement' => 'Geliştirilmeli',
    'insufficient' => 'Yetersiz'
];
?>

<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <th style="width: 200px;">Tarih</th>
            <td><?php echo formatDateTime($survey['created_at']); ?></td>
        </tr>
        <tr>
            <th>Yaş Grubu</th>
            <td><?php echo $survey['age_group']; ?></td>
        </tr>
        <tr>
            <th>Cinsiyet</th>
            <td><?php echo $genderLabels[$survey['gender']] ?? $survey['gender']; ?></td>
        </tr>
        <tr>
            <th>Meclis Üyesi</th>
            <td><?php echo $survey['is_council_member'] ? 'Evet' : 'Hayır'; ?></td>
        </tr>
        <tr>
            <th>Tercih Edilen Hizmetler</th>
            <td><?php echo str_replace(',', ', ', $survey['desired_services']); ?></td>
        </tr>
        <tr>
            <th>Hizmet Detayları</th>
            <td><?php echo nl2br($survey['service_details']) ?: '-'; ?></td>
        </tr>
        <tr>
            <th>İletişim Kanalları</th>
            <td><?php echo str_replace(',', ', ', $survey['communication_channels']); ?></td>
        </tr>
        <tr>
            <th>Geri Bildirim Kanalları</th>
            <td><?php echo str_replace(',', ', ', $survey['feedback_channels']); ?></td>
        </tr>
        <tr>
            <th>Etkinlik Önerisi</th>
            <td><?php echo nl2br($survey['activity_suggestion']) ?: '-'; ?></td>
        </tr>
        <tr>
            <th>Hizmet Değerlendirmesi</th>
            <td><?php echo $ratingLabels[$survey['service_rating']] ?? $survey['service_rating']; ?></td>
        </tr>
        <tr>
            <th>Önceki İletişim</th>
            <td>
                <?php 
                echo $survey['previous_contact'] ? 'Evet' : 'Hayır';
                if ($survey['previous_contact'] && $survey['contact_method']) {
                    echo ' (' . $survey['contact_method'] . ')';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>Ek Görüşler</th>
            <td><?php echo nl2br($survey['additional_feedback']) ?: '-'; ?></td>
        </tr>
        <tr>
            <th>İletişim Bilgisi</th>
            <td><?php echo $survey['contact_info'] ?: '-'; ?></td>
        </tr>
    </table>
</div>