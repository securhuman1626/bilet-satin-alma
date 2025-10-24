<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kullanıcı kontrolü
if (!isLoggedIn() || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$user_id = $_SESSION['user']['id'];
$tickets = $db->getUserTickets($user_id);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from {opacity: 0; transform: translateY(-10px);} to {opacity: 1; transform: translateY(0);} }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5 fade-in">
    <h2 class="mb-4 text-center">🎟️ Biletlerim</h2>

    <?php if (isset($_GET['cancel']) && $_GET['cancel'] === 'success'): ?>
        <div class="alert alert-success text-center">
            ✅ Bilet başarıyla iptal edildi, ücret iade edildi.<br>
            <a href="../index.php" class="btn btn-primary mt-2">🏠 Ana Sayfaya Dön</a>
        </div>
    <?php endif; ?>

    <?php if (empty($tickets)): ?>
        <div class="alert alert-info text-center">
            Henüz alınmış biletiniz bulunmamaktadır.<br>
            <a href="../index.php" class="btn btn-primary mt-2">🏠 Ana Sayfaya Dön</a>
        </div>
    <?php else: ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Firma</th>
                        <th>Kalkış</th>
                        <th>Varış</th>
                        <th>Koltuk(lar)</th>
                        <th>Fiyat</th>
                        <th>Kalkış Zamanı</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <?php
                        // Kalkış zamanı kontrolü (süresi dolmuşsa expired olarak göster)
                        $departure_time = strtotime($t['departure_time']);
                        if ($departure_time < time() && strtolower($t['status']) === 'active') {
                            $t['status'] = 'expired';
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($t['company_name']); ?></td>
                            <td><?= htmlspecialchars($t['departure_city']); ?></td>
                            <td><?= htmlspecialchars($t['destination_city']); ?></td>
                            <td><?= htmlspecialchars($t['seat_numbers']); ?></td>
                            <td><?= number_format($t['total_price'], 0, ',', '.'); ?> ₺</td>
                            <td><?= date('d.m.Y H:i', $departure_time); ?></td>
                            <td>
                                <?php if (strtolower($t['status']) === 'active'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php elseif (strtolower($t['status']) === 'expired'): ?>
                                    <span class="badge bg-secondary">Süresi Doldu</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">İptal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="ticket_pdf.php?id=<?= $t['id']; ?>" class="btn btn-sm btn-primary">PDF</a>

                                <?php if (
                                    strtolower($t['status']) === 'active' &&
                                    ($departure_time - time() > 3600)
                                ): ?>
                                    <a href="cancel_ticket.php?id=<?= $t['id']; ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu bileti iptal etmek istediğinize emin misiniz?');">İptal Et</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>İptal Edilemez</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
