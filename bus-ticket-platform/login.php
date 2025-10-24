<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Lütfen tüm alanları doldurun.';
    } elseif (!validateEmail($email)) {
        $error = 'Geçerli bir e-posta adresi girin.';
    } else {
        try {
            $db = new Database();
            $user = $db->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                // Session hijacking riskini azaltmak için session ID’yi yenile
                session_regenerate_id(true);

                // Oturum verisini güvenli biçimde kaydet
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'company_id' => $user['company_id'] ?? null
                ];

                // Giriş başarılı — role göre yönlendirme
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } elseif ($user['role'] === 'company_admin') {
                    redirect('company/manage_routes.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'E-posta veya şifre hatalı.';
            }
        } catch (Exception $e) {
            error_log("Login hatası: " . $e->getMessage());
            $error = 'Sistemde bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Otobüs Bileti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="m-0">Giriş Yap</h3>
                    </div>
                    <div class="card-body">

                        <?php if ($error): ?>
                            <?php showAlert($error, 'danger'); ?>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <?php showAlert($success, 'success'); ?>
                        <?php endif; ?>

                        <form method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Giriş Yap</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
                            <p><a href="index.php">Ana Sayfaya Dön</a></p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
