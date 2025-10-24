<?php
// ===================================================
// Yardımcı Fonksiyonlar
// ===================================================

// =========================
// Oturum ve Yetkilendirme
// =========================

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireRole($requiredRole) {
    requireLogin();
    if ($_SESSION['user']['role'] !== $requiredRole) {
        header('Location: index.php');
        exit();
    }
}

function requireAdminOrCompanyAdmin() {
    requireLogin();
    if (!in_array($_SESSION['user']['role'], ['admin', 'company_admin'])) {
        header('Location: index.php');
        exit();
    }
}

// =========================
// Bilet İptali Kuralları
// =========================

function canCancelTicket($departureTime) {
    $departure = new DateTime($departureTime);
    $cancellationDeadline = (clone $departure)->sub(new DateInterval('PT1H'));
    $now = new DateTime();
    return $now < $cancellationDeadline;
}

// =========================
// Güvenlik Fonksiyonları
// =========================

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 6; 
}

// Yeni: Giriş denemelerinde rate limit veya hatalı giriş uyarısı eklemek için (isteğe bağlı)
function passwordMatch($inputPassword, $hashedPassword) {
    return password_verify($inputPassword, $hashedPassword);
}

// =========================
// Formatlama Fonksiyonları
// =========================

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₺';
}

function formatDateTime($datetime) {
    return date('d.m.Y H:i', strtotime($datetime));
}

function formatTime($time) {
    return date('H:i', strtotime($time));
}

function getRoleName($role) {
    switch ($role) {
        case 'admin': return 'Sistem Yöneticisi';
        case 'company_admin': return 'Firma Yöneticisi';
        case 'user': return 'Yolcu';
        default: return 'Bilinmeyen';
    }
}

function getStatusName($status) {
    switch ($status) {
        case 'ACTIVE': return 'Aktif';
        case 'CANCELLED': return 'İptal Edildi';
        case 'EXPIRED': return 'Süresi Doldu';
        default: return 'Bilinmeyen';
    }
}

// =========================
// Kullanıcı Arayüzü Fonksiyonları
// =========================

function showAlert($message, $type = 'info') {
    $alertClass = "alert-$type";
    echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>";
    echo htmlspecialchars($message);
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
    echo "</div>";
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// =========================
// Koltuk Haritası
// =========================

function generateSeatMap($capacity, $bookedSeats) {
    $seats = [];
    for ($i = 1; $i <= $capacity; $i++) {
        $seats[$i] = !in_array($i, $bookedSeats); // true = boş, false = dolu
    }
    return $seats;
}

// =========================
// Kupon ve İndirim Hesaplama
// =========================

function calculateDiscount($price, $discountPercent) {
    return $price - ($price * $discountPercent / 100);
}

// Yeni: Kupon geçerliliğini kontrol et (bozuk veya süresi dolmuş kuponları engeller)
function isCouponValid($coupon) {
    if (!$coupon) return false;
    $now = new DateTime();
    $expiry = new DateTime($coupon['valid_until']);
    return $now < $expiry && $coupon['discount_percent'] > 0;
}

// =========================
// Yardımcı Küçük Fonksiyonlar
// =========================

// Oturumdaki kullanıcı ID’sini kolayca almak için
function currentUserId() {
    return isLoggedIn() ? $_SESSION['user']['id'] : null;
}

// Şirket ID'si (firma yöneticileri için)
function currentCompanyId() {
    return (isLoggedIn() && $_SESSION['user']['role'] === 'company_admin')
        ? $_SESSION['user']['company_id']
        : null;
}
?>
