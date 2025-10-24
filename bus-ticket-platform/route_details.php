<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$db = new Database();
$trip_id = sanitizeInput($_GET['id']);
$trip = $db->getTripById($trip_id);

if (!$trip) {
    die('<div class="alert alert-danger text-center mt-5">Sefer bulunamadı!</div>');
}

$bookedSeats = $db->getBookedSeats($trip_id);
$availableSeats = $trip['capacity'] - count($bookedSeats);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Detayları - <?php echo htmlspecialchars($trip['company_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5 fade-in">
        <div class="card trip-card">
            <div class="trip-header">
                <div class="trip-company">
                    <?php echo htmlspecialchars($trip['company_name']); ?>
                </div>
                <div class="trip-price">
                    <?php echo number_format($trip['price'], 0, ',', '.'); ?> ₺
                </div>
            </div>

            <div class="trip-details">
                <div class="trip-detail">
                    <div class="trip-detail-label">Kalkış Şehri</div>
                    <div class="trip-detail-value"><?php echo htmlspecialchars($trip['departure_city']); ?></div>
                </div>
                <div class="trip-detail">
                    <div class="trip-detail-label">Varış Şehri</div>
                    <div class="trip-detail-value"><?php echo htmlspecialchars($trip['destination_city']); ?></div>
                </div>
                <div class="trip-detail">
                    <div class="trip-detail-label">Kalkış Saati</div>
                    <div class="trip-detail-value"><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></div>
                </div>
                <div class="trip-detail">
                    <div class="trip-detail-label">Varış Saati</div>
                    <div class="trip-detail-value"><?php echo date('d.m.Y H:i', strtotime($trip['arrival_time'])); ?></div>
                </div>
                <div class="trip-detail">
                    <div class="trip-detail-label">Toplam Koltuk</div>
                    <div class="trip-detail-value"><?php echo htmlspecialchars($trip['capacity']); ?></div>
                </div>
                <div class="trip-detail">
                    <div class="trip-detail-label">Boş Koltuk</div>
                    <div class="trip-detail-value"><?php echo $availableSeats; ?></div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <?php if (isLoggedIn() && $_SESSION['user']['role'] === 'user'): ?>
                <?php if ($availableSeats > 0): ?>
                    <a href="user/buy_ticket.php?id=<?php echo $trip_id; ?>" class="btn btn-success btn-lg">
                        Bilet Satın Al
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>Dolu</button>
                <?php endif; ?>
            <?php elseif (!isLoggedIn()): ?>
                <div class="alert alert-warning mt-3">
                    Bilet satın almak için <a href="login.php" class="alert-link">giriş yapın</a> veya 
                    <a href="register.php" class="alert-link">kayıt olun</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
