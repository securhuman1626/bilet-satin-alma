<?php
// src/init_db.php
$dbFile = __DIR__ . '/data/app.sqlite';
if (!file_exists(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0777, true);
}
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// tablo oluştur
$schema = [
    "CREATE TABLE IF NOT EXISTS users (
        id TEXT PRIMARY KEY,
        full_name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        role TEXT NOT NULL,
        password TEXT NOT NULL,
        company_id TEXT,
        balance INTEGER DEFAULT 100000,
        created_at TEXT NOT NULL
    );",
    "CREATE TABLE IF NOT EXISTS bus_company (
        id TEXT PRIMARY KEY,
        name TEXT UNIQUE NOT NULL,
        logo_path TEXT,
        created_at TEXT
    );",
    "CREATE TABLE IF NOT EXISTS trips (
        id TEXT PRIMARY KEY,
        company_id TEXT NOT NULL,
        destination_city TEXT NOT NULL,
        arrival_time TEXT NOT NULL,
        departure_time TEXT NOT NULL,
        departure_city TEXT NOT NULL,
        price INTEGER NOT NULL,
        capacity INTEGER NOT NULL,
        created_date TEXT
    );",
    "CREATE TABLE IF NOT EXISTS tickets (
        id TEXT PRIMARY KEY,
        trip_id TEXT NOT NULL,
        user_id TEXT NOT NULL,
        status TEXT DEFAULT 'active',
        total_price INTEGER NOT NULL,
        created_at TEXT
    );"
];

foreach ($schema as $sql) {
    $db->exec($sql);
}

// admin kullanıcı ekle
$check = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$check->execute(['admin@example.com']);
if ($check->fetchColumn() == 0) {
    $insert = $db->prepare("INSERT INTO users (id, full_name, email, role, password, balance, created_at)
        VALUES (:id, :full_name, :email, :role, :password, :balance, :created_at)");
    $insert->execute([
        ':id' => uniqid(),
        ':full_name' => 'Admin User',
        ':email' => 'admin@example.com',
        ':role' => 'admin',
        ':password' => password_hash('admin123', PASSWORD_DEFAULT),
        ':balance' => 100000,
        ':created_at' => date('Y-m-d H:i:s')
    ]);
}

echo "✅ Database initialized and admin user created successfully!";
