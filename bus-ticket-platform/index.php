<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// --------------------------------------------------
// 🧩 GEÇİCİ TEST VERİLERİ (bir kez çalışır)
// --------------------------------------------------
$db = new Database();

try {
    // Firmalar
    $db->getPdo()->exec("
        INSERT OR IGNORE INTO bus_companies (id, name)
        VALUES 
        ('C1', 'Kamil Koç'),
        ('C2', 'Metro Turizm'),
        ('C3', 'Pamukkale'),
        ('C4', 'Varan Turizm');
    ");

    // Seferler
    $db->getPdo()->exec("
        INSERT OR IGNORE INTO trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity)
        VALUES 
        ('T1', 'C1', 'Bursa', 'Eskişehir', '2025-10-20 09:00:00', '2025-10-20 11:30:00', 180, 40),
        ('T2', 'C2', 'Eskişehir', 'Ankara', '2025-10-20 13:00:00', '2025-10-20 15:30:00', 200, 45),
        ('T3', 'C3', 'İstanbul', 'Ankara', '2025-10-21 08:30:00', '2025-10-21 13:00:00', 350, 50),
        ('T4', 'C4', 'Ankara', 'İstanbul', '2025-10-22 10:00:00', '2025-10-22 15:30:00', 370, 50);
    ");
} catch (Exception $e) {
    // Hata bastırma sadece loglama
    error_log("Test seferi eklenemedi: " . $e->getMessage());
}
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otobüs Bileti Satış Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Otobüs Bileti</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Ana Sayfa</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['user']['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($_SESSION['user']['role'] === 'user'): ?>
    <li><a class="dropdown-item" href="tickets.php">Biletlerim</a></li>
<?php endif; ?>

                            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/dashboard.php">Admin Paneli</a></li>
                            <?php elseif ($_SESSION['user']['role'] === 'company_admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="company/dashboard.php">Firma Paneli</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Ana İçerik -->
<div class="container mt-4">
    <h1 class="text-center mb-4">Otobüs Bileti Satış Platformu</h1>

    <!-- Sefer Arama -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Sefer Ara</h5>
            <form method="GET" action="index.php">
                <div class="row">
                    <div class="col-md-3">
                        <label for="departure_city" class="form-label">Kalkış Şehri</label>
                        <input type="text" class="form-control" id="departure_city" name="departure_city"
                               value="<?php echo isset($_GET['departure_city']) ? htmlspecialchars($_GET['departure_city']) : ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="destination_city" class="form-label">Varış Şehri</label>
                        <input type="text" class="form-control" id="destination_city" name="destination_city"
                               value="<?php echo isset($_GET['destination_city']) ? htmlspecialchars($_GET['destination_city']) : ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="departure_date" class="form-label">Tarih</label>
                        <input type="date" class="form-control" id="departure_date" name="departure_date"
                               value="<?php echo isset($_GET['departure_date']) ? htmlspecialchars($_GET['departure_date']) : date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Sefer Ara</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sefer Sonuçları -->
    <?php
    if (isset($_GET['departure_city'], $_GET['destination_city'], $_GET['departure_date'])) {
        $departure_city = $_GET['departure_city'];
        $destination_city = $_GET['destination_city'];
        $departure_date = $_GET['departure_date'];

        $trips = $db->searchTrips($departure_city, $destination_city, $departure_date);

        if (empty($trips)) {
            echo '<div class="alert alert-info">Bu kriterlere uygun sefer bulunamadı.</div>';
        } else {
            echo '<div class="card"><div class="card-body">';
            echo '<h5 class="card-title">Bulunan Seferler</h5>';
            echo '<div class="table-responsive"><table class="table table-striped">';
            echo '<thead><tr>
                    <th>Firma</th>
                    <th>Kalkış</th>
                    <th>Varış</th>
                    <th>Kalkış Saati</th>
                    <th>Varış Saati</th>
                    <th>Fiyat</th>
                    <th>Boş Koltuk</th>
                    <th>İşlemler</th>
                  </tr></thead><tbody>';

            foreach ($trips as $trip) {
                $bookedSeats = $db->getBookedSeats($trip['id']);
                $available = $trip['capacity'] - count($bookedSeats);

                echo '<tr>';
                echo '<td>' . htmlspecialchars($trip['company_name']) . '</td>';
                echo '<td>' . htmlspecialchars($trip['departure_city']) . '</td>';
                echo '<td>' . htmlspecialchars($trip['destination_city']) . '</td>';
                echo '<td>' . date('H:i', strtotime($trip['departure_time'])) . '</td>';
                echo '<td>' . date('H:i', strtotime($trip['arrival_time'])) . '</td>';
                echo '<td>' . number_format($trip['price'], 0, ',', '.') . ' ₺</td>';
                echo '<td>' . $available . '</td>';
                echo '<td>';
                echo '<a href="route_details.php?id=' . urlencode($trip['id']) . '" class="btn btn-sm btn-info">Detaylar</a> ';
                if (isLoggedIn() && $_SESSION['user']['role'] === 'user') {
                    if ($available > 0) {
                        echo '<a href="user/buy_ticket.php?id=' . urlencode($trip['id']) . '" class="btn btn-sm btn-success">Bilet Al</a>';
                    } else {
                        echo '<button class="btn btn-sm btn-secondary" disabled>Dolu</button>';
                    }
                } elseif (!isLoggedIn()) {
                    echo '<a href="login.php" class="btn btn-sm btn-warning">Giriş Yap</a>';
                }
                echo '</td></tr>';
            }

            echo '</tbody></table></div></div></div>';
        }
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
