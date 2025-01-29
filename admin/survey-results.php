<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Admin kontrolü
checkAdmin();

$db = Database::getInstance();

// Tüm anket verilerini çek
$surveys = $db->query("SELECT * FROM youth_surveys ORDER BY created_at DESC")->fetchAll();

// İstatistikler için veri hazırla
$stats = [
    'age_groups' => [
        '10-13' => 0,
        '14-17' => 0,
        '18-24' => 0,
        '25-30' => 0
    ],
    'genders' => [
        'kadın' => 0,
        'erkek' => 0,
        'belirtilmemiş' => 0
    ],
    'services' => [
        'sanat' => 0,
        'dil_kursu' => 0,
        'kodlama' => 0,
        'kişisel_gelişim' => 0,
        'spor_alanları' => 0,
        'açık_hava' => 0,
        'turnuvalar' => 0,
        'fitness' => 0,
        'festivaller' => 0,
        'sinema' => 0,
        'kitap_okuma' => 0,
        'kültür_gezileri' => 0
    ],
    'ratings' => [
        'very_good' => 0,
        'good' => 0,
        'average' => 0,
        'needs_improvement' => 0,
        'insufficient' => 0
    ],
    'council_member' => [
        'yes' => 0,
        'no' => 0
    ],
    'communication_channels' => [
        'web_sitesi' => 0,
        'çocuk_meclisi' => 0,
        'sosyal_medya' => 0,
        'mobil_uygulama' => 0,
        'toplantılar' => 0,
        'eposta' => 0,
        'telefon' => 0,
        'yüz_yüze' => 0
    ],
    'previous_contact' => [
        'yes' => 0,
        'no' => 0
    ]
];

// Grafik etiketlerini Türkçeleştir
$chartLabels = [
    'genders' => [
        'kadın' => 'Kadın',
        'erkek' => 'Erkek',
        'belirtilmemiş' => 'Belirtilmemiş'
    ],
    'services' => [
        'sanat' => 'Sanat',
        'dil_kursu' => 'Dil Kursları',
        'kodlama' => 'Kodlama ve Teknoloji',
        'kişisel_gelişim' => 'Kişisel Gelişim',
        'spor_alanları' => 'Spor Alanları',
        'açık_hava' => 'Açık Hava Etkinlikleri',
        'turnuvalar' => 'Spor Turnuvaları',
        'fitness' => 'Fitness ve Yoga',
        'festivaller' => 'Festivaller',
        'sinema' => 'Sinema ve Tiyatro',
        'kitap_okuma' => 'Kitap Okuma',
        'kültür_gezileri' => 'Kültür Gezileri'
    ],
    'ratings' => [
        'very_good' => 'Çok İyi',
        'good' => 'İyi',
        'average' => 'Orta',
        'needs_improvement' => 'Geliştirilmeli',
        'insufficient' => 'Yetersiz'
    ],
    'council_member' => [
        'yes' => 'Evet',
        'no' => 'Hayır'
    ],
    'communication_channels' => [
        'web_sitesi' => 'Web Sitesi',
        'çocuk_meclisi' => 'Çocuk Meclisi',
        'sosyal_medya' => 'Sosyal Medya',
        'mobil_uygulama' => 'Mobil Uygulama',
        'toplantılar' => 'Toplantılar',
        'eposta' => 'E-posta',
        'telefon' => 'Telefon',
        'yüz_yüze' => 'Yüz Yüze'
    ],
    'previous_contact' => [
        'yes' => 'Evet',
        'no' => 'Hayır'
    ]
];

