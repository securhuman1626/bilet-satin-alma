<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('company_admin');
$db = new Database();
$company_id = currentCompanyId();

if (!isset($_GET['id'])) {
    header("Location: manage_routes.php");
    exit;
}

$trip_id = sanitizeInput($_GET['id']);
$message = "";

// Sefer verisini getir
$stmt = $db->getPdo()->prepare("SELECT * FROM trips WHERE id = ? AND company_id = ?");
$stmt->execute([$trip_id, $company_id]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trip) {
    die("<div class='alert alert-danger text-center mt-5'>❌ Bu sefer bulunamadı veya yetkiniz yok.</div>");
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_trip'])) {
    $departure_city = sanitizeInput($_POST['departure_city']);
    $destination_city = sanitizeInput($_POST['destination_city']);
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);

    if (empty($departure_city) || empty($destination_city) || $price <= 0 || $capacity <= 0) {
        $message = "<div class='alert alert-warning'>⚠️ Lütfen tüm alanları doğru doldurun.</div>";
    } else {
        $stmt = $db->getPdo()->prepare("
            UPDATE trips 
            SET departure_city = ?, destination_city = ?, departure_time = ?, arrival_time = ?, price = ?, capacity = ? 
            WHERE id = ? AND company_id = ?
        ");
        $stmt->execute([$departure_city, $destination_city, $departure_time, $arrival_time, $price, $capacity, $trip_id, $company_id]);
        $message = "<div class='alert alert-success'>✅ Sefer başarıyla güncellendi.</div>";

        // Güncel veriyi tekrar çekelim
        $stmt = $db->getPdo()->prepare("SELECT * FROM trips WHERE id = ? AND company_id = ?");
        $stmt->execute([$trip_id, $company_id]);
        $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sefer Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">🚌 Sefer Düzenle</h2>
    <?= $message; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kalkış Şehri</label>
                    <input type="text" name="departure_city" class="form-control" value="<?= htmlspecialchars($trip['departure_city']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Varış Şehri</label>
                    <input type="text" name="destination_city" class="form-control" value="<?= htmlspecialchars($trip['destination_city']); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kalkış Saati</label>
                    <input type="datetime-local" name="departure_time" class="form-control" 
                        value="<?= date('Y-m-d\TH:i', strtotime($trip['departure_time'])); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Varış Saati</label>
                    <input type="datetime-local" name="arrival_time" class="form-control" 
                        value="<?= date('Y-m-d\TH:i', strtotime($trip['arrival_time'])); ?>" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Fiyat</label>
                    <input type="number" name="price" class="form-control" value="<?= $trip['price']; ?>" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Koltuk</label>
                    <input type="number" name="capacity" class="form-control" value="<?= $trip['capacity']; ?>" required>
                </div>
                <div class="col-md-12 text-end">
                    <button type="submit" name="update_trip" class="btn btn-success">Kaydet</button>
                    <a href="manage_routes.php" class="btn btn-secondary">Geri Dön</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
