<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

if (!isset($_GET['id'])) {
    header("Location: manage_companies.php");
    exit;
}

$db = new Database();
$pdo = $db->getPdo();

$companyId = $_GET['id'];
$message = "";

// Firma bilgisi getir
$stmt = $pdo->prepare("SELECT * FROM bus_companies WHERE id = ?");
$stmt->execute([$companyId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    die("<div class='alert alert-danger text-center mt-5'>Firma bulunamadı.</div>");
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name']);

    if (empty($newName)) {
        $message = "<div class='alert alert-warning'>Firma adı boş olamaz.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE bus_companies SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $companyId]);
            $message = "<div class='alert alert-success'>Firma başarıyla güncellendi!</div>";
            // Güncel veriyi tekrar çekelim
            $stmt = $pdo->prepare("SELECT * FROM bus_companies WHERE id = ?");
            $stmt->execute([$companyId]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Veritabanı hatası: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Firma Düzenle</h2>
    <?php echo $message; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Firma Adı</label>
            <input type="text" id="name" name="name" class="form-control" 
                   value="<?php echo htmlspecialchars($company['name']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="manage_companies.php" class="btn btn-secondary">Geri Dön</a>
    </form>
</div>
</body>
</html>
