<?php
require_once 'config.php';
require_once 'db.php';

// Session başlatma kontrolü
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Oturum ve yetki kontrolleri
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}


// Mevcut showMessages() fonksiyonunu showMessage() olarak güncelleyelim veya yeni bir fonksiyon ekleyelim
function showMessage() {
    $output = '';
    
    if (isset($_SESSION['success'])) {
        $output .= '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        $output .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['error']);
    }
    
    echo $output;
}

/**
 * Admin yetkisi kontrolü - yönlendirme yapar
 */
function requireAdmin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }

    $db = Database::getInstance();
    $user = $db->query(
        "SELECT role FROM users WHERE id = ? LIMIT 1", 
        [$_SESSION['user_id']]
    )->fetch();

    if (!$user || $user['role'] !== 'admin') {
        setError("Bu sayfaya erişim yetkiniz bulunmamaktadır.");
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

/**
 * Admin kontrolü - yönlendirme yapar
 */
function checkAdmin() {
    if (!isAdmin()) {
        setError("Bu sayfaya erişim yetkiniz bulunmamaktadır.");
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

/**
 * Kullanıcının admin olup olmadığını kontrol eder
 * @return bool
 */
function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $db = Database::getInstance();
    $user = $db->query(
        "SELECT role FROM users WHERE id = ? LIMIT 1", 
        [$_SESSION['user_id']]
    )->fetch();

    return ($user && $user['role'] === 'admin');
}

/**
 * Kullanıcının giriş yapıp yapmadığını kontrol eder
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Kullanıcının profil bilgilerinin tam olup olmadığını kontrol eder
 * @param int $userId
 * @return bool
 */
function isProfileComplete($user_id) {
    $db = Database::getInstance();
    
    $user = $db->query(
        "SELECT first_name, last_name, tc_no, birth_date, phone, district_id 
         FROM users 
         WHERE id = ?", 
        [$user_id]
    )->fetch();
    
    // Tüm gerekli alanların dolu olup olmadığını kontrol et
    return $user && 
           !empty($user['first_name']) && 
           !empty($user['last_name']) && 
           !empty($user['tc_no']) && 
           !empty($user['birth_date']) && 
           !empty($user['phone']) && 
           !empty($user['district_id']);
}

// Yönlendirme fonksiyonu
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Mesaj fonksiyonları
function setSuccess($message) {
    $_SESSION['success'] = $message;
}

function setError($message) {
    $_SESSION['error'] = $message;
}

function showMessages() {
    $output = '';
    
    if (isset($_SESSION['success'])) {
        $output .= '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        $output .= '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    
    return $output;
}

// Güvenli metin temizleme
function clean($str) {
    if ($str === null) {
        return '';
    }
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Dosya işlemleri
function uploadFile($file, $upload_path, $allowed_types = ['jpg', 'jpeg', 'png', 'webp']) {
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0777, true);
    }

    if ($file['error'] == 0) {
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed_types) && checkFileSize($file)) {
            $new_filename = time() . '_' . basename($filename);
            $target_path = $upload_path . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                return $new_filename;
            }
        }
    }
    return false;
}

function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function checkFileExtension($filename, $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf']) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed_extensions);
}

function checkFileSize($file, $max_size = 5) {
    return ($file['size'] / 1024 / 1024) <= $max_size;
}

// Format fonksiyonları
function formatMoney($amount) {
    return number_format($amount, 2, ',', '.') . ' TL';
}

function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}

function formatDateTime($date) {
    return date('d.m.Y H:i', strtotime($date));
}

// URL ve string işlemleri
function createSlug($string) {
    $string = str_replace(
        ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'], 
        ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'], 
        $string
    );
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function generateRandomString($length = 10) {
    return substr(
        str_shuffle(
            str_repeat(
                $x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 
                ceil($length/strlen($x))
            )
        ),
        1,
        $length
    );
}

// Güvenli ID kontrolü
function safeInt($value) {
    return filter_var($value, FILTER_VALIDATE_INT) ? $value : 0;
}