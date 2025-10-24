<?php
// Bu script, 'admin' rolünde bir kullanıcı oluşturur.
// Çalıştırdıktan sonra GÜVENLİK İÇİN HEMEN SİLİN!

require_once 'config/database.php';

try {
    // Bu kod, 'config/database.php' içindeki güvenli yolu okuyacak
    // ve admin'i 'db_storage' içindeki veritabanına ekleyecek.
    $db = new Database();
    
    // --- Admin Bilgileri (BUNLARI KULLANARAK GİRİŞ YAPACAKSIN) ---
    $admin_fullname = "Sistem Yöneticisi";
    $admin_email = "admin@example.com";
    $admin_password = "GucluSifre123!"; // Giriş şifren bu olacak
    // ------------------------------------------------

    // E-posta zaten var mı diye kontrol et
    if ($db->getUserByEmail($admin_email)) {
        echo "<h1>Hata!</h1>";
        echo "<p>Bu e-posta adresi ($admin_email) zaten kayıtlı.</p>";
        echo "<p>Lütfen giriş yapmayı deneyin veya bu script'teki e-postayı değiştirin.</p>";
    } else {
        // 'admin' rolüyle yeni kullanıcı oluştur
        $db->createUser($admin_fullname, $admin_email, $admin_password, 'admin');
        
        echo "<h1>Başarılı!</h1>";
        echo "<p>Admin kullanıcısı başarıyla oluşturuldu.</p>";
        echo "<ul>";
        echo "<li><strong>E-posta:</strong> " . htmlspecialchars($admin_email) . "</li>";
        echo "<li><strong>Şifre:</strong> " . htmlspecialchars($admin_password) . "</li>";
        echo "</ul>";
        echo "<p><a href='login.php'>Giriş yapmak için buraya tıklayın.</a></p>";
        echo "<p style='color:red; font-weight:bold;'>GÜVENLİK UYARISI: Lütfen bu 'create_admin.php' dosyasını hemen sunucudan silin!</p>";
    }

} catch (Exception $e) {
    error_log("Admin oluşturma hatası: " . $e->getMessage());
    echo "Admin oluşturulurken kritik bir hata oluştu. Logları kontrol edin.";
}
?>