// Verileri işle
foreach ($surveys as $survey) {
    // Yaş grupları istatistiği
    $age_group = $survey['age_group'] ?? '';
    if (!empty($age_group) && isset($stats['age_groups'][$age_group])) {
        $stats['age_groups'][$age_group]++;
    }
    
    // Cinsiyet istatistiği
    $gender = $survey['gender'] ?? '';
    if (!empty($gender) && isset($stats['genders'][$gender])) {
        $stats['genders'][$gender]++;
    }
    
    // Hizmet tercihleri istatistiği
    if (!empty($survey['desired_services'])) {
        $services = array_filter(explode(',', trim($survey['desired_services'])));
        foreach ($services as $service) {
            $service = trim($service);
            if (!empty($service) && isset($stats['services'][$service])) {
                $stats['services'][$service]++;
            }
        }
    }
    
    // Değerlendirme istatistiği
    $rating = $survey['service_rating'] ?? '';
    if (!empty($rating) && isset($stats['ratings'][$rating])) {
        $stats['ratings'][$rating]++;
    }

    // Meclis üyeliği istatistiği
    $is_member = isset($survey['is_council_member']) ? ($survey['is_council_member'] ? 'yes' : 'no') : 'no';
    $stats['council_member'][$is_member]++;

    // İletişim kanalları istatistiği
    if (!empty($survey['communication_channels'])) {
        $channels = array_filter(explode(',', trim($survey['communication_channels'])));
        foreach ($channels as $channel) {
            $channel = trim($channel);
            if (!empty($channel) && isset($stats['communication_channels'][$channel])) {
                $stats['communication_channels'][$channel]++;
            }
        }
    }

    // Önceki iletişim istatistiği
    $previous_contact = isset($survey['previous_contact']) ? ($survey['previous_contact'] ? 'yes' : 'no') : 'no';
    $stats['previous_contact'][$previous_contact]++;
}

// Debug için veritabanındaki değerlendirme değerlerini kontrol et
$ratings_check = $db->query("SELECT service_rating, COUNT(*) as count 
                            FROM youth_surveys 
                            WHERE service_rating IS NOT NULL 
                            GROUP BY service_rating")->fetchAll();

foreach ($ratings_check as $rating) {
    error_log("Database rating: " . $rating['service_rating'] . " - Count: " . $rating['count']);
}

$page_title = "Anket Sonuçları";
include 'includes/header.php';
?>

<style>
.chart-container {
    position: relative;
    height: 250px;
    width: 100%;
}
.small-chart {
    height: 200px;
}
.dataTables_wrapper {
    padding: 20px;
}
</style>

<div class="container-fluid py-4">
    <!-- Özet Kartları -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Toplam Anket</h6>
                    <h3 class="mb-0"><?php echo count($surveys); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Meclis Üyesi Sayısı</h6>
                    <h3 class="mb-0"><?php echo $stats['council_member']['yes']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Önceki İletişim</h6>
                    <h3 class="mb-0"><?php echo $stats['previous_contact']['yes']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Ortalama Memnuniyet</h6>
                    <h3 class="mb-0">
                        <?php 
                        $total = array_sum($stats['ratings']);
                        $weighted = $stats['ratings']['very_good'] * 5 + 
                                  $stats['ratings']['good'] * 4 + 
                                  $stats['ratings']['average'] * 3 + 
                                  $stats['ratings']['needs_improvement'] * 2 + 
                                  $stats['ratings']['insufficient'] * 1;
                        echo number_format($total > 0 ? $weighted / $total : 0, 1) . '/5';
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafikler -->
    <div class="row g-4 mb-4">
        <!-- Yaş Grupları Grafiği -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Yaş Grupları Dağılımı</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cinsiyet Dağılımı -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Cinsiyet Dağılımı</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meclis Üyeliği -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Meclis Üyeliği Dağılımı</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="councilChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
		        <!-- Hizmet Tercihleri -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">En Çok Talep Edilen Hizmetler</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="servicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Değerlendirme Sonuçları -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Hizmet Değerlendirmesi</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ratingsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- İletişim Kanalları -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Tercih Edilen İletişim Kanalları</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="communicationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Önceki İletişim -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Önceki İletişim Durumu</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="previousContactChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Anket Listesi -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Tüm Anket Yanıtları</h6>
            <div>
                <a href="<?= SITE_URL ?>/admin/all-surveys.php" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-list-ul me-2"></i>Tüm Yanıtları Görüntüle
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
                            <th>Değerlendirme</th>
                            <th>İletişim</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($surveys as $survey): ?>
                        <tr>
                            <td><?php echo date('d.m.Y H:i', strtotime($survey['created_at'])); ?></td>
                            <td><?php echo $survey['age_group'] ?? 'Belirtilmemiş'; ?></td>
                            <td><?php echo $chartLabels['genders'][$survey['gender'] ?? ''] ?? 'Belirtilmemiş'; ?></td>
                            <td><?php echo isset($survey['is_council_member']) && $survey['is_council_member'] ? 'Evet' : 'Hayır'; ?></td>
                            <td>
                                <?php
                                $services = !empty($survey['desired_services']) ? explode(',', $survey['desired_services']) : [];
                                $serviceNames = [];
                                foreach ($services as $service) {
                                    $service = trim($service);
                                    if (!empty($service) && isset($chartLabels['services'][$service])) {
                                        $serviceNames[] = $chartLabels['services'][$service];
                                    }
                                }
                                echo !empty($serviceNames) ? implode(', ', array_slice($serviceNames, 0, 2)) : '-';
                                if (count($serviceNames) > 2) echo '...';
                                ?>
                            </td>
                            <td><?php echo $chartLabels['ratings'][$survey['service_rating'] ?? ''] ?? 'Belirtilmemiş'; ?></td>
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
document.addEventListener('DOMContentLoaded', function() {
    // DataTables inicializasyonu
    $('#surveysTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
        },
        order: [[0, 'desc']],
        pageLength: 25
    });

    // Grafikleri oluştur
    createCharts();
});

