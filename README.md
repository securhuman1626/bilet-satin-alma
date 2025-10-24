[README.md](https://github.com/user-attachments/files/23134723/README.md)
# 🚌 Bilet Satın Alma Platformu

Bu proje, kullanıcıların **otobüs bileti satın alabileceği**, firma yöneticilerinin ise **sefer yönetimi** ve **kupon oluşturma** işlemlerini yapabileceği web tabanlı bir bilet sistemidir.  
Proje, **Docker** ile container yapısında çalışacak şekilde tasarlanmıştır.

---

## 🚀 Özellikler

- 👤 **Kullanıcı Paneli**
  - Bilet arama, satın alma, iptal etme
  - Geçmiş biletleri görüntüleme
- 🏢 **Firma Paneli**
  - Sefer ekleme, düzenleme, silme
  - Kupon ekleme ve kupon yönetimi
- 🛠️ **Admin Paneli**
  - Firmaları ve kullanıcıları yönetme
  - Sistemi genel olarak denetleme
- 🔐 **Güvenli Giriş Sistemi**
  - Şifreler `password_hash()` ile güvenli biçimde saklanır.
- 💾 **SQLite Veritabanı**
  - Docker konteyner içinde otomatik oluşturulur.

---

## 🧩 Teknolojiler

| Teknoloji | Kullanım Amacı |
|------------|----------------|
| **PHP 8.1** | Backend geliştirme |
| **SQLite3** | Hafif veritabanı sistemi |
| **Bootstrap 5** | Arayüz tasarımı |
| **Docker & Docker Compose** | Uygulama container’ı oluşturma |
| **Apache2** | Web sunucusu |

---

## ⚙️ Kurulum (Docker ile)

1. **Projeyi klonla**
   ```git clone https://github.com/securhuman1626/bilet-satin-alma.git
      cd bilet-satin-alma
   ```

2. **Docker container’ı başlat**
   ```docker-compose up --build```

3. **Tarayıcıdan aç**
   ```
   http://localhost:8080
   ```

4. **Veritabanı oluştur (ilk kez çalıştırırken)**
   ```docker exec -it bus-ticket-web php setup_database.php```

---
## 🧰 Kurulum Sonrası Adımlar

Admin hesabı oluştur

Uygulama ilk kez çalıştığında admin hesabı yoksa aşağıdaki adrese gidin:

```http://localhost:8080/create_admin.php```

Ad, e-posta ve şifre bilgilerini girip kaydedin.

Artık admin paneline giriş yapabilirsiniz:

```http://localhost:8080/admin/login.php```

## 📁 Proje Klasör Yapısı

```
bus-ticket-platform/
│
├── admin/                 → Admin işlemleri
├── assets/                → CSS, JS, görseller
├── company/               → Firma yönetimi sayfaları
├── user/                  → Kullanıcı işlemleri
├── config/                → Veritabanı bağlantısı
├── database/              → SQLite veritabanı dosyası
├── includes/              → Ortak bileşenler (navbar vb.)
├── docker-compose.yml     → Docker servis tanımı
├── Dockerfile             → Docker imaj yapılandırması
└── setup_database.php     → Veritabanı başlangıç dosyası
```

---

## 👨‍💻 Geliştirici

**Furkan Karataş**  
📧 karatasfurkan56@gmail.com  
🎓 Bursa Teknik Üniversitesi - Bilgisayar Mühendisliği  
🔒 Siber Güvenlik & Web Uygulama Güvenliği
