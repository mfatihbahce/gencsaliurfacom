<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Kategorileri getir
$categories = $db->query("SELECT DISTINCT category FROM training_types WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Eğitim türlerini getir
$training_types = $db->query("SELECT * FROM training_types ORDER BY name")->fetchAll();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // HTML özel karakterlerini decode et
    $school_name = html_entity_decode($_POST['school_name'], ENT_QUOTES, 'UTF-8');
    $contact_person = html_entity_decode($_POST['contact_person'], ENT_QUOTES, 'UTF-8');
    $phone = $_POST['phone'];
    $address = html_entity_decode($_POST['address'], ENT_QUOTES, 'UTF-8');
    $training_type_id = (int)$_POST['training_type_id'];
    $category = $_POST['category'];
    $requested_date = $_POST['requested_date'];
    $student_count = (int)$_POST['student_count'];

    try {
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO training_requests (
                school_name, 
                training_type_id,
                category, 
                requested_date, 
                contact_person, 
                phone, 
                address, 
                student_count, 
                status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [
                $school_name,
                $training_type_id,
                $category,
                $requested_date,
                $contact_person,
                $phone,
                $address,
                $student_count
            ]
        );
        
        setSuccess("Eğitim talebiniz başarıyla oluşturuldu. En kısa sürede sizinle iletişime geçilecektir.");
        header("Location: training-request.php");
        exit();
    } catch (Exception $e) {
        setError("Talep oluşturulurken bir hata oluştu: " . $e->getMessage());
    }
}

$page_title = 'Eğitim Talebi';
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid bg-light py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h2 fw-bold mb-2">Okulun için Ücretsiz Eğitim Talebi Oluştur</h1>
                <p class="text-muted mb-0">Okulunuz için Ücetsiz Robotik kodlama ve yazılım eğitimlerimiz için talepte bulunabilirsiniz.</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="training-contents" class="btn btn-primary">
                    <i class="bi bi-book me-2"></i>Eğitim İçeriklerini İncele
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- Sol Taraf: Form -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="card-title mb-0">Ücretsiz Eğitim Talebi Formu</h4>
                </div>
                <div class="card-body">
                    <form method="POST" accept-charset="UTF-8" action="" id="requestForm" novalidate>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="school_name" class="form-label">Okul Adı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="school_name" name="school_name" 
                                       value="<?= isset($_POST['school_name']) ? htmlspecialchars($_POST['school_name']) : '' ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label for="category" class="form-label">Eğitim Kategorisi <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category) ?>"
                                            <?= (isset($_POST['category']) && $_POST['category'] == $category) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="training_type_id" class="form-label">Talep Edilen Eğitim <span class="text-danger">*</span></label>
                                <select class="form-select" id="training_type_id" name="training_type_id" required>
                                    <option value="">Önce kategori seçiniz</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="requested_date" class="form-label">Talep Edilen Tarih <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="requested_date" name="requested_date" 
                                       min="<?= date('Y-m-d') ?>"
                                       value="<?= isset($_POST['requested_date']) ? htmlspecialchars($_POST['requested_date']) : '' ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="student_count" class="form-label">Öğrenci Sayısı <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="student_count" name="student_count" 
                                       min="15" max="40" step="1"
                                       value="<?= isset($_POST['student_count']) ? htmlspecialchars($_POST['student_count']) : '' ?>" required>
                                <div class="form-text">Minimum 15, maksimum 40 öğrenci</div>
                            </div>

                            <div class="col-md-12">
                                <label for="contact_person" class="form-label">Yetkili Kişi Adı Soyadı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                       value="<?= isset($_POST['contact_person']) ? htmlspecialchars($_POST['contact_person']) : '' ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label for="phone" class="form-label">Telefon Numarası <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       minlength="10" maxlength="10"
                                       placeholder="5XXXXXXXXX"
                                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" 
                                       required>
                                <div class="form-text">Örnek: 5XXXXXXXXX (10 haneli numara)</div>
                            </div>

                            <div class="col-md-12">
                                <label for="address" class="form-label">Okul Adresi <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Talep Oluştur
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sağ Taraf: SSS -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="card-title mb-0">Sıkça Sorulan Sorular</h4>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Eğitimler ücretsiz mi?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Evet, tüm eğitimlerimiz tamamen ücretsizdir.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Minimum öğrenci sayısı kaçtır?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Eğitimlerimiz için minimum 15, maksimum 40 öğrenci katılımı gerekmektedir.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Ne kadar sürede dönüş yapılıyor?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Talebiniz alındıktan sonra en geç 2 iş günü içinde sizinle iletişime geçilecektir.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Eğitim süresi ne kadar?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Eğitim süreleri seçilen programa göre değişmektedir. Detaylı bilgi için eğitim içeriklerini inceleyebilirsiniz.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<!-- Yukarı Çık Butonu -->
<button id="backToTop" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4" 
        style="display: none; width: 45px; height: 45px;">
    <i class="bi bi-arrow-up"></i>
</button>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: var(--bs-primary);
}

#backToTop {
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1000;
}

#backToTop.visible {
    opacity: 1;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}
</style>

<script>
// Kategori seçimi değiştiğinde eğitimleri güncelle
document.getElementById('category').addEventListener('change', function() {
    const category = this.value;
    const trainingSelect = document.getElementById('training_type_id');
    
    // Eğitim seçimini sıfırla
    trainingSelect.innerHTML = '<option value="">Seçiniz</option>';
    
    if (category) {
        // AJAX ile seçilen kategoriye ait eğitimleri getir
        fetch(`get_trainings.php?category=${encodeURIComponent(category)}`)
            .then(response => response.json())
            .then(trainings => {
                trainings.forEach(training => {
                    const option = document.createElement('option');
                    option.value = training.id;
                    option.textContent = training.name;
                    trainingSelect.appendChild(option);
                });
            });
    }
});

// Telefon numarası kontrolü
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value;
});

// Form doğrulama
document.getElementById('requestForm').addEventListener('submit', function(event) {
    const phoneInput = document.getElementById('phone');
    const phoneValue = phoneInput.value.replace(/\D/g, '');
    
    if (phoneValue.length !== 10 || !phoneValue.startsWith('5')) {
        event.preventDefault();
        phoneInput.setCustomValidity('Lütfen geçerli bir telefon numarası giriniz (5XXXXXXXXX)');
    } else {
        phoneInput.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>