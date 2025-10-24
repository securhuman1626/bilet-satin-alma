<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kullanıcı girişi kontrolü
if (!isLoggedIn() || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// Geçersiz ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: my_tickets.php");
    exit;
}

$db = new Database();
$ticket_id = sanitizeInput($_GET['id']);
$pdo = $db->getPdo();

// 🎟️ Bilet detaylarını al
$stmt = $pdo->prepare("
    SELECT t.*, tr.departure_time, tr.destination_city, tr.departure_city, bc.name AS company_name
    FROM tickets t
    JOIN trips tr ON t.trip_id = tr.id
    JOIN bus_companies bc ON tr.company_id = bc.id
    WHERE t.id = ?
");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

// Bilet kontrolü
if (!$ticket || $ticket['user_id'] != $_SESSION['user']['id']) {
    $msg = '<div class="alert alert-danger text-center mt-5">❌ Böyle bir bilet bulunamadı.</div>';
} 
else {
    // 🔒 1 saatten az kaldıysa iptal etme
    $departureTime = strtotime($ticket['departure_time']);
    $currentTime = time();

    if ($departureTime - $currentTime < 3600) {
        $msg = '<div class="alert alert-warning text-center mt-5">⚠️ Sefer saatine 1 saat kala iptal yapılamaz.</div>';
    } 
    // ✅ İptal işlemi
    else {
        $db->cancelTicket($ticket_id);
        $msg = '
        <div class="alert alert-success text-center mt-5 fade-in">
            ✅ Bilet başarıyla iptal edildi ve bakiyeniz iade edildi.<br>
            <strong>' . htmlspecialchars($ticket['company_name']) . '</strong> — 
            ' . htmlspecialchars($ticket['departure_city']) . ' → ' . htmlspecialchars($ticket['destination_city']) . '
        </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Bilet İptali</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.fade-in { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px);} to {opacity: 1; transform: translateY(0);} }
.card { max-width: 600px; margin: 50px auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <h3 class="text-center text-primary mb-4">🎫 Bilet İptali</h3>
        <?= $msg; ?>

        <div class="text-center mt-4">
            <a href="my_tickets.php" class="btn btn-secondary me-2">🎟️ Biletlerime Dön</a>
            <a href="../index.php" class="btn btn-primary">🏠 Ana Sayfa</a>
        </div>
    </div>
</div>

</body>
</html>
