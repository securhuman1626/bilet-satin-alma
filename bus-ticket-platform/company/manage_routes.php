<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('company_admin');
$db = new Database();

$company_id = currentCompanyId();
$user = $_SESSION['user'];
$message = "";

// 🔹 Firma adını dinamik olarak çek
$userCompanyName = '-';
if (!empty($company_id)) {
    $stmt = $db->getPdo()->prepare("SELECT name FROM bus_companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($company) {
        $userCompanyName = $company['name'];
    }
}

// 🔹 Sefer ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_trip'])) {
    $departure = sanitizeInput($_POST['departure_city']);
    $destination = sanitizeInput($_POST['destination_city']);
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = (int)$_POST['price'];
    $capacity = (int)$_POST['capacity'];

    if ($db->addRoute($company_id, $departure, $destination, $departure_time, $arrival_time, $price, $capacity)) {
        $message = "<div class='alert alert-success mt-3'>✅ Sefer başarıyla eklendi.</div>";
    } else {
        $message = "<div class='alert alert-danger mt-3'>❌ Sefer eklenirken hata oluştu.</div>";
    }
}

// 🔹 Sefer silme işlemi
if (isset($_GET['delete'])) {
    $trip_id = sanitizeInput($_GET['delete']);
    try {
        $stmt = $db->getPdo()->prepare("DELETE FROM trips WHERE id = ? AND company_id = ?");
        $stmt->execute([$trip_id, $company_id]);
        header("Location: manage_routes.php");
        exit;
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger mt-3'>❌ Sefer silinemedi: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 🔹 Firma adminin seferleri
$trips = $db->getCompanyRoutes($company_id);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Sefer Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <!-- Firma Adı ve Kullanıcı Bilgisi -->
    <h2 class="text-center mb-4">
        🚌 <?= htmlspecialchars($user['full_name']); ?> -
        Bağlı Olduğu Firma: <strong><?= htmlspecialchars($userCompanyName); ?></strong>
    </h2>

    <?= $message; ?>

    <!-- Yeni Sefer Ekle -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">Yeni Sefer Ekle</div>
        <div class="card-body">
            <form method="POST" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="departure_city" class="form-control" placeholder="Kalkış Şehri" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="destination_city" class="form-control" placeholder="Varış Şehri" required>
                </div>
                <div class="col-md-2">
                    <input type="datetime-local" name="departure_time" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <input type="datetime-local" name="arrival_time" class="form-control" required>
                </div>
                <div class="col-md-1">
                    <input type="number" name="price" class="form-control" placeholder="₺" required>
                </div>
                <div class="col-md-1">
                    <input type="number" name="capacity" class="form-control" placeholder="Koltuk" required>
                </div>
                <div class="col-md-12 text-end mt-3">
                    <button type="submit" name="add_trip" class="btn btn-success">Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mevcut Seferler -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Mevcut Seferler</div>
        <div class="card-body">
            <?php if (empty($trips)): ?>
                <div class="alert alert-info">Henüz kayıtlı sefer bulunmamaktadır.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kalkış</th>
                                <th>Varış</th>
                                <th>Kalkış Saati</th>
                                <th>Varış Saati</th>
                                <th>Fiyat</th>
                                <th>Koltuk</th>
                                <th>Sil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trips as $trip): ?>
                                <tr>
                                    <td><?= htmlspecialchars($trip['departure_city']); ?></td>
                                    <td><?= htmlspecialchars($trip['destination_city']); ?></td>
                                    <td><?= formatDateTime($trip['departure_time']); ?></td>
                                    <td><?= formatDateTime($trip['arrival_time']); ?></td>
                                    <td><?= formatPrice($trip['price']); ?></td>
                                    <td><?= $trip['capacity']; ?></td>
                                    <td>
                                        <a href="manage_routes.php?delete=<?= $trip['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Bu seferi silmek istiyor musunuz?');">
                                           Sil
                                        </a>
                                    </td>
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
