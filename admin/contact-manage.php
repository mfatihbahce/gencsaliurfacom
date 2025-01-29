<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Mesaj silme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $db->query("DELETE FROM contact_messages WHERE id = ?", [$id]);
    if ($stmt) {
        setSuccess('Mesaj başarıyla silindi.');
    } else {
        setError('Mesaj silinirken bir hata oluştu.');
    }
}

// Mesajları getir (sayfalama ile)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$total_messages = $db->query("SELECT COUNT(*) as count FROM contact_messages")->fetch()['count'];
$total_pages = ceil($total_messages / $per_page);

$messages = $db->query(
    "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?",
    [$per_page, $offset]
)->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>İletişim Mesajları</h2>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($messages): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>İsim</th>
                            <th>E-posta</th>
                            <th>Konu</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($message['created_at'])) ?></td>
                                <td><?= clean($message['name']) ?></td>
                                <td><?= clean($message['email']) ?></td>
                                <td><?= clean($message['subject']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#messageModal<?= $message['id'] ?>">
                                        Görüntüle
                                    </button>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Bu mesajı silmek istediğinizden emin misiniz?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $message['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Mesaj Detay Modal -->
                            <div class="modal fade" id="messageModal<?= $message['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Mesaj Detayı</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Gönderen:</strong> <?= clean($message['name']) ?></p>
                                            <p><strong>E-posta:</strong> <?= clean($message['email']) ?></p>
                                            <p><strong>Konu:</strong> <?= clean($message['subject']) ?></p>
                                            <p><strong>Mesaj:</strong></p>
                                            <div class="border p-3 bg-light">
                                                <?= nl2br(clean($message['message'])) ?>
                                            </div>
                                            <p class="text-muted mt-2">
                                                <small>Gönderilme Tarihi: <?= date('d.m.Y H:i', strtotime($message['created_at'])) ?></small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sayfalama -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Sayfalama" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center">Henüz mesaj bulunmuyor.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 