<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Admin kontrolü
checkAdmin();

$db = Database::getInstance();

// Tüm anket verilerini çek
$surveys = $db->query("SELECT * FROM youth_surveys ORDER BY created_at DESC")->fetchAll();

// Etiketleri hazırla
$labels = [
    'genders' => [
        'female' => 'Kadın',
        'male' => 'Erkek',
        'prefer_not_to_say' => 'Belirtilmemiş'
    ],
    'services' => [
        'art' => 'Sanat',
        'sports' => 'Spor',
        'education' => 'Eğitim',
        'technology' => 'Teknoloji',
        'culture' => 'Kültür',
        'health' => 'Sağlık',
        'social' => 'Sosyal',
        'environment' => 'Çevre'
    ],
    'ratings' => [
        'very_good' => 'Çok İyi',
        'good' => 'İyi',
        'average' => 'Orta',
        'needs_improvement' => 'Geliştirilmeli',
        'insufficient' => 'Yetersiz'
    ]
];

$page_title = "Tüm Anket Yanıtları";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tüm Anket Yanıtları</h5>
                    <div>
                        <a href="<?= SITE_URL ?>/admin/survey-results.php" class="btn btn-secondary btn-sm me-2">
                            <i class="bi bi-arrow-left me-2"></i>Geri Dön
                        </a>
                        <button class="btn btn-sm btn-success" onclick="exportToExcel()">
                            <i class="bi bi-file-excel me-2"></i>Excel'e Aktar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="surveysTable">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Yaş</th>
                                    <th>Cinsiyet</th>
                                    <th>Meclis Üyesi</th>
                                    <th>Tercih Edilen Hizmetler</th>
                                    <th>Hizmet Detayları</th>
                                    <th>İletişim Kanalları</th>
                                    <th>Geri Bildirim Kanalları</th>
                                    <th>Etkinlik Önerisi</th>
                                    <th>Değerlendirme</th>
                                    <th>Önceki İletişim</th>
                                    <th>İletişim Yöntemi</th>
                                    <th>Ek Görüşler</th>
                                    <th>İletişim Bilgisi</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($surveys as $survey): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($survey['created_at'])); ?></td>
                                    <td><?php echo $survey['age_group']; ?></td>
                                    <td><?php echo $labels['genders'][$survey['gender']] ?? $survey['gender']; ?></td>
                                    <td><?php echo $survey['is_council_member'] ? 'Evet' : 'Hayır'; ?></td>
                                    <td>
                                        <?php
                                        $services = explode(',', $survey['desired_services']);
                                        $serviceNames = [];
                                        foreach ($services as $service) {
                                            if (isset($labels['services'][$service])) {
                                                $serviceNames[] = $labels['services'][$service];
                                            }
                                        }
                                        echo implode(', ', $serviceNames);
                                        ?>
                                    </td>
                                    <td><?php echo nl2br($survey['service_details']) ?: '-'; ?></td>
                                    <td><?php echo str_replace(',', ', ', $survey['communication_channels']); ?></td>
                                    <td><?php echo str_replace(',', ', ', $survey['feedback_channels']); ?></td>
                                    <td><?php echo nl2br($survey['activity_suggestion']) ?: '-'; ?></td>
                                    <td><?php echo $labels['ratings'][$survey['service_rating']] ?? $survey['service_rating']; ?></td>
                                    <td><?php echo $survey['previous_contact'] ? 'Evet' : 'Hayır'; ?></td>
                                    <td><?php echo $survey['contact_method'] ?: '-'; ?></td>
                                    <td><?php echo nl2br($survey['additional_feedback']) ?: '-'; ?></td>
                                    <td><?php echo $survey['contact_info'] ?: '-'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $survey['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detay Modalı -->
<div class="modal fade" id="surveyDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anket Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="surveyDetailContent">
                <!-- AJAX ile doldurulacak -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#surveysTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        scrollX: true
    });
});

function viewDetails(id) {
    $.get('survey-detail.php', { id: id }, function(response) {
        $('#surveyDetailContent').html(response);
        $('#surveyDetailModal').modal('show');
    });
}

function exportToExcel() {
    const table = document.getElementById('surveysTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Tüm Anket Yanıtları" });
    XLSX.writeFile(wb, `anket_yanıtları_${new Date().toISOString().slice(0,10)}.xlsx`);
}
</script>

<?php include 'includes/footer.php'; ?>