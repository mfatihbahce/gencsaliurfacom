<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Form verileri başarıyla gönderildiyse session'ı temizle
if (isset($_SESSION['form_data']) && !isset($_SESSION['error'])) {
    unset($_SESSION['form_data']);
}

$page_title = "Gençlik Hizmetleri Anketi";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Başlık Kartı -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center p-5">
                    <h1 class="display-6 mb-3">Gençlik Hizmetleri Anketi</h1>
                    <p class="lead text-muted">
                        Değerli gençlerimiz, sizin için daha iyi hizmetler sunabilmek adına 
                        görüşleriniz bizim için çok değerli.
                    </p>
                </div>
            </div>

            <?php echo showMessages(); ?>

            <!-- Anket Formu -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form id="youthSurvey" action="process-survey.php" method="POST" class="needs-validation" novalidate>
                        
                        <!-- 1. GENEL BİLGİLER -->
                        <div class="section mb-5" id="error-section">
                            <h4 class="border-bottom pb-2 mb-4">
                                <i class="bi bi-person-circle me-2"></i>1. Genel Bilgileriniz
                            </h4>
                            
                            <!-- Yaş Grubu -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Yaşınız <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <?php
                                    $ageGroups = ['10-13', '14-17', '18-24', '25-30'];
                                    $selected_age = $_SESSION['form_data']['age_group'] ?? '';
                                    foreach ($ageGroups as $age) {
                                        $checked = ($selected_age == $age) ? 'checked' : '';
                                        echo '<input type="radio" class="btn-check" name="age_group" 
                                                     id="age_'.$age.'" value="'.$age.'" '.$checked.' required>
                                              <label class="btn btn-outline-primary" for="age_'.$age.'">'.$age.'</label>';
                                    }
                                    ?>
                                </div>
                                <div class="invalid-feedback">Lütfen yaş grubunuzu seçin.</div>
                            </div>

                            <!-- Cinsiyet -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Cinsiyetiniz <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <?php $selected_gender = $_SESSION['form_data']['gender'] ?? ''; ?>
                                    <input type="radio" class="btn-check" name="gender" id="gender_female" 
                                           value="kadın" <?php echo ($selected_gender == 'kadın') ? 'checked' : ''; ?> required>
                                    <label class="btn btn-outline-primary" for="gender_female">Kadın</label>
                                    
                                    <input type="radio" class="btn-check" name="gender" id="gender_male" 
                                           value="erkek" <?php echo ($selected_gender == 'erkek') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary" for="gender_male">Erkek</label>

                                </div>
                            </div>
							                            <!-- Meclis Üyeliği -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Çocuk Meclisine Üye Misiniz? <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <?php $selected_member = $_SESSION['form_data']['is_council_member'] ?? ''; ?>
                                    <input type="radio" class="btn-check" name="is_council_member" id="council_yes" 
                                           value="1" <?php echo ($selected_member == '1') ? 'checked' : ''; ?> required>
                                    <label class="btn btn-outline-primary" for="council_yes">Evet</label>
                                    
                                    <input type="radio" class="btn-check" name="is_council_member" id="council_no" 
                                           value="0" <?php echo ($selected_member == '0') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary" for="council_no">Hayır</label>
                                </div>
                            </div>
                        </div>

                        <!-- 2. HİZMET TERCİHLERİ -->
                        <div class="section mb-5">
                            <h4 class="border-bottom pb-2 mb-4">
                                <i class="bi bi-list-check me-2"></i>2. Belediyeden Beklentileriniz
                            </h4>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                En fazla 3 seçenek işaretleyebilirsiniz.
                            </div>

                            <?php $desired_services = $_SESSION['form_data']['desired_services'] ?? []; ?>

                            <div class="row g-4 mb-4">
                                <!-- Eğitim ve Atölyeler -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Ücretsiz Eğitim ve Atölyeler</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="sanat" 
                                                       <?php echo in_array('sanat', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Sanat</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="dil_kursu"
                                                       <?php echo in_array('dil_kursu', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Dil Kursları</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="kodlama"
                                                       <?php echo in_array('kodlama', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Kodlama ve Teknoloji</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="kişisel_gelişim"
                                                       <?php echo in_array('kişisel_gelişim', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Kişisel Gelişim ve Liderlik</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Spor ve Açık Hava -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Spor ve Açık Hava Etkinlikleri</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="spor_alanları"
                                                       <?php echo in_array('spor_alanları', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Yeni Spor Alanları</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="açık_hava"
                                                       <?php echo in_array('açık_hava', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Açık Hava Etkinlikleri</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="turnuvalar"
                                                       <?php echo in_array('turnuvalar', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Gençlere Özel Spor Turnuvaları</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="doga_yuruyusu"
                                                       <?php echo in_array('doga_yuruyusu', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Doğa Yürüyüşleri ve Kamp Etkinlikleri</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sosyal ve Kültürel -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Sosyal ve Kültürel Projeler</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="festivaller"
                                                       <?php echo in_array('festivaller', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Gençlik Festivalleri ve Konserler</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="sinema"
                                                       <?php echo in_array('sinema', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Film ve Tiyatro Etkinlikleri</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="kitap_okuma"
                                                       <?php echo in_array('kitap_okuma', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Kitap Okuma Etkinlikleri</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="kültür_gezileri"
                                                       <?php echo in_array('kültür_gezileri', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Yerel Tarih ve Kültür Gezileri</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Kariyer ve İstihdam -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Kariyer ve İstihdam</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="cv_hazirlama"
                                                       <?php echo in_array('cv_hazirlama', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">CV Hazırlama ve Mülakat Teknikleri Eğitimleri</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="istihdam"
                                                       <?php echo in_array('istihdam', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">İstihdam Odaklı Kurslar</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="staj_is"
                                                       <?php echo in_array('staj_is', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Staj ve İş Bulma Programları</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input service-check" type="checkbox" 
                                                       name="desired_services[]" value="genc_girisimciler"
                                                       <?php echo in_array('genc_girisimciler', $desired_services) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Genç Girişimciler İçin Destek ve İş Fikirleri Atölyeleri</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>								
                            </div>
							                            <!-- Hizmet Detayları -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Seçtiğiniz hizmetlerle ilgili eklemek istedikleriniz:</label>
                                <textarea class="form-control" name="service_details" rows="3" 
                                          placeholder="Örneğin: Hangi saatlerde olsun, nerede olsun, nasıl olsun..."
                                          ><?php echo $_SESSION['form_data']['service_details'] ?? ''; ?></textarea>
                            </div>
                        </div>

                        <!-- 3. İLETİŞİM TERCİHLERİ -->
                        <div class="section mb-5">
                            <h4 class="border-bottom pb-2 mb-4">
                                <i class="bi bi-chat-dots me-2"></i>3. İletişim Tercihleri
                            </h4>

                            <?php 
                            $communication_channels = $_SESSION['form_data']['communication_channels'] ?? [];
                            $feedback_channels = $_SESSION['form_data']['feedback_channels'] ?? [];
                            ?>

                            <!-- İletişim Kanalları -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Belediye gençlerle iletişim için hangi kanalları kullanmalı?</label>
                                <div class="row g-3">
                                    <?php
                                    $channels = [
                                        'web_sitesi' => 'Genç Şanlıurfa İnternet Sitesi',
                                        'çocuk_meclisi' => 'Çocuk Meclisi',
                                        'sosyal_medya' => 'Sosyal Medya Platformları',
                                        'mobil_uygulama' => 'Mobil Uygulama',
                                        'toplantılar' => 'Toplantı ve Yüz Yüze Görüşmeler'
                                    ];
                                    foreach ($channels as $value => $label) {
                                        echo '<div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="communication_channels[]" value="'.$value.'"
                                                       '.(in_array($value, $communication_channels) ? 'checked' : '').'>
                                                <label class="form-check-label">'.$label.'</label>
                                            </div>
                                        </div>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Geri Bildirim Kanalları -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Geri bildirimleriniz için hangi kanalları tercih edersiniz?</label>
                                <div class="row g-3">
                                    <?php
                                    $feedbackChannels = [
                                        'web_sitesi' => 'Genç Şanlıurfa İnternet Sitesi',
                                        'sosyal_medya' => 'Sosyal Medya',
                                        'eposta' => 'E-posta',
                                        'mobil_uygulama' => 'Mobil Uygulama',
                                        'telefon' => 'Telefon',
                                        'yüz_yüze' => 'Yüz Yüze Görüşme'
                                    ];
                                    foreach ($feedbackChannels as $value => $label) {
                                        echo '<div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="feedback_channels[]" value="'.$value.'"
                                                       '.(in_array($value, $feedback_channels) ? 'checked' : '').'>
                                                <label class="form-check-label">'.$label.'</label>
                                            </div>
                                        </div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- 4. DEĞERLENDİRME -->
                        <div class="section mb-5">
                            <h4 class="border-bottom pb-2 mb-4">
                                <i class="bi bi-star me-2"></i>4. Değerlendirme ve Öneriler
                            </h4>

                            <!-- Etkinlik Önerisi -->
                            <div class="mb-4">
                                <label class="form-label">Düzenlenmesini istediğiniz bir etkinlik veya proje öneriniz var mı?</label>
                                <textarea class="form-control" name="activity_suggestion" rows="3"
                                          ><?php echo $_SESSION['form_data']['activity_suggestion'] ?? ''; ?></textarea>
                            </div>

                            <!-- Mevcut Hizmet Değerlendirmesi -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Belediyenin gençlere sağladığı mevcut hizmetler hakkında ne düşünüyorsunuz? <span class="text-danger">*</span></label>
                                <?php $selected_rating = $_SESSION['form_data']['service_rating'] ?? ''; ?>
                                <select class="form-select" name="service_rating" required>
                                    <option value="">Seçiniz</option>
                                    <option value="çok_iyi" <?php echo ($selected_rating == 'çok_iyi') ? 'selected' : ''; ?>>Çok İyi</option>
                                    <option value="iyi" <?php echo ($selected_rating == 'iyi') ? 'selected' : ''; ?>>İyi</option>
                                    <option value="orta" <?php echo ($selected_rating == 'orta') ? 'selected' : ''; ?>>Orta</option>
                                    <option value="geliştirilmeli" <?php echo ($selected_rating == 'geliştirilmeli') ? 'selected' : ''; ?>>Geliştirilmesi Gerekiyor</option>
                                    <option value="yetersiz" <?php echo ($selected_rating == 'yetersiz') ? 'selected' : ''; ?>>Yetersiz</option>
                                </select>
                            </div>
							                            <!-- Önceki İletişim -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Daha önce belediye ile iletişime geçtiniz mi? <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <?php $previous_contact = $_SESSION['form_data']['previous_contact'] ?? ''; ?>
                                    <input type="radio" class="btn-check" name="previous_contact" id="contact_yes" 
                                           value="1" <?php echo ($previous_contact == '1') ? 'checked' : ''; ?> required>
                                    <label class="btn btn-outline-primary" for="contact_yes">Evet</label>
                                    
                                    <input type="radio" class="btn-check" name="previous_contact" id="contact_no" 
                                           value="0" <?php echo ($previous_contact == '0') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary" for="contact_no">Hayır</label>
                                </div>
                            </div>

                            <!-- İletişim Yöntemi -->
                            <div id="contactMethodSection" class="mb-4" style="display: none;">
                                <label class="form-label">Nasıl iletişime geçtiniz?</label>
                                <?php $contact_method = $_SESSION['form_data']['contact_method'] ?? ''; ?>
                                <select class="form-select" name="contact_method">
                                    <option value="">Seçiniz</option>
                                    <option value="sosyal_medya" <?php echo ($contact_method == 'sosyal_medya') ? 'selected' : ''; ?>>Sosyal Medya</option>
                                    <option value="telefon" <?php echo ($contact_method == 'telefon') ? 'selected' : ''; ?>>Telefon</option>
                                    <option value="eposta" <?php echo ($contact_method == 'eposta') ? 'selected' : ''; ?>>E-posta</option>
                                    <option value="web_sitesi" <?php echo ($contact_method == 'web_sitesi') ? 'selected' : ''; ?>>Web Sitesi</option>
                                    <option value="yüz_yüze" <?php echo ($contact_method == 'yüz_yüze') ? 'selected' : ''; ?>>Yüz Yüze</option>
                                    <option value="çocuk_meclisi" <?php echo ($contact_method == 'çocuk_meclisi') ? 'selected' : ''; ?>>Çocuk Meclisi</option>
                                </select>
                            </div>

                            <!-- Ek Görüşler -->
                            <div class="mb-4">
                                <label class="form-label">Eklemek istediğiniz başka görüş veya önerileriniz var mı?</label>
                                <textarea class="form-control" name="additional_feedback" rows="3"
                                          ><?php echo $_SESSION['form_data']['additional_feedback'] ?? ''; ?></textarea>
                            </div>
                        </div>

                        <!-- İLETİŞİM BİLGİSİ -->
                        <div class="section mb-5">
                            <div class="mb-4">
                                <label class="form-label">İletişim Bilgisi (İsteğe bağlı)</label>
                                <input type="text" class="form-control" name="contact_info" 
                                       placeholder="E-posta veya telefon numaranız"
                                       value="<?php echo $_SESSION['form_data']['contact_info'] ?? ''; ?>">
                                <div class="form-text">
                                    Bu bilgiyi paylaşırsanız, görüşlerinizle ilgili sizinle iletişime geçebiliriz.
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-2"></i>Anketi Gönder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Maksimum 3 seçim kontrolü
    const serviceChecks = document.querySelectorAll('.service-check');
    serviceChecks.forEach(check => {
        check.addEventListener('change', function() {
            const checked = document.querySelectorAll('.service-check:checked');
            if (checked.length > 3) {
                this.checked = false;
                alert('En fazla 3 seçenek işaretleyebilirsiniz!');
            }
        });
    });

    // Önceki iletişim sorusu kontrolü
    const previousContactInputs = document.querySelectorAll('input[name="previous_contact"]');
    const contactMethodSection = document.getElementById('contactMethodSection');

    previousContactInputs.forEach(input => {
        input.addEventListener('change', function() {
            contactMethodSection.style.display = this.value === '1' ? 'block' : 'none';
        });
    });

    // Sayfa yüklendiğinde önceki iletişim durumunu kontrol et
    const checkedContact = document.querySelector('input[name="previous_contact"]:checked');
    if (checkedContact && checkedContact.value === '1') {
        contactMethodSection.style.display = 'block';
    }

    // Form validasyonu
    const form = document.getElementById('youthSurvey');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Hata bölümüne scroll
    if (window.location.hash === '#error-section') {
        document.getElementById('error-section').scrollIntoView();
    }
});
</script>

<?php include 'includes/footer.php'; ?>