<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkAdmin();

$db = Database::getInstance();

// Filtreleme ve arama parametreleri

$search = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$filter_tax = isset($_GET['tax_paper']) ? $_GET['tax_paper'] : '';
$filter_room = isset($_GET['room_registration']) ? $_GET['room_registration'] : '';

// SQL sorgusu oluşturma
$sql = "SELECT * FROM members WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (member_no LIKE ? OR tc_no LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if ($filter_tax) {
    $sql .= " AND tax_paper = ?";
    $params[] = $filter_tax;
}

if ($filter_room) {
    $sql .= " AND room_registration = ?";
    $params[] = $filter_room;
}

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Toplam kayıt sayısı
$count_sql = str_replace("SELECT *", "SELECT COUNT(*) as count", $sql);
$total_count = $db->query($count_sql, $params)->fetch()['count'];
$total_pages = ceil($total_count / $per_page);

// Sayfalı sonuçları getir
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = (int)$per_page; // Integer olarak gönder
$params[] = (int)$offset;   // Integer olarak gönder

$members = $db->query($sql, $params)->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Üye Listesi</h2>
</div>

<div class="card">
    <div class="card-body">
        <!-- Arama ve filtreleme formu -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" 
                       placeholder="Üye No, TC No veya Ad Soyad" value="<?= clean($search) ?>">
            </div>
            <div class="col-md-3">
                <select class="form-control" name="tax_paper">
                    <option value="">Vergi Levhası (Tümü)</option>
                    <option value="var" <?= $filter_tax == 'var' ? 'selected' : '' ?>>Var</option>
                    <option value="yok" <?= $filter_tax == 'yok' ? 'selected' : '' ?>>Yok</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" name="room_registration">
                    <option value="">Oda Kaydı (Tümü)</option>
                    <option value="var" <?= $filter_room == 'var' ? 'selected' : '' ?>>Var</option>
                    <option value="yok" <?= $filter_room == 'yok' ? 'selected' : '' ?>>Yok</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>

        <?php if ($members): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Üye No</th>
                            <th>TC No</th>
                            <th>Ad Soyad</th>
                            <th>Vergi Levhası</th>
                            <th>Oda Kaydı</th>
                            <th>Peşinat</th>
                            <th>Kayıt Tarihi</th>
                            <th>Detay</th>
                            <th>Düzenle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?= clean($member['member_no']) ?></td>
                                <td><?= clean($member['tc_no']) ?></td>
                                <td><?= clean($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                <td><?= $member['tax_paper'] ?></td>
                                <td><?= $member['room_registration'] ?></td>
                                <td><?= number_format($member['deposit_amount'], 2, ',', '.') ?> TL</td>
                                <td><?= date('d.m.Y', strtotime($member['created_at'])) ?></td>
                                <td>
                                    <!-- Mevcut tablo içindeki detay butonu kısmını şu şekilde güncelleyin -->

    <a href="member-detail.php?id=<?= $member['id'] ?>" class="btn btn-info btn-sm">
        <i class="bi bi-eye"></i> Detay
    </a>
    </td>
    <td>
    <a href="member-edit.php?id=<?= $member['id'] ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil"></i> Düzenle
    </a>
    <!-- 
    <button type="button" class="btn btn-danger btn-sm" 
            onclick="confirmDelete(<?= $member['id'] ?>)">
        <i class="bi bi-trash"></i> Sil
    </button>
    -->
</td>
                                </td>
                            </tr>
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
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tax_paper=<?= $filter_tax ?>&room_registration=<?= $filter_room ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center">Kayıtlı üye bulunamadı.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>