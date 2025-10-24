<?php
// HATA AYIKLAMA (Sorun varsa göstersin)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../config/database.php';
require_once '../includes/functions.php';

// Sadece Admin erişebilir
requireRole('admin');

$db = new Database();
$error = '';
$success = '';

// Form Gönderimi Kontrolü (Yeni Firma Ekleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $companyName = sanitizeInput($_POST['company_name']);
    
    if (empty($companyName)) {
        $error = 'Firma adı boş bırakılamaz.';
    } else {
        try {
            if ($db->createCompany($companyName)) { // Database sınıfındaki metodu kullanıyoruz
                $success = 'Yeni firma başarıyla eklendi.';
            } else {
                $error = 'Firma eklenirken bir hata oluştu veya bu isimde bir firma zaten var.';
            }
        } catch (PDOException $e) {
            // UNIQUE kısıtlaması ihlali (aynı isimde firma)
            if ($e->getCode() == 23000) { 
                $error = 'Bu isimde bir firma zaten mevcut.';
            } else {
                $error = 'Veritabanı hatası: ' . $e->getMessage();
            }
        } catch (Exception $e) {
            $error = 'Beklenmedik bir hata oluştu: ' . $e->getMessage();
        }
    }
}

// Firma silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $companyId = sanitizeInput($_POST['company_id']);
    try {
        if ($db->deleteCompany($companyId)) {
            $success = 'Firma başarıyla silindi.';
        } else {
            $error = 'Firma silinemedi. Firmaya ait seferler veya kullanıcılar olabilir.';
        }
    } catch (Exception $e) {
        $error = 'Veritabanı hatası: ' . $e->getMessage();
    }
}

// Firma listesi
$companies = [];
try {
    $companies = $db->getAllCompanies();
} catch (Exception $e) {
    $error = 'Firmalar yüklenirken bir hata oluştu: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Firma Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Firma Yönetimi</h1>
        </div>
        
        <?php if ($error): showAlert($error, 'danger'); endif; ?>
        <?php if ($success): showAlert($success, 'success'); endif; ?>

        <!-- Firma Ekleme -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">Yeni Otobüs Firması Ekle</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="company_name" class="form-label">Firma Adı</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Ekle</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Firma Listesi -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-secondary text-white">Kayıtlı Otobüs Firmaları</div>
            <div class="card-body">
                <?php if (empty($companies)): ?>
                    <div class="alert alert-info">Sistemde kayıtlı otobüs firması bulunmamaktadır.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Firma Adı</th>
                                    <th>Kayıt Tarihi</th>
                                    <th style="width:150px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($company['name']); ?></td>
                                    <td><?php echo formatDateTime($company['created_at']); ?></td>
                                    <td>
                                        <a href="company_edit.php?id=<?php echo $company['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                        
                                        <form method="POST" style="display:inline-block;" 
                                              onsubmit="return confirm('Bu firmayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                                        </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
