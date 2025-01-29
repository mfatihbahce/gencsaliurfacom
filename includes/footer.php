</div> <!-- Ana container kapanışı -->

<footer class="modern-footer">
    <!-- Footer Top - İletişim Çubuğu -->


    <!-- Footer Main -->
    <div class="footer-main py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Logo ve Açıklama -->
                <div class="col-lg-4">
                    <div class="footer-brand mb-4">
                        <h3 class="text-white">GENÇ ŞANLIURFA</h3>
                        <div class="brand-divider"></div>
                    </div>
                    <p class="footer-description">
                        Teknoloji ve eğitimin geleceğini şekillendiriyoruz. 
                        Robotik, kodlama ve yazılım eğitimleriyle geleceğe hazırlanın.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <!-- Hızlı Bağlantılar -->
                <div class="col-lg-4">
                    <h5 class="footer-title">Hızlı Bağlantılar</h5>
                    <div class="row">
                        <div class="col-6">
                            <ul class="footer-links">
                                <li><a href="<?= SITE_URL ?>/about.php">Hakkımızda</a></li>
                                <li><a href="<?= SITE_URL ?>/news.php">Haberler</a></li>
                            </ul>
                            <ul class="footer-links">
                            <li><a href="<?= SITE_URL ?>/trainings.php">Eğitimler</a></li>
                            <li><a href="<?= SITE_URL ?>/contact.php">İletişim</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- İletişim Bilgileri -->
                <div class="col-lg-4">
                    <h5 class="footer-title">İletişim</h5>
                    <ul class="footer-contact-list">
                        <li>
                            <i class="bi bi-geo-alt-fill"></i>
                            <div>
                                <strong>Adres:</strong>
                                <p class="mb-0">Bamyasuyu Mahallesi 154. Sokak No:2 Haliliye / Şanlıurfa</p>
                            </div>
                        </li>
                        <li>
                            <i class="bi bi-clock-fill"></i>
                            <div>
                                <strong>Çalışma Saatleri:</strong>
                                <p class="mb-0">Pazartesi - Cuma: 08:00 - 17:00</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">© <?= date('Y') ?> GENÇ ŞANLIURFA. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.modern-footer {
    background-color: #2b2d42;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 0;
}

.footer-contact-bar {
    background-color: #ef233c;
    padding: 15px 0;
}

.footer-contact-bar a {
    transition: opacity 0.3s ease;
}

.footer-contact-bar a:hover {
    opacity: 0.8;
}

.footer-main {
    background-color: #2b2d42;
}

.footer-brand h3 {
    margin-bottom: 0;
    font-weight: 600;
}

.brand-divider {
    width: 50px;
    height: 3px;
    background-color: #ef233c;
    margin: 15px 0;
}

.footer-description {
    margin-bottom: 25px;
    line-height: 1.6;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-link {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
}

.social-link:hover {
    color: #fff;
    background: #ef233c;
    transform: translateY(-3px);
}

.footer-title {
    color: #fff;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 30px;
    height: 2px;
    background: #ef233c;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: #fff;
    padding-left: 5px;
}

.footer-contact-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact-list li {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.footer-contact-list i {
    color: #ef233c;
    font-size: 1.2rem;
}

.footer-contact-list strong {
    color: #fff;
    display: block;
    margin-bottom: 5px;
}

.footer-bottom {
    background: rgba(0, 0, 0, 0.2);
    font-size: 0.9rem;
}

.footer-bottom a {
    color: rgba(255, 255, 255, 0.7);
    transition: color 0.3s ease;
}

.footer-bottom a:hover {
    color: #fff;
}

@media (max-width: 768px) {
    .footer-contact-bar {
        text-align: center;
    }
    
    .footer-contact-bar h4 {
        margin-bottom: 15px;
    }
    
    .footer-title {
        margin-top: 20px;
    }
    
    .footer-bottom {
        text-align: center;
    }
    
    .footer-bottom .text-md-end {
        margin-top: 10px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html> 