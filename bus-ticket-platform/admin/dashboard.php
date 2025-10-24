<?php
// HATA AYIKLAMA AÇIK (geliştirme aşamasında)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Sadece admin erişebilir
requireRole('admin');

$db = new Database();

// Veriler
$all_companies = $db->getAllCompanies();
$all_users = $db->getAllUsers();
$all_coupons = $db->getAllCoupons(); // Kupon sayısı da gösterilsin
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Otobüs Bileti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Paneli</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_companies.php">Firma Yönetimi</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Kullanıcı Yönetimi</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_coupons.php">Kupon Yönetimi</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Siteye Dön</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Çıkış Yap (<?php echo htmlspecialchars($_SESSION['user']['full_name']); ?>)</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Admin Paneline Hoş Geldiniz</h1>
        
        <div class="row">
            <!-- Firma Yönetimi -->
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Firma Yönetimi</div>
                    <div class="card-body">
                        <h5 class="card-title">Toplam Firma: <?php echo count($all_companies); ?></h5>
                        <p class="card-text">Yeni otobüs firmaları oluşturun veya mevcutları yönetin.</p>
                        <a href="manage_companies.php" class="btn btn-primary w-100">Firmaları Yönet</a>
                    </div>
                </div>
            </div>

            <!-- Kullanıcı Yönetimi -->
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">Kullanıcı Yönetimi</div>
                    <div class="card-body">
                        <h5 class="card-title">Toplam Kullanıcı: <?php echo count($all_users); ?></h5>
                        <p class="card-text">Yeni "Firma Admin" kullanıcıları oluşturun ve firmalara atayın.</p>
                        <a href="manage_users.php" class="btn btn-success w-100">Kullanıcıları Yönet</a>
                    </div>
                </div>
            </div>

            <!-- Kupon Yönetimi -->
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-warning text-dark">Kupon Yönetimi</div>
                    <div class="card-body">
                        <h5 class="card-title">Toplam Kupon: <?php echo count($all_coupons); ?></h5>
                        <p class="card-text">Tüm firmalarda geçerli indirim kuponlarını oluşturun ve yönetin.</p>
                        <a href="manage_coupons.php" class="btn btn-warning w-100 text-dark fw-bold">Kuponları Yönet</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
