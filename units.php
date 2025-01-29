<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Birimleri getir
$units = $db->query("SELECT * FROM units ORDER BY name")->fetchAll();

$page_title = "Eğitim Birimleri";
include 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    /* Ana container için stil */
    .main-container {
        height: calc(100vh - 180px);
        margin-top: 20px;
        background: #f8f9fa;
        padding: 20px;
        position: relative;
    }

    /* Birimler listesi için stil */
    .units-list {
        height: 100%;
        overflow-y: auto;
        padding: 10px;
        max-height: calc(100vh - 280px);
        position: relative;
    }

    .units-list .list-group-item {
        border-radius: 15px;
        margin-bottom: 15px;
        border: none;
        background: white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        padding: 12px;
    }

    .units-list .list-group-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
    }

    /* Kart başlığı için stil */
    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 15px;
        background: linear-gradient(135deg, #007bff, #0056b3) !important;
    }

    /* Birim kartı içeriği için stil */
    .unit-card-content {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .unit-header {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .unit-icon {
        min-width: 40px;
        height: 40px;
        background: #f0f4ff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #007bff;
    }

    .unit-info {
        margin-bottom: 8px;
    }

    .unit-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
        font-size: 1rem;
    }

    .unit-address {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .unit-hours {
        font-size: 0.8rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .unit-actions {
        display: flex;
        gap: 8px;
        margin-top: 5px;
    }

    .btn-unit-action {
        flex: 1;
        padding: 8px;
        border-radius: 10px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        transition: all 0.2s ease;
    }

    /* Harita container stil */
    .map-container {
        position: sticky;
        top: 20px;
        height: calc(100vh - 220px);
        min-height: 500px;
    }

    /* Harita stil */
    #map {
        width: 100%;
        height: 100%;
        border-radius: 15px;
        background: #f0f0f0;
        z-index: 1;
    }

    /* En yakın birim butonu için stil */
    .nearest-unit-btn {
        background: white;
        border: none;
        border-radius: 10px;
        padding: 8px 15px;
        font-size: 0.9rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 10px;
    }

    .nearest-unit-btn:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Responsive düzenlemeler */
    @media (max-width: 768px) {
        .main-container {
            padding: 10px;
            height: auto;
            margin-bottom: 30px;
        }
        
        .units-list {
            height: 350px;
            margin-bottom: 15px;
        }
        
        .unit-actions {
            flex-direction: column;
        }
        
        .btn-unit-action {
            width: 100%;
        }
        
        .map-container {
            height: 400px;
            position: relative;
            top: 0;
            margin-bottom: 30px;
        }
    }
</style>

<div class="container-fluid main-container">
    <div class="row h-100">
        <!-- Birimler Listesi -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-white d-flex align-items-center">
                        <i class="bi bi-building me-2"></i>Eğitim Birimleri
                    </h5>
                    <button class="nearest-unit-btn" onclick="findNearestUnit()">
                        <i class="bi bi-geo-alt me-1"></i>En Yakın Birimi Bul
                    </button>
                </div>
                <div class="card-body units-list">
                    <div class="list-group">
                        <?php foreach ($units as $unit): ?>
                            <div class="list-group-item">
                                <div class="unit-card-content">
                                    <div class="unit-header">
                                        <div class="unit-icon">
                                            <i class="bi bi-building-fill"></i>
                                        </div>
                                        <div class="unit-info">
                                            <div class="unit-name"><?= htmlspecialchars($unit['name']) ?></div>
                                            <div class="unit-address">
                                                <i class="bi bi-geo-alt-fill"></i>
                                                <?= htmlspecialchars($unit['address']) ?>
                                            </div>
                                            <div class="unit-hours">
                                                <i class="bi bi-clock-fill"></i>
                                                <?= htmlspecialchars($unit['working_hours']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="unit-actions">
                                        <button class="btn-unit-action btn btn-outline-primary" 
                                                onclick="showUnitOnMap(<?= $unit['latitude'] ?>, <?= $unit['longitude'] ?>, 
                                                '<?= htmlspecialchars($unit['name']) ?>')">
                                            <i class="bi bi-map-fill"></i>
                                            Haritada Göster
                                        </button>
                                        <a href="unit-detail.php?id=<?= $unit['id'] ?>" 
                                           class="btn-unit-action btn btn-outline-info">
                                            <i class="bi bi-info-circle-fill"></i>
                                            Detay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Harita -->
        <div class="col-md-8">
            <div class="card h-100 map-container">
                <div class="card-body p-0">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Harita için global değişkenler
let map;
let markers = [];
let userLocation;

// Birim verilerini JavaScript array'ine dönüştür
const units = [
    <?php foreach ($units as $unit): ?>
    {
        lat: <?= $unit['latitude'] ?>,
        lng: <?= $unit['longitude'] ?>,
        name: '<?= htmlspecialchars($unit['name']) ?>',
        address: '<?= htmlspecialchars($unit['address']) ?>',
        hours: '<?= htmlspecialchars($unit['working_hours']) ?>',
        id: <?= $unit['id'] ?>
    },
    <?php endforeach; ?>
];

// Sayfa yüklendiğinde haritayı başlat
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

function initMap() {
    try {
        // Şanlıurfa'nın koordinatları
        const sanliurfaCenter = [37.167339, 38.793919];
        
        // Haritayı oluştur
        map = L.map('map', {
            center: sanliurfaCenter,
            zoom: 12
        });

        // OpenStreetMap katmanı
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Özel marker ikonu
        const customIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Tüm birimleri haritaya ekle
        let bounds = L.latLngBounds();
        
        units.forEach(unit => {
            if (unit.lat && unit.lng) {
                const marker = L.marker([unit.lat, unit.lng], {
                    icon: customIcon
                }).bindPopup(`
                    <div class="text-center">
                        <h6 class="mb-2 fw-bold">${unit.name}</h6>
                        <p class="small mb-2">${unit.address}</p>
                        <p class="small mb-2"><i class="bi bi-clock"></i> ${unit.hours}</p>
                        <div class="d-grid gap-2">
                            <a href="https://www.google.com/maps/dir/?api=1&destination=${unit.lat},${unit.lng}" 
                               target="_blank" class="btn btn-sm btn-dark">
                                <i class="bi bi-map"></i> Yol Tarifi
                            </a>
                            <a href="unit-detail.php?id=${unit.id}" class="btn btn-sm btn-info">
                                <i class="bi bi-info-circle"></i> Detay
                            </a>
                        </div>
                    </div>
                `).addTo(map);
                
                markers.push(marker);
                bounds.extend([unit.lat, unit.lng]);
            }
        });

        // Eğer marker varsa, tüm markerleri gösterecek şekilde haritayı ayarla
        if (markers.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }

        // Haritayı yeniden boyutlandır
        setTimeout(() => {
            map.invalidateSize();
        }, 100);

    } catch (error) {
        console.error('Harita yüklenirken hata oluştu:', error);
    }
}

function showUnitOnMap(lat, lng, title) {
    if (map && lat && lng) {
        map.setView([lat, lng], 16);
        markers.forEach(marker => {
            const markerLatLng = marker.getLatLng();
            if (markerLatLng.lat === lat && markerLatLng.lng === lng) {
                marker.openPopup();
            }
        });
    }
}

function findNearestUnit() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(position => {
            userLocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            let nearestUnit = null;
            let shortestDistance = Infinity;
            
            units.forEach(unit => {
                const distance = getDistance(
                    userLocation.lat, 
                    userLocation.lng, 
                    unit.lat, 
                    unit.lng
                );
                
                if (distance < shortestDistance) {
                    shortestDistance = distance;
                    nearestUnit = unit;
                }
            });
            
            if (nearestUnit) {
                showUnitOnMap(nearestUnit.lat, nearestUnit.lng, nearestUnit.name);
                
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.style.borderRadius = '12px';
                alertDiv.style.marginBottom = '20px';
                alertDiv.innerHTML = `
                    <strong>En yakın birim:</strong> ${nearestUnit.name}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        }, error => {
            alert('Konum alınamadı: ' + error.message);
        });
    } else {
        alert('Konum servisleri tarayıcınız tarafından desteklenmiyor.');
    }
}

function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function deg2rad(deg) {
    return deg * (Math.PI/180);
}

// Sayfa yüklendiğinde responsive düzenlemeler
window.addEventListener('resize', () => {
    if (map) {
        map.invalidateSize();
    }
});
</script>

<br><br>
<?php include 'includes/footer.php'; ?>