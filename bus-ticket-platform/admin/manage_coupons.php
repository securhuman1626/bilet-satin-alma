<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Sadece admin erişebilir
requireRole('admin');

$db = new Database();
$message = "";

// === Kupon Silme ===
if (isset($_GET['delete'])) {
    $coupon_id = sanitizeInput($_GET['delete']);
    try {
        $db->deleteGlobalCoupon($coupon_id);
        $message = "<div class='alert alert-success'>🗑️ Kupon başarıyla silindi.</div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>❌ Silme hatası: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// === Kupon Ekleme ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(sanitizeInput($_POST['code']));
    $discount = intval($_POST['discount']);
    $usage_limit = intval($_POST['usage_limit']);
    $expire_date = $_POST['expire_date'];

    if (empty($code) || $discount <= 0 || $usage_limit <= 0 || empty($expire_date)) {
        $message = "<div class='alert alert-warning'>⚠️ Lütfen tüm alanları doğru şekilde doldurun.</div>";
    } else {
        try {
            $db->addGlobalCoupon($code, $discount, $expire_date, $usage_limit);
            $message = "<div class='alert alert-success'>✅ Kupon başarıyla oluşturuldu.</div>";
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>❌ Kupon eklenirken hata oluştu: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// === Kupon Güncelleme ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_coupon'])) {
    $coupon_id = sanitizeInput($_POST['coupon_id']);
    $code = strtoupper(sanitizeInput($_POST['code']));
    $discount = intval($_POST['discount']);
    $usage_limit = intval($_POST['usage_limit']);
    $expire_date = $_POST['expire_date'];

    if (empty($coupon_id) || empty($code) || $discount <= 0 || $usage_limit <= 0 || empty($expire_date)) {
        $message = "<div class='alert alert-warning'>⚠️ Lütfen tüm alanları doldurun.</div>";
    } else {
        try {
            $stmt = $db->getPdo()->prepare("
                UPDATE coupons 
                SET code = ?, discount = ?, usage_limit = ?, expire_date = ? 
                WHERE id = ?
            ");
            $stmt->execute([$code, $discount, $usage_limit, $expire_date, $coupon_id]);
            $message = "<div class='alert alert-success'>✅ Kupon başarıyla güncellendi.</div>";
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>❌ Güncelleme hatası: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// === Kupon Listesi ===
$coupons = $db->getAllCoupons();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Kupon Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">🎟️ Global Kupon Yönetimi</h2>

    <?php echo $message; ?>

    <!-- Yeni Kupon Ekle -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">Yeni Kupon Ekle</div>
        <div class="card-body">
            <form method="POST" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="code" class="form-control" placeholder="Kupon Kodu (ör. WELCOME50)" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="discount" class="form-control" placeholder="İndirim (%)" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="usage_limit" class="form-control" placeholder="Kullanım Limiti" required>
                </div>
                <div class="col-md-3">
                    <input type="date" name="expire_date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_coupon" class="btn btn-success w-100">Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kupon Tablosu -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Mevcut Kuponlar</div>
        <div class="card-body">
            <?php if (empty($coupons)): ?>
                <div class="alert alert-info">Henüz sistemde kupon bulunmamaktadır.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kod</th>
                                <th>İndirim</th>
                                <th>Kullanım Limiti</th>
                                <th>Son Tarih</th>
                                <th>Oluşturulma</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $c): ?>
                                <tr>
                                    <form method="POST">
                                        <td><input type="text" name="code" value="<?php echo htmlspecialchars($c['code']); ?>" class="form-control"></td>
                                        <td><input type="number" name="discount" value="<?php echo htmlspecialchars($c['discount']); ?>" class="form-control"></td>
                                        <td><input type="number" name="usage_limit" value="<?php echo htmlspecialchars($c['usage_limit']); ?>" class="form-control"></td>
                                        <td><input type="date" name="expire_date" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($c['expire_date']))); ?>" class="form-control"></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($c['created_at'])); ?></td>
                                        <td class="text-center">
                                            <input type="hidden" name="coupon_id" value="<?php echo htmlspecialchars($c['id']); ?>">
                                            <button type="submit" name="update_coupon" class="btn btn-sm btn-warning text-dark">Güncelle</button>
                                            <a href="manage_coupons.php?delete=<?php echo $c['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bu kuponu silmek istediğinize emin misiniz?');">
                                               Sil
                                            </a>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
