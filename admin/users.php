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

// Kullanıcıları getir
$users = $db->query("
    SELECT 
        id,
        username,
        email,
        first_name,
        last_name,
        phone,
        role,
        created_at
    FROM users 
    ORDER BY created_at DESC
")->fetchAll();

$page_title = 'Kullanıcı Yönetimi';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Kullanıcı Yönetimi</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Yeni Kullanıcı
        </button>
    </div>

    <?= showMessages() ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Telefon</th>
                            <th>Rol</th>
                            <th>Kayıt Tarihi</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-2">
                                            <div class="avatar-initial rounded-circle bg-primary text-white" 
                                                 style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <?= !empty($user['first_name']) ? strtoupper(substr($user['first_name'], 0, 1)) : '?' ?>
                                            </div>
                                        </div>
                                        <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($user['role'] ?? '') === 'admin' ? 'danger' : 'primary' ?>">
                                        <?= ($user['role'] ?? '') === 'admin' ? 'Admin' : 'Kullanıcı' ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Kullanıcı Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Kullanıcı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="user_actions.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        <input type="text" name="first_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Soyad</label>
                        <input type="text" name="last_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-select">
                            <option value="user">Kullanıcı</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" name="action" value="add" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables ve diğer JS'ler -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
        },
        order: [[0, 'desc']]
    });
});

function editUser(id) {
    // AJAX ile kullanıcı bilgilerini getir ve düzenleme modalını aç
    // Bu fonksiyonu daha sonra implement edeceğiz
}

function deleteUser(id) {
    if (confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')) {
        window.location.href = 'user_actions.php?action=delete&id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>