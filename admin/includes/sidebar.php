<div class="sidebar bg-dark">
    <div class="sidebar-header">
        <h3 class="text-white">Admin Panel</h3>
    </div>
    <ul class="nav flex-column">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <!-- Anket Sonuçları - YENİ -->
            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/survey-results">
                    <i class="bi bi-bar-chart-fill"></i> Anket Sonuçları
                </a>
            </li>
			<!-- Çocuk Meclisi - YENİ -->
            <li class="nav-item">
                <a class="nav-link" href="#cocukmeclisiabout" data-bs-toggle="collapse" 
                   role="button" aria-expanded="false" aria-controls="cocukmeclisiabout">
                    <i class="bi bi-megaphone-fill"></i> Çocuk Meclisi
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="cocukmeclisiabout">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/cocuk-meclisi-about-manage">
                                <i class="bi bi-info-circle"></i> Meclis Hakkında
                            </a>
                        </li>
                    </ul>
                </div>
				<div class="collapse" id="cocukmeclisiabout">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/cocuk-meclisi-news-manage">
                                <i class="bi bi-calendar-event-fill"></i> Çocuk Meclisi Etkinlikler
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <!-- Bildirim Yönetimi - YENİ -->
            <li class="nav-item">
                <a class="nav-link" href="#submissionsSubmenu" data-bs-toggle="collapse" 
                   role="button" aria-expanded="false" aria-controls="submissionsSubmenu">
                    <i class="bi bi-bell-fill"></i> Bildirim Yönetimi
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="submissionsSubmenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/submissions-manage.php?type=announcement">
                                <i class="bi bi-megaphone-fill"></i> Duyurular
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/submissions-manage.php?type=event">
                                <i class="bi bi-calendar-event-fill"></i> Etkinlikler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/submissions-manage.php?type=place">
                                <i class="bi bi-geo-alt-fill"></i> Yer Bildirimleri
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/about-manage">
                    <i class="bi bi-info-circle"></i> Hakkımızda
                </a>
            </li>
            <!-- ... Diğer menü öğeleri ... -->
            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/gallery-manage">
                    <i class="bi bi-images"></i> Galeri
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/projects-manage">
                    <i class="bi bi-briefcase"></i> Çalışmalar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="news-manage">
                    <i class="bi bi-newspaper"></i> Haber Yönetimi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/contact-manage">
                    <i class="bi bi-envelope"></i> İletişim Mesajları
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/unit-manage">
                    <i class="bi bi-building"></i> Birimler
                </a>
            </li>
			<!-- Eğitimler - YENİ -->
            <li class="nav-item">
                <a class="nav-link" href="#egitimler" data-bs-toggle="collapse" 
                   role="button" aria-expanded="false" aria-controls="egitimler">
                    <i class="bi bi-mortarboard"></i> Eğitimler
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="egitimler">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/training-manage">
                                <i class="bi bi-mortarboard"></i> Eğitimler
                            </a>
                        </li>
                    </ul>
                </div>
				<div class="collapse" id="egitimler">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/training-applications-full">
                                <i class="bi bi-calendar-event-fill"></i> Tüm Başvurular
                            </a>
                        </li>
                    </ul>
                </div>
				<div class="collapse" id="egitimler">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/student-list">
                                <i class="bi bi-people"></i> Öğrenci Listesi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

			<!-- Okul Talep Eğitimler - YENİ -->
            <li class="nav-item">
                <a class="nav-link" href="#okultalepleri" data-bs-toggle="collapse" 
                   role="button" aria-expanded="false" aria-controls="okultalepleri">
                    <i class="bi bi-collection"></i> Okul Eğitim Talepleri
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="okultalepleri">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/training-types">
                                <i class="bi bi-collection"></i> Eğitim Türü
                            </a>
                        </li>
                    </ul>
                </div>
				<div class="collapse" id="okultalepleri">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/admin/training-requests">
                                <i class="bi bi-clipboard-check"></i> Eğitim Talep Listesi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/seo-settings">
                    <i class="bi bi-gear"></i> SEO Ayarları
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= SITE_URL ?>/admin/users">
                    <i class="bi bi-people-fill"></i> Kullanıcılar
                </a>
            </li>

        <?php endif; ?>
        
        <!-- Her kullanıcı için görünür -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>

            <li class="nav-item">
                <a class="nav-link <?= str_contains($current_page, 'userlistedu') ? 'active' : '' ?>" 
                   href="<?= SITE_URL ?>/admin/userlistedu">
                    <i class="bi bi-person-workspace"></i>
                    <span>Kullanıcı Ekranı</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($current_page, 'add-student-registration') ? 'active' : '' ?>" 
                   href="<?= SITE_URL ?>/admin/add-student-registration">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Manuel Başvuru</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<br><br><br><br><br><br>
<style>
/* Sidebar alt menü stilleri */
.sub-menu {
    padding-left: 2rem;
    background-color: rgba(0, 0, 0, 0.1);
}

.sub-menu .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.nav-link i.bi-chevron-down {
    transition: transform 0.3s ease;
}

.nav-link[aria-expanded="true"] i.bi-chevron-down {
    transform: rotate(180deg);
}
</style>