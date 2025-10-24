<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Sadece firma yöneticisi erişebilir
requireRole('company_admin');

$db = new Database();

// Firma ID'sini al
$company_id = $_SESSION['user']['company_id'] ?? null;
if (!$company_id) {
    die("Firma bilgisi bulunamadı.");
}

// === İSTATİSTİKLER ===
try {
    // Toplam sefer sayısı
    $stmt = $db->getPdo()->prepare("SELECT COUNT(*) FROM trips WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $totalTrips = $stmt->fetchColumn();

    // Toplam koltuk kapasitesi
    $stmt = $db->getPdo()->prepare("SELECT SUM(capacity) FROM trips WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $totalSeats = $stmt->fetchColumn() ?? 0;

    // Toplam kupon sayısı
    $stmt = $db->getPdo()->prepare("SELECT COUNT(*) FROM coupons WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $totalCoupons = $stmt->fetchColumn();

    // Tahmini gelir (basit hesap)
    $stmt = $db->getPdo()->prepare("SELECT SUM(price * (50 - capacity)) FROM trips WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $totalSales = $stmt->fetchColumn() ?? 0;
} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">🏢 Firma Paneli</h2>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h5 class="text-muted">Toplam Sefer</h5>
                <h3><?php echo $totalTrips; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h5 class="text-muted">Toplam Koltuk</h5>
                <h3><?php echo $totalSeats; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h5 class="text-muted">Toplam Kupon</h5>
                <h3><?php echo $totalCoupons; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h5 class="text-muted">Tahmini Gelir</h5>
                <h3><?php echo number_format($totalSales, 0, ',', '.'); ?> ₺</h3>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4>🚍 Sefer Yönetimi</h4>
        <a href="manage_routes.php" class="btn btn-primary mt-2">Seferleri Görüntüle</a>
    </div>

    <div class="mt-4">
        <h4>💸 Kupon Yönetimi</h4>
        <a href="manage_coupons.php" class="btn btn-secondary mt-2">Kuponları Görüntüle</a>
    </div>
</div>

</body>
</html>
