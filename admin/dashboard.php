<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Temel istatistikler
$stats = [
    'users' => $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'],
    'members' => $db->query("SELECT COUNT(*) as count FROM members")->fetch()['count'],
    'trainings' => $db->query("SELECT COUNT(*) as count FROM trainings WHERE is_active = 1")->fetch()['count'],
    'students' => $db->query("SELECT COUNT(*) as count FROM students")->fetch()['count'],
    'pending_registrations' => $db->query("SELECT COUNT(*) as count FROM training_registrations")->fetch()['count']
];

// Aylık kayıt istatistikleri
$monthly_stats = $db->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM training_registrations
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
")->fetchAll();

// Birimlere göre öğrenci dağılımı
$unit_stats = $db->query("
    SELECT u.name, COUNT(s.id) as student_count
    FROM units u
    LEFT JOIN students s ON u.id = s.unit_id
    GROUP BY u.id
    ORDER BY student_count DESC
    LIMIT 5
")->fetchAll();

// Son aktiviteler
$recent_activities = $db->query("
    SELECT 
        tr.*, 
        t.title as training_title,
        CONCAT(tr.first_name, ' ', tr.last_name) as student_name
    FROM training_registrations tr
    JOIN trainings t ON tr.training_id = t.id
    ORDER BY tr.created_at DESC
    LIMIT 10
")->fetchAll();

// Aktif eğitimler
$active_trainings = $db->query("
    SELECT 
        t.*, 
        u.name as unit_name,
        (SELECT COUNT(*) FROM training_registrations WHERE training_id = t.id) as registration_count
    FROM trainings t
    JOIN units u ON t.unit_id = u.id
    WHERE t.is_active = 1 AND t.end_date >= CURDATE()
    ORDER BY t.start_date ASC 
    LIMIT 5
")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid dashboard-container py-4">
    <!-- Başlık -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" onclick="exportData('csv')">
                <i class="bi bi-download me-1"></i> Veri İndir
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Yazdır
            </button>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row g-4 mb-4">
        <!-- Toplam Üye -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-left-primary h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Toplam Üye
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['members']) ?>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktif Eğitimler -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-left-success h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Aktif Eğitimler
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['trainings']) ?>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-book-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toplam Öğrenci -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-left-info h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Toplam Öğrenci
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['students']) ?>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-mortarboard-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bekleyen Başvurular -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-left-warning h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Bekleyen Başvurular
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['pending_registrations']) ?>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafikler -->
    <div class="row g-4 mb-4">
        <!-- Aylık Kayıt Grafiği -->
        <div class="col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Aylık Kayıt İstatistikleri</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadChart('monthlyChart')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Birim Dağılımı -->
        <div class="col-xl-4">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Birim Dağılımı</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadChart('unitChart')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="unitChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Alt Kartlar -->
    <div class="row g-4">
        <!-- Son Aktiviteler -->
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Son Aktiviteler</h6>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-content">
                                    <div class="activity-header">
                                        <strong><?= htmlspecialchars($activity['student_name']) ?></strong>
                                        <span class="text-muted">
                                            <?= htmlspecialchars($activity['training_title']) ?> eğitimine kayıt oldu
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('d.m.Y H:i', strtotime($activity['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktif Eğitimler -->
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aktif Eğitimler</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Eğitim</th>
                                    <th>Birim</th>
                                    <th>Katılımcı</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($active_trainings as $training): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($training['title']) ?></td>
                                        <td><?= htmlspecialchars($training['unit_name']) ?></td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <?php 
                                                $percentage = min(($training['registration_count'] / $training['capacity']) * 100, 100);
                                                ?>
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <small><?= $training['registration_count'] ?>/<?= $training['capacity'] ?></small>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($training['start_date'])) ?></td>
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

<!-- Stil -->
<style>
.dashboard-container {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    opacity: 0.3;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-icon {
    opacity: 0.8;
}

.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }

.activity-timeline {
    position: relative;
    padding: 0;
}

.activity-item {
    position: relative;
    padding: 1rem 0;
    border-left: 2px solid #e3e6f0;
    margin-left: 1rem;
}

.activity-item::before {
    content: '';
    position: absolute;
    left: -0.5rem;
    top: 1.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #4e73df;
    border: 2px solid #fff;
}

.activity-content {
    margin-left: 1rem;
}

.progress {
    background-color: #eaecf4;
}

.progress-bar {
    background-color: #4e73df;
}

@media print {
    .btn, .stat-icon { display: none; }
    .card { break-inside: avoid; }
}

@media (max-width: 768px) {
    .activity-timeline {
        margin-left: -0.5rem;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
}
</style>

<!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aylık kayıt grafiği
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(function($item) {
                return date('M Y', strtotime($item['month']));
            }, $monthly_stats)) ?>,
            datasets: [{
                label: 'Kayıt Sayısı',
                data: <?= json_encode(array_column($monthly_stats, 'count')) ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.4,
                fill: true
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
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Birim dağılım grafiği
    const unitCtx = document.getElementById('unitChart').getContext('2d');
    new Chart(unitCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($unit_stats, 'name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($unit_stats, 'student_count')) ?>,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            },
            cutout: '70%'
        }
    });
});

// Grafik indirme fonksiyonu
function downloadChart(chartId) {
    const canvas = document.getElementById(chartId);
    const image = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.download = `${chartId}.png`;
    link.href = image;
    link.click();
}

// Veri dışa aktarma fonksiyonu
function exportData(type) {
    if (type === 'csv') {
        // CSV formatında veri dışa aktarma
        const data = [
            ['İstatistik', 'Değer'],
            ['Toplam Üye', <?= $stats['members'] ?>],
            ['Aktif Eğitimler', <?= $stats['trainings'] ?>],
            ['Toplam Öğrenci', <?= $stats['students'] ?>],
            ['Bekleyen Başvurular', <?= $stats['pending_registrations'] ?>]
        ];

        let csvContent = "data:text/csv;charset=utf-8," 
            + data.map(row => row.join(',')).join('\n');

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'dashboard_stats.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>

<?php include 'includes/footer.php'; ?>