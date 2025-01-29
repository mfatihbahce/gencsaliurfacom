<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Birimleri getir
$units = $db->query("SELECT id, name FROM units ORDER BY name")->fetchAll();

// İlçeleri getir
$districts = $db->query("SELECT id, name FROM districts ORDER BY name")->fetchAll();

$page_title = "Manuel Başvuru Ekle";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="bi bi-person-plus me-2"></i>Manuel Başvuru Ekle
        </h2>
        <a href="training-manage.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Geri Dön
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="save-student-registration.php" method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <!-- Kişisel Bilgiler -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Kişisel Bilgiler</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Ad <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">TC Kimlik No <span class="text-danger">*</span></label>
                            <input type="text" name="tc_no" class="form-control" 
                                   pattern="[0-9]{11}" maxlength="11" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Doğum Tarihi <span class="text-danger">*</span></label>
                            <input type="date" name="birth_date" class="form-control" required>
                        </div>

                        <!-- Uyruk input'u yerine aşağıdaki kodu ekleyin -->
<div class="mb-3">
    <label class="form-label">Uyruk <span class="text-danger">*</span></label>
    <select name="nationality" class="form-select" required>
        <option value="">Seçiniz</option>
        <option value="Türk">Türk</option>
        <option value="Suriyeli">Suriyeli</option>
        <option value="Diğer">Diğer</option>
    </select>
    <!-- Diğer seçeneği için ek input -->
    <div id="otherNationalityDiv" class="mt-2" style="display: none;">
        <input type="text" id="otherNationality" class="form-control" 
               placeholder="Lütfen uyruğunuzu belirtiniz">
    </div>
</div>
                    </div>

                    <!-- İletişim ve Eğitim Bilgileri -->
                    <div class="col-md-6">
                        <h5 class="mb-3">İletişim ve Eğitim Bilgileri</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">İlçe <span class="text-danger">*</span></label>
                            <select name="district_id" class="form-select" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($districts as $district): ?>
                                <option value="<?= $district['id'] ?>">
                                    <?= htmlspecialchars($district['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mahalle <span class="text-danger">*</span></label>
                            <input type="text" name="neighborhood" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" 
                                   pattern="[0-9]{10,11}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Eğitim Birimi <span class="text-danger">*</span></label>
                            <select name="unit_id" class="form-select" required 
                                    onchange="loadTrainings(this.value)">
                                <option value="">Seçiniz</option>
                                <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>">
                                    <?= htmlspecialchars($unit['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Eğitim <span class="text-danger">*</span></label>
                            <select name="training_id" class="form-select" required 
                                    id="trainingSelect" disabled>
                                <option value="">Önce birim seçiniz</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Başvuruyu Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

// Nationality select değiştiğinde
document.querySelector('select[name="nationality"]').addEventListener('change', function() {
    const otherDiv = document.getElementById('otherNationalityDiv');
    const otherInput = document.getElementById('otherNationality');
    
    if (this.value === 'Diğer') {
        otherDiv.style.display = 'block';
        otherInput.required = true;
        // Diğer seçeneği seçildiğinde, gerçek değeri otherInput'tan al
        otherInput.addEventListener('input', function() {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'nationality_actual';
            hiddenInput.value = this.value;
            this.parentNode.appendChild(hiddenInput);
        });
    } else {
        otherDiv.style.display = 'none';
        otherInput.required = false;
        // Varsa önceki hidden input'u kaldır
        const hiddenInput = document.querySelector('input[name="nationality_actual"]');
        if (hiddenInput) hiddenInput.remove();
    }
});

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Eğitimleri yükle
function loadTrainings(unitId) {
    const trainingSelect = document.getElementById('trainingSelect');
    trainingSelect.disabled = true;
    trainingSelect.innerHTML = '<option value="">Yükleniyor...</option>';

    if (unitId) {
        fetch('get-trainings.php?unit_id=' + unitId)
            .then(response => response.json())
            .then(trainings => {
                trainingSelect.innerHTML = '<option value="">Seçiniz</option>';
                trainings.forEach(training => {
                    trainingSelect.innerHTML += `
                        <option value="${training.id}">
                            ${training.title} (${training.start_date} - ${training.end_date})
                        </option>
                    `;
                });
                trainingSelect.disabled = false;
            });
    } else {
        trainingSelect.innerHTML = '<option value="">Önce birim seçiniz</option>';
    }
}
</script>

<?php include 'includes/footer.php'; ?> 