<?php
require_once '../../includes/db.php';
require_once '../../includes/admin_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        // Bildirimi onayla
        $sql = "UPDATE notifications SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Kullanıcıya puan ekle
            $sql = "INSERT INTO user_points (user_id, points) 
                   SELECT user_id, 50 FROM notifications WHERE id = ? 
                   ON DUPLICATE KEY UPDATE points = points + 50";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
        }
    } else if ($action === 'reject') {
        // Bildirimi reddet
        $sql = "UPDATE notifications SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        }
    }
}
?>