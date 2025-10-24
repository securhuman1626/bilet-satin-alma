<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Sadece admin erişebilir
requireRole('admin');

$db = new Database();
$admins = $db->getCompanyAdmins();
$companies = $db->getAllCompanies();

$message = "";

// Yeni firma admini ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password_plain = $_POST['password'];
    $company_id = intval($_POST['company_id']);

    if (empty($full_name) || empty($email) || empty($password_plain) || empty($company_id)) {
        $message = "<div class='alert alert-warning'>⚠️ Lütfen tüm alanları doldurun.</div>";
    } elseif (!validateEmail($email)) {
        $message = "<div class='alert alert-warning'>⚠️ Geçerli bir e-posta adresi girin.</div>";
    } elseif (!$db->getUserByEmail($email)) {
        // Kullanıcı yoksa yeni oluştur
        $password = password_hash($password_plain, PASSWORD_DEFAULT);
        if ($db->createCompanyAdmin($full_name, $email, $password, $company_id)) {
            $message = "<div class='alert alert-success'>✅ Firma yöneticisi başarıyla oluşturuldu.</div>";
            $admins = $db->getCompanyAdmins(); // Listeyi güncelle
        } else {
            $message = "<div class='alert alert-danger'>❌ Firma yöneticisi eklenirken hata oluştu.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ Bu e-posta adresiyle zaten bir kullanıcı mevcut.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Firma Admin Yönetimi</h2>

    <?php echo $message; ?>

    <div class="card p-4 mb-4 shadow-sm">
        <h5>Yeni Firma Admini Oluştur</h5>
        <form method="POST">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="full_name" class="form-control" placeholder="Ad Soyad" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="E-posta" required>
                </div>
                <div class="col-md-2">
                    <input type="password" name="password" class="form-control" placeholder="Şifre" required>
                </div>
                <div class="col-md-3">
                    <select name="company_id" class="form-select" required>
                        <option value="">Firma Seç</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo $company['id']; ?>">
                                <?php echo htmlspecialchars($company['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" name="add_admin" class="btn btn-success w-100">Ekle</button>
                </div>
            </div>
        </form>
    </div>

    <h5 class="mb-3">Kayıtlı Firma Yöneticileri</h5>
    <?php if (empty($admins)): ?>
        <div class="alert alert-info">Henüz kayıtlı firma yöneticisi bulunmamaktadır.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Bağlı Olduğu Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($a['email']); ?></td>
                            <td><?php echo htmlspecialchars($a['company_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
