<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php';

// Sadece admin erişebilir
if ($user['role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/admin/dashboard');
    exit;
}

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Yeni kullanıcı ekle
        $username = $_POST['username'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        try {
            // E-posta ve kullanıcı adı kontrolü
            $existing = $db->query(
                "SELECT id FROM users WHERE email = ? OR username = ?", 
                [$email, $username]
            )->fetch();

            if ($existing) {
                $_SESSION['error'] = "Bu e-posta adresi veya kullanıcı adı zaten kullanılıyor.";
                header('Location: users.php');
                exit;
            }

            // Kullanıcıyı ekle
            $db->beginTransaction();

            // Users tablosuna ekle
            $db->query(
                "INSERT INTO users (username, email, phone, password, role, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$username, $email, $phone, $password, $role]
            );
            
            $user_id = $db->lastInsertId();

            // User details tablosuna ekle
            if (!empty($first_name) || !empty($last_name)) {
                $db->query(
                    "INSERT INTO user_details (user_id, first_name, last_name, phone) 
                     VALUES (?, ?, ?, ?)",
                    [$user_id, $first_name, $last_name, $phone]
                );
            }

            $db->commit();
            $_SESSION['success'] = "Kullanıcı başarıyla eklendi.";

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Kullanıcı eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
    elseif ($action === 'edit') {
        // Kullanıcı düzenle
        $id = $_POST['id'];
        $username = $_POST['username'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $role = $_POST['role'];
        
        try {
            // E-posta ve kullanıcı adı kontrolü (kendi ID'si hariç)
            $existing = $db->query(
                "SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?", 
                [$email, $username, $id]
            )->fetch();

            if ($existing) {
                $_SESSION['error'] = "Bu e-posta adresi veya kullanıcı adı başka bir kullanıcı tarafından kullanılıyor.";
                header('Location: users.php');
                exit;
            }

            $db->beginTransaction();

            // Users tablosunu güncelle
            $db->query(
                "UPDATE users 
                 SET username = ?, email = ?, phone = ?, role = ? 
                 WHERE id = ?",
                [$username, $email, $phone, $role, $id]
            );

            // User details tablosunu güncelle
            $details = $db->query(
                "SELECT id FROM user_details WHERE user_id = ?", 
                [$id]
            )->fetch();

            if ($details) {
                $db->query(
                    "UPDATE user_details 
                     SET first_name = ?, last_name = ?, phone = ? 
                     WHERE user_id = ?",
                    [$first_name, $last_name, $phone, $id]
                );
            } else {
                $db->query(
                    "INSERT INTO user_details (user_id, first_name, last_name, phone) 
                     VALUES (?, ?, ?, ?)",
                    [$id, $first_name, $last_name, $phone]
                );
            }

            // Şifre değişikliği varsa
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $db->query(
                    "UPDATE users SET password = ? WHERE id = ?",
                    [$password, $id]
                );
            }

            $db->commit();
            $_SESSION['success'] = "Kullanıcı başarıyla güncellendi.";

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Kullanıcı güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
    
    header('Location: users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? 0;
    
    if ($action === 'delete' && $id) {
        try {
            // Admin kendini silemesin
            if ($id == $_SESSION['user_id']) {
                $_SESSION['error'] = "Kendi hesabınızı silemezsiniz.";
                header('Location: users.php');
                exit;
            }

            $db->beginTransaction();

            // Önce user_details tablosundan sil
            $db->query("DELETE FROM user_details WHERE user_id = ?", [$id]);
            
            // Sonra users tablosundan sil
            $db->query("DELETE FROM users WHERE id = ?", [$id]);

            $db->commit();
            $_SESSION['success'] = "Kullanıcı başarıyla silindi.";

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Kullanıcı silinirken bir hata oluştu: " . $e->getMessage();
        }
        
        header('Location: users.php');
        exit;
    }
    elseif ($action === 'get' && $id) {
        // AJAX için kullanıcı bilgilerini getir
        try {
            $userData = $db->query(
                "SELECT u.*, ud.first_name, ud.last_name 
                 FROM users u 
                 LEFT JOIN user_details ud ON u.id = ud.user_id 
                 WHERE u.id = ?", 
                [$id]
            )->fetch();

            if ($userData) {
                echo json_encode([
                    'success' => true,
                    'user' => $userData
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı'
                ]);
            }
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}

// Geçersiz istek
header('Location: users.php');
exit;