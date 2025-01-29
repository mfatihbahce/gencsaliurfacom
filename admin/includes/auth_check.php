<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

// Session kontrolü
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı giriş yapmamışsa login sayfasına yönlendir
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/login');
    exit;
}

// Kullanıcı rolünü kontrol et
$db = Database::getInstance();
$user = $db->query("SELECT role FROM users WHERE id = ?", [$_SESSION['user_id']])->fetch();

// İzin verilen sayfalar listesi (role göre)
$allowed_pages = [
    'user' => [
        'userlistedu.php',
        'profile.php',
        // Kullanıcının erişebileceği diğer sayfalar
    ],
    'admin' => [
        // Admin tüm sayfalara erişebilir
        '*'
    ]
];

// Mevcut sayfayı al
$current_page = basename($_SERVER['PHP_SELF']);

// Kullanıcının rolüne göre sayfa erişimini kontrol et
if ($user['role'] !== 'admin') {
    // Admin değilse ve izin verilen sayfalar listesinde yoksa engelle
    if (!in_array($current_page, $allowed_pages['user'])) {
        $_SESSION['error'] = "Bu sayfaya erişim yetkiniz bulunmamaktadır.";
        header('Location: ' . SITE_URL . '/admin/userlistedu');
        exit;
    }
}