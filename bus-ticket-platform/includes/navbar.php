<?php
// DÜZELTİLDİ: functions.php aynı klasörde olduğu için direkt çağırıyoruz
require_once __DIR__ . '/functions.php';

// Kök dizinden mi yoksa alt klasörden mi erişim yapılıyor kontrolü
// Eğer /user/, /admin/ veya /company/ klasörlerinden birindeysek, $basePath = '../' olur.
// Değilse (ana dizindeysek), $basePath = '' olur.
$basePath = (strpos($_SERVER['PHP_SELF'], '/user/') !== false ||
             strpos($_SERVER['PHP_SELF'], '/admin/') !== false ||
             strpos($_SERVER['PHP_SELF'], '/company/') !== false)
             ? '../' : '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= $basePath ?>index.php">🚌 Otobüs Bileti</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Menüyü Aç">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"
                       href="<?= $basePath ?>index.php">Ana Sayfa</a>
                </li>

                <?php if (isLoggedIn() && $_SESSION['user']['role'] === 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'my_tickets.php' ? 'active' : '' ?>"
                           href="<?= $basePath ?>user/my_tickets.php">🎫 Biletlerim</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light fw-semibold" href="#" id="navbarDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            👋 <?= htmlspecialchars($_SESSION['user']['full_name']); ?>
                            (<?= getRoleName($_SESSION['user']['role']); ?>)
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= $basePath ?>admin/dashboard.php">⚙️ Admin Paneli</a></li>
                                <li><a class="dropdown-item" href="<?= $basePath ?>admin/manage_users.php">👤 Kullanıcılar</a></li>
                                <li><a class="dropdown-item" href="<?= $basePath ?>admin/manage_companies.php">🏢 Firmalar</a></li>
                                <li><a class="dropdown-item" href="<?= $basePath ?>admin/manage_coupons.php">💸 Kuponlar</a></li>

                            <?php elseif ($_SESSION['user']['role'] === 'company_admin'): ?>
                                <li><a class="dropdown-item" href="<?= $basePath ?>company/dashboard.php">📊 Firma Paneli</a></li>
                                <li><a class="dropdown-item" href="<?= $basePath ?>company/manage_routes.php">🚌 Sefer Yönetimi</a></li>
                                <li><a class="dropdown-item" href="<?= $basePath ?>company/manage_coupons.php">🎟️ Kuponlar</a></li>

                            <?php elseif ($_SESSION['user']['role'] === 'user'): ?>
                                <li><a class="dropdown-item" href="<?= $basePath ?>user/my_tickets.php">🎫 Biletlerim</a></li>
                                <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="<?= $basePath ?>logout.php">🚪 Çıkış Yap</a></li>
                        </ul>
                    </li>
                <?php else: // Giriş yapılmamışsa ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $basePath ?>login.php">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $basePath ?>register.php">Kayıt Ol</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>