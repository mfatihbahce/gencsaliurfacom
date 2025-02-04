<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Aktif sayfayı belirle
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// SEO ayarlarını veritabanından çek
$db = Database::getInstance();
$seo = $db->query("SELECT * FROM seo_settings WHERE page_identifier = ?", [$current_page])->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php if($seo): ?>
        <title><?= htmlspecialchars($seo['title']) ?></title>
        <meta name="description" content="<?= htmlspecialchars($seo['description']) ?>">
        <meta name="keywords" content="<?= htmlspecialchars($seo['keywords']) ?>">
        
        <?php if($seo['canonical_url']): ?>
            <link rel="canonical" href="<?= htmlspecialchars($seo['canonical_url']) ?>">
        <?php endif; ?>
        
        <meta name="robots" content="<?= htmlspecialchars($seo['robots']) ?>">
        
        <!-- Open Graph Meta Tags -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?= SITE_URL . '/' . $current_page ?>">
        <meta property="og:title" content="<?= htmlspecialchars($seo['og_title'] ?? $seo['title']) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($seo['og_description'] ?? $seo['description']) ?>">
        <?php if($seo['og_image']): ?>
            <meta property="og:image" content="<?= htmlspecialchars($seo['og_image']) ?>">
        <?php endif; ?>
        
        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="<?= htmlspecialchars($seo['twitter_card'] ?? 'summary_large_image') ?>">
        <meta name="twitter:title" content="<?= htmlspecialchars($seo['og_title'] ?? $seo['title']) ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($seo['og_description'] ?? $seo['description']) ?>">
        <?php if($seo['og_image']): ?>
            <meta name="twitter:image" content="<?= htmlspecialchars($seo['og_image']) ?>">
        <?php endif; ?>
        
        <?php if($seo['schema_type']): ?>
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "<?= htmlspecialchars($seo['schema_type']) ?>",
                "name": "<?= htmlspecialchars($seo['title']) ?>",
                "description": "<?= htmlspecialchars($seo['description']) ?>",
                "url": "<?= SITE_URL . '/' . $current_page ?>"
            }
            </script>
        <?php endif; ?>
    <?php else: ?>
        <title>GENÇ ŞANLIURFA</title>
    <?php endif; ?>
