<?php
ob_start();
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$db = new Database();

if (!isset($_GET['id'])) {
    die("<div class='alert alert-danger text-center mt-5'>Geçersiz sefer isteği.</div>");
}

$tripId = sanitizeInput($_GET['id']);
$trip = $db->getTripById($tripId);
if (!$trip) {
    die("<div class='alert alert-danger text-center mt-5'>Sefer bulunamadı.</div>");
}

$user = $db->getUserById($_SESSION['user']['id']);
$userBalance = isset($user['balance']) ? $user['balance'] : 0;

$bookedSeats = $db->getBookedSeats($tripId);
$availableSeats = generateSeatMap($trip['capacity'], $bookedSeats);

$msg = "";
$couponMessage = "";
$discountPercent = 0;
$discountedPrice = $trip['price'];

// 🎟️ Kupon kodu işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = strtoupper(trim($_POST['coupon_code']));
    if ($coupon_code) {
        $stmt = $db->getPdo()->prepare("
            SELECT * FROM coupons 
            WHERE code = ? AND usage_limit > 0 
              AND expire_date >= DATE('now')
            LIMIT 1
        ");
        $stmt->execute([$coupon_code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($coupon) {
            $discountPercent = (int)$coupon['discount'];
            $discountedPrice = $trip['price'] - ($trip['price'] * $discountPercent / 100);
            $_SESSION['coupon'] = [
                'code' => $coupon_code,
                'discount' => $discountPercent,
                'id' => $coupon['id']
            ];
            $couponMessage = "<div class='alert alert-success text-center'>✅ Kupon başarıyla uygulandı! %" . $discountPercent . " indirim kazandınız.</div>";
        } else {
            unset($_SESSION['coupon']);
            $couponMessage = "<div class='alert alert-danger text-center'>❌ Geçersiz veya süresi dolmuş kupon.</div>";
        }
    }
}

// 🎫 Bilet satın alma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seat_number']) && !isset($_POST['apply_coupon'])) {
    $seat = intval($_POST['seat_number']);
    $uid = $_SESSION['user']['id'];
    $price = $trip['price'];

    if (isset($_SESSION['coupon'])) {
        $discountPercent = $_SESSION['coupon']['discount'];
        $price -= ($price * $discountPercent / 100);
    }

    if ($userBalance < $price) {
        $msg = "<div class='alert alert-danger text-center'>❌ Bakiyeniz yetersiz. Lütfen bakiyenizi artırın.</div>";
    } elseif (!$availableSeats[$seat]) {
        $msg = "<div class='alert alert-warning text-center'>⚠️ Bu koltuk zaten dolu, lütfen başka koltuk seçin.</div>";
    } else {
        try {
            $ticketId = $db->createTicket($tripId, $uid, $price, $seat);
            $_SESSION['user']['balance'] = max(0, $userBalance - $price);

            if (isset($_SESSION['coupon'])) {
                $coupon_id = $_SESSION['coupon']['id'];
                $db->getPdo()->prepare("UPDATE coupons SET usage_limit = usage_limit - 1 WHERE id = ?")->execute([$coupon_id]);
                unset($_SESSION['coupon']);
            }

            header("Location: buy_ticket.php?success=1&ticket_id={$ticketId}&id={$tripId}");
            exit;
        } catch (Exception $e) {
            $msg = "<div class='alert alert-danger text-center'>Hata: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Satın Al</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:#f8f9fa; }
        .seat { width:42px; height:42px; margin:6px; border-radius:8px;
                display:inline-flex; align-items:center; justify-content:center;
                cursor:pointer; font-weight:600; transition:0.2s; }
        .available { background:#28a745; color:#fff; }
        .booked { background:#6c757d; color:#fff; cursor:not-allowed; }
        .selected { background:#ffc107; color:#000; }
        .fade-in { animation: fadeIn 0.6s ease-in-out; }
        @keyframes fadeIn { from {opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5 fade-in">
    <h2 class="text-center mb-4">🎫 Bilet Satın Al</h2>

    <?php if (isset($_GET['success']) && isset($_GET['ticket_id'])): ?>
        <div class="alert alert-success text-center fade-in shadow">
            <h4>✅ Bilet başarıyla satın alındı!</h4>
            <p class="mt-2">Sefer: <b><?= htmlspecialchars($trip['departure_city']); ?> → <?= htmlspecialchars($trip['destination_city']); ?></b></p>
            <div class="mt-3 d-flex justify-content-center gap-2">
                <a href="ticket_pdf.php?id=<?= htmlspecialchars($_GET['ticket_id']); ?>" class="btn btn-success">📄 PDF indir</a>
                <a href="my_tickets.php" class="btn btn-secondary">🎟️ Biletlerimi Gör</a>
                <a href="../index.php" class="btn btn-primary">🏠 Ana Sayfaya Dön</a>
            </div>
        </div>
    <?php else: ?>
        <?= $msg; ?>
        <?= $couponMessage; ?>

        <div class="card shadow p-4">
            <div class="card-header bg-primary text-white fw-bold">
                <?= htmlspecialchars($trip['company_name']); ?> — <?= htmlspecialchars($trip['departure_city']); ?> → <?= htmlspecialchars($trip['destination_city']); ?>
            </div>
            <div class="card-body">
                <p><strong>Kalkış:</strong> <?= formatDateTime($trip['departure_time']); ?></p>
                <p><strong>Varış:</strong> <?= formatDateTime($trip['arrival_time']); ?></p>
                <p><strong>Fiyat:</strong> <?= formatPrice($trip['price']); ?></p>
                <p><strong>Bakiyeniz:</strong> <?= formatPrice($userBalance); ?></p>

                <form method="POST" class="row g-2 mb-4">
                    <div class="col-md-8">
                        <input type="text" name="coupon_code" class="form-control" placeholder="Kupon Kodu (ör. WELCOME50)">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="apply_coupon" class="btn btn-success w-100">Kupon Uygula</button>
                    </div>
                </form>

                <h5 class="mb-3 text-center">🪑 Koltuk Seçimi</h5>
                <div class="mb-3 text-center">
                    <?php foreach ($availableSeats as $num => $isFree): ?>
                        <div class="seat <?= $isFree ? 'available':'booked'; ?>" data-seat="<?= $num; ?>"><?= $num; ?></div>
                    <?php endforeach; ?>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label for="seat_number" class="form-label">Seçilen Koltuk</label>
                        <input type="number" id="seat_number" name="seat_number" class="form-control text-center" readonly required>
                    </div>
                    <button type="submit" class="btn btn-lg btn-success w-100">🛒 Bileti Satın Al</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const seats = document.querySelectorAll('.seat.available');
    const seatInput = document.getElementById('seat_number');

    seats.forEach(seat => {
        seat.addEventListener('click', e => {
            e.preventDefault();
            seats.forEach(s => s.classList.remove('selected'));
            seat.classList.add('selected');
            seatInput.value = seat.dataset.seat;
        });
    });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>