function createCharts() {
    // Yaş Grupları Grafiği
    new Chart(document.getElementById('ageChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(<?php echo json_encode($stats['age_groups']); ?>),
            datasets: [{
                label: 'Kişi Sayısı',
                data: Object.values(<?php echo json_encode($stats['age_groups']); ?>),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Cinsiyet Dağılımı Grafiği
    new Chart(document.getElementById('genderChart'), {
        type: 'pie',
        data: {
            labels: Object.values(<?php echo json_encode($chartLabels['genders']); ?>),
            datasets: [{
                data: Object.values(<?php echo json_encode($stats['genders']); ?>),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Hizmet Tercihleri Grafiği
    new Chart(document.getElementById('servicesChart'), {
        type: 'bar',
        data: {
            labels: Object.values(<?php echo json_encode($chartLabels['services']); ?>),
            datasets: [{
                label: 'Tercih Sayısı',
                data: Object.values(<?php echo json_encode($stats['services']); ?>),
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    // İletişim Kanalları Grafiği
    new Chart(document.getElementById('communicationChart'), {
        type: 'bar',
        data: {
            labels: Object.values(<?php echo json_encode($chartLabels['communication_channels']); ?>),
            datasets: [{
                label: 'Tercih Sayısı',
                data: Object.values(<?php echo json_encode($stats['communication_channels']); ?>),
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Meclis Üyeliği ve Değerlendirme Grafikleri
    new Chart(document.getElementById('councilChart'), {
        type: 'doughnut',
        data: {
            labels: Object.values(<?php echo json_encode($chartLabels['council_member']); ?>),
            datasets: [{
                data: Object.values(<?php echo json_encode($stats['council_member']); ?>),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    new Chart(document.getElementById('ratingsChart'), {
        type: 'doughnut',
        data: {
            labels: Object.values(<?php echo json_encode($chartLabels['ratings']); ?>),
            datasets: [{
                data: Object.values(<?php echo json_encode($stats['ratings']); ?>),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(255, 159, 64, 0.5)',
                    'rgba(255, 99, 132, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    new Chart(document.getElementById('previousContactChart'), {
        type: 'pie',
        data: {
            labels: Object.values(<?php echo json_encode($chartLabels['previous_contact']); ?>),
            datasets: [{
                data: Object.values(<?php echo json_encode($stats['previous_contact']); ?>),
                backgroundColor: [
                    'rgba(255, 159, 64, 0.5)',
                    'rgba(201, 203, 207, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function viewDetails(id) {
    $.get('survey-detail.php', { id: id }, function(response) {
        $('#surveyDetailContent').html(response);
        $('#surveyDetailModal').modal('show');
    });
}

function exportToExcel() {
    const table = document.getElementById('surveysTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Anket Sonuçları" });
    XLSX.writeFile(wb, `anket_sonuclari_${new Date().toISOString().slice(0,10)}.xlsx`);
}
</script>

<?php include 'includes/footer.php'; ?>