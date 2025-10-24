<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company_admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$company_id = $_SESSION['user']['company_id'];
$message = "";

// 🔹 Kupon ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(sanitizeInput($_POST['code']));
    $discount = intval($_POST['discount']);
    $valid_until = $_POST['valid_until'];

    if (!empty($code) && $discount > 0 && !empty($valid_until)) {
        try {
            $db->addCompanyCoupon($company_id, $code, $discount, $valid_until);
            $message = "<div class='alert alert-success mt-3'>✅ Kupon başarıyla eklendi.</div>";
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger mt-3'>❌ Hata: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning mt-3'>⚠️ Lütfen tüm alanları doğru doldurun.</div>";
    }
}

// 🔹 Kupon silme işlemi
if (isset($_GET['delete'])) {
    $coupon_id = sanitizeInput($_GET['delete']); // 🔥 intval kaldırıldı!
    try {
        if ($db->deleteCompanyCoupon($coupon_id, $company_id)) {
            $message = "<div class='alert alert-success mt-3'>🗑️ Kupon başarıyla silindi.</div>";
        } else {
            $message = "<div class='alert alert-danger mt-3'>⚠️ Kupon silinemedi veya bulunamadı.</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger mt-3'>❌ Hata: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 🔹 Kuponları getir
$coupons = $db->getCompanyCoupons($company_id);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Kupon Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Kupon Yönetimi</h2>

    <?= $message; ?>

    <!-- Yeni Kupon Ekle -->
    <div class="card p-4 mb-4 shadow-sm">
        <h5>Yeni Kupon Ekle</h5>
        <form method="POST">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="code" class="form-control" placeholder="Kupon Kodu" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="discount" class="form-control" placeholder="İndirim (%)" required>
                </div>
                <div class="col-md-3">
                    <input type="date" name="valid_until" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="add_coupon" class="btn btn-success w-100">Ekle</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Kupon Listesi -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Kayıtlı Kuponlar</div>
        <div class="card-body">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kod</th>
                        <th>İndirim</th>
                        <th>Geçerlilik</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                        <tr><td colspan="4" class="text-muted">Henüz kayıtlı kupon bulunmamaktadır.</td></tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['code']); ?></td>
                                <td>%<?= htmlspecialchars($c['discount'] ?? '0'); ?></td>
                                <td><?= isset($c['expire_date']) && !empty($c['expire_date'])
                                        ? date('d.m.Y', strtotime($c['expire_date']))
                                        : '-'; ?></td>
                                <td>
                                    <a href="manage_coupons.php?delete=<?= urlencode($c['id']); ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu kuponu silmek istediğinize emin misiniz?');">
                                       Sil
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
