<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Sadece admin erişebilir
requireRole('admin');

$db = new Database();
$message = "";

// Firma listesi (dropdown için)
$companies = $db->getAllCompanies();

// ✅ Kullanıcı silme işlemi
if (isset($_GET['delete_user'])) {
    $user_id = sanitizeInput($_GET['delete_user']);
    try {
        $stmt = $db->getPdo()->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "<div class='alert alert-success'>🗑️ Kullanıcı başarıyla silindi.</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            // Foreign key hatası — kullanıcıya ait bilet varsa
            $message = "<div class='alert alert-warning'>⚠️ Bu kullanıcıya ait bilet kayıtları bulunduğu için silinemiyor.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Veritabanı hatası: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>❌ Beklenmedik hata: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// ✅ Yeni Firma Admin ekleme (email benzersizlik kontrolü eklendi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company_admin'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $company_id = $_POST['company_id'];

    if (empty($full_name) || empty($email) || empty($password) || empty($company_id)) {
        $message = "<div class='alert alert-warning'>⚠️ Lütfen tüm alanları doldurun.</div>";
    } elseif ($db->getUserByEmail($email)) {
        $message = "<div class='alert alert-warning'>⚠️ Bu e-posta adresiyle zaten bir kullanıcı kayıtlı. Başka bir adres deneyin.</div>";
    } else {
        try {
            $db->createCompanyAdmin($full_name, $email, $password, $company_id);
            $message = "<div class='alert alert-success'>✅ Firma yöneticisi başarıyla oluşturuldu.</div>";
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>❌ Hata: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Tüm kullanıcıları getir
$users = $db->getAllUsers();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Kullanıcı Yönetimi</h2>

    <?php echo $message; ?>

    <!-- Yeni Firma Admin Ekle -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">Yeni Firma Admin Ekle</div>
        <div class="card-body">
            <form method="POST" class="row g-2">
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
                        <option value="">Firma Seçin</option>
                        <?php foreach ($companies as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['id']); ?>">
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" name="add_company_admin" class="btn btn-success w-100">Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kullanıcı Listesi -->
    <?php if (empty($users)): ?>
        <div class="alert alert-info">Henüz sistemde kayıtlı kullanıcı bulunmuyor.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th>Firma</th>
                        <th>Bakiye</th>
                        <th>Kayıt Tarihi</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo getRoleName($u['role']); ?></td>
                            <td><?php echo htmlspecialchars($u['company_name'] ?? '-'); ?></td>
                            <td><?php echo number_format($u['balance'], 0, ',', '.'); ?> ₺</td>
                            <td><?php echo formatDateTime($u['created_at']); ?></td>
                            <td>
                                <?php if ($u['role'] !== 'admin'): ?>
                                    <a href="manage_users.php?delete_user=<?php echo $u['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">
                                       Sil
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
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