<!-- Head kısmına Font Awesome CDN ekleyin -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        /* Header Styles */
        .top-bar {
            background: #1a237e;
            padding: 8px 0;
            color: #fff;
            font-size: 0.9rem;
        }

        .top-bar a {
            color: #fff;
            text-decoration: none;
            margin-right: 1.5rem;
        }

        .main-header {
            background: #fff;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 0;
        }

        .navbar > .container {
            max-width: 1300px;
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a237e !important;
            padding: 0.8rem 0;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            padding: 1.2rem 0.7rem !important;
            position: relative;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .nav-link i {
            font-size: 0.85rem;
            margin-right: 0.3rem !important;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #1a237e !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: #1a237e;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .auth-buttons .nav-link {
            padding: 0.4rem 0.8rem !important;
            margin: 0.8rem 0.2rem;
            border-radius: 5px;
        }

        .login-btn {
            background: #e3f2fd;
        }

        .register-btn {
            background: #1a237e;
            color: #fff !important;
        }

        .register-btn:hover {
            background: #0d47a1;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 0.5rem;
        }

        .dropdown-item {
            padding: 0.7rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .dropdown-item i {
            margin-right: 0.5rem;
            font-size: 0.85rem;
        }

        .dropdown-item:hover,
        .dropdown-item.active {
            background: #e3f2fd;
            color: #1a237e;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .nav-link {
                padding: 1rem 0.5rem !important;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 991px) {
            .top-bar {
                display: none;
            }

            .nav-link {
                padding: 0.75rem 1rem !important;
            }

            .nav-link::after {
                display: none;
            }

            .dropdown-menu {
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            
            .dropdown-item {
                padding: 0.75rem 1.5rem;
            }
        }
		/* Dropdown ok işaretini kaldır */
.dropdown-toggle::after {
    display: none !important;
}

/* Buton altındaki çizgiyi kaldır */
.nav-link::after {
    display: none !important;
}

/* Hover durumunda da çizgi olmasın */
.nav-link:hover::after,
.nav-link.active::after {
    display: none !important;
}
/* Arena Button Style */
.arena-btn {
    position: relative;
    background: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%);
    color: white !important;
    margin: 0.6rem 0.5rem;  /* Margin'i azalttım */
    padding: 0.4rem 0.8rem !important; /* Padding'i azalttım */
    border-radius: 6px; /* Border radius'u azalttım */
    font-weight: 600 !important;
    text-transform: uppercase;
    font-size: 0.75rem !important; /* Font boyutunu azalttım */
    letter-spacing: 0.3px; /* Letter spacing'i azalttım */
    border: 1px solid transparent; /* Border kalınlığını azalttım */
    box-shadow: 0 2px 8px rgba(255, 75, 43, 0.2); /* Gölgeyi azalttım */
    transition: all 0.3s ease !important;
}

.arena-btn i {
    margin-right: 0.3rem; /* Icon margin'ini azalttım */
    font-size: 0.85rem; /* Icon boyutunu azalttım */
}

.arena-btn::before {
    content: '';
    position: absolute;
    top: -1px;
    left: -1px;
    right: -1px;
    bottom: -1px;
    border-radius: 7px;
    background: linear-gradient(45deg, #FF416C, #FF4B2B, #FF416C);
    z-index: -1;
    animation: glowing 1.5s ease-in-out infinite;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.arena-btn:hover {
    transform: translateY(-1px); /* Hover yüksekliğini azalttım */
    color: white !important;
    box-shadow: 0 4px 12px rgba(255, 75, 43, 0.3); /* Hover gölgesini azalttım */
}

/* Diğer stiller aynı kalabilir */
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="tel:+905555555555"><i class="bi bi-telephone me-1"></i>+90 533 317 8197</a>
                    <a href="mailto:info@gencsanliurfa.com"><i class="bi bi-envelope me-1"></i>info@gencsanliurfa.com</a>
                </div>
                <div>
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="<?= SITE_URL ?>">
                    <i class="bi bi-robot me-2"></i>GENÇ ŞANLIURFA
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <i class="bi bi-list fs-1"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">


                        <!-- İçerikler Dropdown -->
                        <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?= in_array($current_page, ['projects', 'news', 'places']) ? 'active' : '' ?>" 
       href="#" role="button" data-bs-toggle="dropdown">
        <i class="bi bi-bank"></i>Şehrim <i class="bi bi-chevron-down" style="font-size: 12px;"></i>
    </a>
                            <ul class="dropdown-menu">
								<li>
                                    <a class="dropdown-item <?= $current_page == 'about' ? 'active' : '' ?>" 
                                       href="<?= SITE_URL ?>/about">
                                        <i class="bi bi-people-fill"></i> Hakkımızda
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $current_page == 'projects' ? 'active' : '' ?>" 
                                       href="<?= SITE_URL ?>/projects">
                                        <i class="bi bi-gear"></i> Duyurular
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $current_page == 'news' ? 'active' : '' ?>" 
                                       href="<?= SITE_URL ?>/news">
                                        <i class="bi bi-newspaper"></i> Etkinlikler
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $current_page == 'places' ? 'active' : '' ?>" 
                                       href="<?= SITE_URL ?>/places">
                                        <i class="bi bi-geo-alt"></i> Gezilecek Yerler
                                    </a>
                                </li>
                            </ul>
                        </li>

    <!-- Eğitimler Dropdown -->
    <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?= in_array($current_page, ['trainings', 'training-request']) ? 'active' : '' ?>" 
       href="#" role="button" data-bs-toggle="dropdown">
        <i class="bi bi-mortarboard"></i>Eğitimler <i class="bi bi-chevron-down" style="font-size: 12px;"></i>
    </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item <?= $current_page == 'trainings' ? 'active' : '' ?>" 
                   href="<?= SITE_URL ?>/trainings">
                    <i class="bi bi-person-video3"></i> Öğrenciler için Eğitimler
                </a>
            </li>
            <li>
                <a class="dropdown-item <?= $current_page == 'training-request' ? 'active' : '' ?>" 
                   href="<?= SITE_URL ?>/training-request">
                    <i class="bi bi-building"></i> Okullar için Eğitimler
                </a>
            </li>
			<li>
                <a class="dropdown-item <?= $current_page == 'https://docs.google.com/forms/d/e/1FAIpQLSfJn9-vaoUoVpS40Q8F70REaIPc-eQYa_m5Oo4vjl9oHDpS3A/viewform' ? 'active' : '' ?>" 
                   href="https://docs.google.com/forms/d/e/1FAIpQLSfJn9-vaoUoVpS40Q8F70REaIPc-eQYa_m5Oo4vjl9oHDpS3A/viewform">
                    <i class="bi bi-bus-front"></i> Gezici Maker
                </a>
            </li>
        </ul>
    </li>

    <!-- Harita (eski Sınıflarımız) -->
    <li class="nav-item">
        <a class="nav-link <?= $current_page == 'units' ? 'active' : '' ?>" href="<?= SITE_URL ?>/units">
            <i class="bi bi-geo-fill"></i>Harita
        </a>
    </li>
						
<!-- Çocuk Meclisi kısmını bu kodla değiştirin -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?= in_array($current_page, ['children-council', 'council-events', 'child-rights', 'council-news', 'council-contact', 'join-council']) ? 'active' : '' ?>" 
       href="#" role="button" data-bs-toggle="dropdown">
        <i class="fa-solid fa-child-reaching me-1"></i>Çocuk Meclisi
        <i class="bi bi-chevron-down ms-1" style="font-size: 12px;"></i>
    </a>
    <ul class="dropdown-menu council-menu">
        <li>
            <a class="dropdown-item <?= $current_page == '/cocuk_meclisi/cocuk-meclisi-about' ? 'active' : '' ?>" 
               href="<?= SITE_URL ?>/cocuk_meclisi/cocuk-meclisi-about">
                <i class="fa-solid fa-circle-info me-2"></i>Hakkımızda
            </a>
        </li>
        <li>
            <a class="dropdown-item <?= $current_page == '/cocuk_meclisi/cocuk-meclisi-news' ? 'active' : '' ?>" 
               href="<?= SITE_URL ?>/cocuk_meclisi/cocuk-meclisi-news">
                <i class="fa-solid fa-calendar-days me-2"></i>Etkinlikler
            </a>
        </li>
        <li>
            <a class="dropdown-item <?= $current_page == '/#' ? 'active' : '' ?>" 
               href="<?= SITE_URL ?>/#">
                <i class="fa-solid fa-scale-balanced me-2"></i>Çocuk Hakları
            </a>
        </li>
        <li>
            <a class="dropdown-item <?= $current_page == '/#' ? 'active' : '' ?>" 
               href="<?= SITE_URL ?>/#">
                <i class="fa-solid fa-newspaper me-2"></i>Haberler
            </a>
        </li>
        <li>
            <a class="dropdown-item <?= $current_page == '/#' ? 'active' : '' ?>" 
               href="<?= SITE_URL ?>/#">
                <i class="fa-solid fa-comments me-2"></i>İletişim
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item join-council-btn" 
               href="https://cocukmeclis.sanliurfa.bel.tr/" 
               target="_blank" 
               rel="noopener noreferrer">
                <i class="fa-solid fa-handshake-angle me-2"></i>Bize Katıl
                <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 0.8em;"></i>
            </a>
        </li>
    </ul>
</li>
			
						
						<li class="nav-item">
    <a class="nav-link <?= $current_page == 'gencfest' ? 'active' : '' ?>" href="<?= SITE_URL ?>/gencfest">
        <i class="bi bi-stars"></i>GençFest'25
    </a>
</li>
						<!-- Fikrini Paylaş - YENİ -->
<li class="nav-item">
    <a class="nav-link <?= $current_page == 'youth-survey' ? 'active' : '' ?>" href="<?= SITE_URL ?>/youth-survey">
        <i class="bi bi-chat-square-heart-fill"></i>Fikrini Paylaş
    </a>
</li>



                    </ul>
			
					
                    <ul class="navbar-nav auth-buttons">
						<li class="nav-item">
				            <a class="nav-link arena-btn <?= $current_page == 'collect-points' ? 'active' : '' ?>" href="<?= SITE_URL ?>/collect-points">
				                <i class="bi bi-trophy-fill"></i>Arena
				            </a>
				        </li>
                        <?php if (isset($_SESSION['user_id'])): 
                            $user = $db->query("SELECT role FROM users WHERE id = ?", [$_SESSION['user_id']])->fetch();
                            $dashboard_url = ($user['role'] === 'admin') ? SITE_URL . '/admin/dashboard' : SITE_URL . '/admin/userlistedu';
                        ?>
        <li class="nav-item dropdown">
            <a class="nav-link login-btn dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>Hesabım
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
				<li>
                    <a class="dropdown-item" href="<?= $dashboard_url ?>">
                        <i class="bi bi-speedometer2 me-2"></i>Kullanıcı Ekranı
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= SITE_URL ?>/admin/profile">
                        <i class="bi bi-person me-2"></i>Profil Ayarları
                    </a>
                </li>				          
            </ul>
        </li>
                            <li class="nav-item">
                                <a class="nav-link register-btn" href="<?= SITE_URL ?>/logout">
                                    <i class="bi bi-box-arrow-right"></i>Çıkış Yap
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link login-btn" href="<?= SITE_URL ?>/login">
                                    <i class="bi bi-box-arrow-in-right"></i>Giriş
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link register-btn" href="<?= SITE_URL ?>/register">
                                    <i class="bi bi-person-plus"></i>Kayıt Ol
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mt-4">
        <?= showMessages() ?>