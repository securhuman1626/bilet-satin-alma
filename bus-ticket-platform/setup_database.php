<?php
// Bu script, 'config/database.php' dosyasındaki güvenli yolu okuyacak
// ve tabloları 'db_storage' klasörünün içine kuracak.
require_once 'config/database.php';

try {
    $db = new Database();
    $pdo = $db->getPdo();
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // USERS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id TEXT PRIMARY KEY,
            full_name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('admin', 'company_admin', 'user')),
            password TEXT NOT NULL,
            company_id TEXT,
            balance INTEGER DEFAULT 800,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES bus_companies(id)
        );
    ");

    // BUS COMPANIES
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bus_companies (
            id TEXT PRIMARY KEY,
            name TEXT UNIQUE NOT NULL,
            logo_path TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // TRIPS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS trips (
            id TEXT PRIMARY KEY,
            company_id TEXT NOT NULL,
            destination_city TEXT NOT NULL,
            arrival_time DATETIME NOT NULL,
            departure_time DATETIME NOT NULL,
            departure_city TEXT NOT NULL,
            price INTEGER NOT NULL,
            capacity INTEGER NOT NULL,
            created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES bus_companies(id)
        );
    ");

    // COUPONS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS coupons (
            id TEXT PRIMARY KEY,
            code TEXT NOT NULL,
            discount REAL NOT NULL,
            company_id TEXT,
            usage_limit INTEGER NOT NULL,
            expire_date DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES bus_companies(id)
        );
    ");

    // USER COUPONS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_coupons (
            id TEXT PRIMARY KEY,
            coupon_id TEXT NOT NULL,
            user_id TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (coupon_id) REFERENCES coupons(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
    ");

    // TICKETS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tickets (
            id TEXT PRIMARY KEY,
            trip_id TEXT NOT NULL,
            user_id TEXT NOT NULL,
            status TEXT DEFAULT 'active' CHECK(status IN ('active', 'canceled', 'expired')),
            total_price INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (trip_id) REFERENCES trips(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
    ");

    // BOOKED SEATS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS booked_seats (
            id TEXT PRIMARY KEY,
            ticket_id TEXT NOT NULL,
            seat_number INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id)
        );
    ");

    echo "<h1>✅ Veritabanı başarıyla oluşturuldu!</h1>";
    echo "<p>Tüm tablolar diyagrama göre güncellendi.</p>";
    echo "<p style='color:red;font-weight:bold;'>Kurulumdan sonra bu dosyayı silmeyi unutma!</p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>Hata oluştu: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
