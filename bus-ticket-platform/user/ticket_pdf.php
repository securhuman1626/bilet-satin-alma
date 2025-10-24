<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();

if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger text-center mt-5">Geçersiz bilet isteği.</div>');
}

$ticket_id = sanitizeInput($_GET['id']);
$ticket = $db->getTicketById($ticket_id);

if (!$ticket) {
    die('<div class="alert alert-danger text-center mt-5">Bilet bulunamadı.</div>');
}

// Yetki kontrolü
if ($ticket['user_id'] !== $_SESSION['user']['id']) {
    die('<div class="alert alert-danger text-center mt-5">Bu bilete erişim izniniz yok.</div>');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Bilgisi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .ticket-card {
            max-width: 600px; margin: 50px auto; 
            background: #fff; border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header { background-color: #007bff; color: white; padding: 15px; border-radius: 10px 10px 0 0; }
        .btn-print { background-color: #28a745; color: white; }
        .btn-print:hover { background-color: #218838; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .ticket-card { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>
<div class="ticket-card">
    <div class="header text-center">
        <h4>🎫 Otobüs Bileti</h4>
    </div>
    <div class="p-4">
        <p><strong>Firma:</strong> <?= htmlspecialchars($ticket['company_name']); ?></p>
        <p><strong>Kalkış:</strong> <?= htmlspecialchars($ticket['departure_city']); ?></p>
        <p><strong>Varış:</strong> <?= htmlspecialchars($ticket['destination_city']); ?></p>
        <p><strong>Koltuk No:</strong> <?= htmlspecialchars($ticket['seat_number']); ?></p>
        <p><strong>Fiyat:</strong> <?= number_format($ticket['total_price'], 0, ',', '.'); ?> ₺</p>
        <p><strong>Kalkış Saati:</strong> <?= date('d.m.Y H:i', strtotime($ticket['departure_time'])); ?></p>
        <p><strong>Durum:</strong> 
            <?php
                $statusText = strtoupper($ticket['status']);
                $color = $statusText === 'ACTIVE' ? 'success' : ($statusText === 'CANCELLED' ? 'danger' : 'secondary');
                echo "<span class='badge bg-$color'>$statusText</span>";
            ?>
        </p>
        <hr>
        <p class="text-center">İyi yolculuklar dileriz 🚍</p>
        <div class="text-center no-print mt-3">
            <button class="btn btn-print me-2" onclick="window.print()">📄 PDF Olarak İndir</button>
            <a href="my_tickets.php" class="btn btn-secondary">🎟️ Biletlerime Dön</a>
        </div>
    </div>
</div>
</body>
</html>
