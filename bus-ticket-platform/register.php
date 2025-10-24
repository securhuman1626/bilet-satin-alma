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
    $fullName = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Tüm alanları doldurun.';
    } elseif (!validateEmail($email)) {
        $error = 'Geçerli bir e-posta adresi girin.';
    } elseif (!validatePassword($password)) {
        $error = 'Şifre en az 6 karakter olmalıdır.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Şifreler eşleşmiyor.';
    } else {
        $db = new Database();
        
        // E-posta kontrolü
        if ($db->getUserByEmail($email)) {
            $error = 'Bu e-posta adresi zaten kullanılıyor.';
        } else {
            if ($db->createUser($fullName, $email, $password)) {
                $success = 'Kayıt başarılı! Giriş yapabilirsiniz.';
            } else {
                $error = 'Kayıt sırasında bir hata oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Otobüs Bileti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Kayıt Ol</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <?php showAlert($error, 'danger'); ?>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <?php showAlert($success, 'success'); ?>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Zaten hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
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
