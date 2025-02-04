<?php
session_start();

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_USER', 'gencs_anliurfa');
define('DB_PASS', 'Sekiztane1*');
define('DB_NAME', 'gencsanliurfa_com_pclab');


// Mevcut config kodlarının başına ekleyin
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Site URL
define('SITE_URL', 'https://gencsanliurfa.com');

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Upload dizinleri
define('GALLERY_UPLOAD_PATH', '../uploads/gallery/');
define('PROJECTS_UPLOAD_PATH', '../uploads/projects/');
define('TAX_PAPERS_PATH', '../uploads/tax_papers/');
define('ROOM_REGISTRATIONS_PATH', '../uploads/room_registrations/'); 


// Twitter API Credentials
define('TWITTER_CONSUMER_KEY', 'YOUR_CONSUMER_KEY');
define('TWITTER_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET');
define('TWITTER_ACCESS_TOKEN', '1853896294118375424-UHIJvcVj8OrIcqGLpCG9T0F7LaKH6q');
define('TWITTER_ACCESS_TOKEN_SECRET', 'B5nBY3RygqFqdyOctCOyudFNx0JPV6Fry9xf5XLYT52e2');
define('TWITTER_USERNAME', 'mfatihbahce');