<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AKABESANAYİ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
    :root {
        --primary: #0B2447;
        --secondary: #19376D;
        --accent: #576CBC;
        --light: #A5D7E8;
    }

    .navbar {
        background: var(--primary);
        padding: 0;
    }

    .navbar-brand {
        background: var(--secondary);
        padding: 20px 30px;
        color: white !important;
        font-weight: 600;
        font-size: 22px;
        position: relative;
        margin-right: 50px;
    }

    .navbar-brand::after {
        content: '';
        position: absolute;
        right: -30px;
        top: 0;
        border-style: solid;
        border-width: 34px 0 34px 30px;
        border-color: transparent transparent transparent var(--secondary);
    }

    .nav-link {
        color: white !important;
        padding: 25px 20px !important;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 3px;
        background: var(--light);
        transition: 0.3s;
    }

    .nav-link:hover::before,
    .nav-link.active::before {
        left: 0;
    }

    .nav-link:hover {
        background: var(--secondary);
    }

    .nav-link.active {
        background: var(--secondary);
    }

    .nav-link i {
        margin-right: 6px;
    }

    .auth-btn {
        padding: 8px 25px;
        border-radius: 4px;
        color: white !important;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 500;
    }

    .login-btn {
        background: var(--accent);
    }

    .login-btn:hover {
        background: var(--secondary);
    }

    .register-btn {
        background: var(--light);
        color: var(--primary) !important;
    }

    .register-btn:hover {
        background: white;
    }

    .navbar-toggler {
        border: none;
        color: white;
        padding: 15px;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }

    @media (max-width: 991px) {
        .navbar-collapse {
            background: var(--secondary);
            padding: 15px;
        }
        
        .nav-link {
            padding: 12px 20px !important;
            border-radius: 4px;
        }
        
        .auth-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        
        .auth-btn {
            flex: 1;
            text-align: center;
        }
    }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>">
                <i class="bi bi-building-gear me-2"></i>AKABE
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'about.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/about.php">
                            <i class="bi bi-building"></i>Hakkımızda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'projects.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/projects.php">
                            <i class="bi bi-gear"></i>Projeler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'gallery.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/gallery.php">
                            <i class="bi bi-images"></i>Galeri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'contact.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/contact.php">
                            <i class="bi bi-telephone"></i>İletişim
                        </a>
                    </li>
                </ul>

                <div class="auth-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="auth-btn login-btn">
                            <i class="bi bi-speedometer2"></i> Panel
                        </a>
                        <a href="<?= SITE_URL ?>/logout.php" class="auth-btn register-btn">
                            <i class="bi bi-box-arrow-right"></i> Çıkış
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/login.php" class="auth-btn login-btn">
                            <i class="bi bi-box-arrow-in-right"></i> Giriş
                        </a>
                        <a href="<?= SITE_URL ?>/register.php" class="auth-btn register-btn">
                            <i class="bi bi-person-plus"></i> Kayıt
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?= showMessages() ?>