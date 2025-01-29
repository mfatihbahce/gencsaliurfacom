<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth_check.php'; // Yetki kontrolü
checkLogin(); // User veya Admin olabilir

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_no = filter_input(INPUT_POST, 'member_no', FILTER_SANITIZE_STRING);
    $tc_no = filter_input(INPUT_POST, 'tc_no', FILTER_SANITIZE_STRING);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $tax_paper = $_POST['tax_paper'];
    $room_registration = $_POST['room_registration'];
    $deposit_amount = filter_input(INPUT_POST, 'deposit_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    // Üye no ve TC kontrolü
    $check = $db->query(
        "SELECT COUNT(*) as count FROM members WHERE member_no = ? OR tc_no = ?",
        [$member_no, $tc_no]
    )->fetch();

    if ($check['count'] > 0) {
        setError('Bu üye numarası veya TC kimlik numarası ile kayıtlı bir üye zaten var!');
    } else {
        $tax_paper_file = null;
        $room_registration_file = null;

        // Vergi levhası dosyası yükleme
        if ($tax_paper == 'var' && isset($_FILES['tax_paper_file']) && $_FILES['tax_paper_file']['error'] == 0) {
            $tax_paper_file = uploadFile($_FILES['tax_paper_file'], TAX_PAPERS_PATH);
        }

        // Oda kaydı dosyası yükleme
        if ($room_registration == 'var' && isset($_FILES['room_registration_file']) && $_FILES['room_registration_file']['error'] == 0) {
            $room_registration_file = uploadFile($_FILES['room_registration_file'], ROOM_REGISTRATIONS_PATH);
        }

        $stmt = $db->query(
            "INSERT INTO members (member_no, tc_no, first_name, last_name, address, 
                                tax_paper, tax_paper_file, room_registration, 
                                room_registration_file, deposit_amount) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $member_no, $tc_no, $first_name, $last_name, $address,
                $tax_paper, $tax_paper_file, $room_registration,
                $room_registration_file, $deposit_amount
            ]
        );

        if ($stmt) {
            setSuccess('Üye başarıyla kaydedildi.');
        } else {
            setError('Üye kaydedilirken bir hata oluştu.');
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Yeni Üye Kaydı</h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="memberForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="member_no" class="form-label">Üye No</label>
                    <input type="text" class="form-control" id="member_no" name="member_no" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tc_no" class="form-label">TC Kimlik No</label>
                    <input type="text" class="form-control" id="tc_no" name="tc_no" 
                           pattern="[0-9]{11}" maxlength="11" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">Ad</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Soyad</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Adres</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tax_paper" class="form-label">Vergi Levhası</label>
                    <select class="form-control" id="tax_paper" name="tax_paper" required>
                        <option value="">Seçiniz</option>
                        <option value="var">Var</option>
                        <option value="yok">Yok</option>
                    </select>
                    <div id="tax_paper_file_div" style="display: none;" class="mt-2">
                        <input type="file" class="form-control" id="tax_paper_file" 
                               name="tax_paper_file" accept=".pdf,image/*">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="room_registration" class="form-label">Oda Kaydı</label>
                    <select class="form-control" id="room_registration" name="room_registration" required>
                        <option value="">Seçiniz</option>
                        <option value="var">Var</option>
                        <option value="yok">Yok</option>
                    </select>
                    <div id="room_registration_file_div" style="display: none;" class="mt-2">
                        <input type="file" class="form-control" id="room_registration_file" 
                               name="room_registration_file" accept=".pdf,image/*">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="deposit_amount" class="form-label">Peşinat Tutarı</label>
                <select class="form-control" id="deposit_amount_select" name="deposit_amount" required>
                    <option value="">Seçiniz</option>
                    <option value="400000">400.000 TL</option>
                    <option value="other">Diğer</option>
                </select>
                <div id="other_amount_div" style="display: none;" class="mt-2">
                    <input type="number" class="form-control" id="other_amount" 
                           name="other_amount" step="0.01" placeholder="Tutar giriniz">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vergi levhası dosya yükleme kontrolü
    document.getElementById('tax_paper').addEventListener('change', function() {
        const fileDiv = document.getElementById('tax_paper_file_div');
        const fileInput = document.getElementById('tax_paper_file');
        
        if (this.value === 'var') {
            fileDiv.style.display = 'block';
            fileInput.required = true;
        } else {
            fileDiv.style.display = 'none';
            fileInput.required = false;
        }
    });

    // Oda kaydı dosya yükleme kontrolü
    document.getElementById('room_registration').addEventListener('change', function() {
        const fileDiv = document.getElementById('room_registration_file_div');
        const fileInput = document.getElementById('room_registration_file');
        
        if (this.value === 'var') {
            fileDiv.style.display = 'block';
            fileInput.required = true;
        } else {
            fileDiv.style.display = 'none';
            fileInput.required = false;
        }
    });

    // Peşinat tutarı kontrolü
    document.getElementById('deposit_amount_select').addEventListener('change', function() {
        const otherDiv = document.getElementById('other_amount_div');
        const otherInput = document.getElementById('other_amount');
        
        if (this.value === 'other') {
            otherDiv.style.display = 'block';
            otherInput.required = true;
            this.name = ''; // Select name'ini kaldır
            otherInput.name = 'deposit_amount'; // Input'a name ver
        } else {
            otherDiv.style.display = 'none';
            otherInput.required = false;
            this.name = 'deposit_amount'; // Select name'ini geri ver
            otherInput.name = ''; // Input name'ini kaldır
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 