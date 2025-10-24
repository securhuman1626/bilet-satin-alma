<?php

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $dbPath = __DIR__ . '/../db_storage/bus_ticket.db';
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 🔥 Foreign key desteğini aktif et
            $this->pdo->exec('PRAGMA foreign_keys = ON;');

        } catch (PDOException $e) {
            error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
            die("Sistemde bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
        }
    }

    /* =======================
       KULLANICI İŞLEMLERİ
    ======================= */
    public function createUser($fullName, $email, $password, $role = 'user', $companyId = null) {
        $id = uniqid('U');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            INSERT INTO users (id, full_name, email, password, role, company_id, balance)
            VALUES (?, ?, ?, ?, ?, ?, 800)
        ");
        return $stmt->execute([$id, $fullName, $email, $hashedPassword, $role, $companyId]);
    }

    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("
            SELECT u.*, bc.name as company_name 
            FROM users u
            LEFT JOIN bus_companies bc ON u.company_id = bc.id
            ORDER BY u.role, u.full_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompanyAdmins() {
        $stmt = $this->pdo->prepare("
            SELECT u.*, bc.name as company_name
            FROM users u
            LEFT JOIN bus_companies bc ON u.company_id = bc.id
            WHERE u.role = 'company_admin'
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCompanyAdmin($full_name, $email, $password, $company_id) {
        $id = uniqid('U');
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            INSERT INTO users (id, full_name, email, password, role, company_id)
            VALUES (?, ?, ?, ?, 'company_admin', ?)
        ");
        return $stmt->execute([$id, $full_name, $email, $hashed, $company_id]);
    }

    /* =======================
       FİRMA İŞLEMLERİ
    ======================= */
    public function createCompany($name, $logoPath = null) {
        $id = uniqid('C');
        $stmt = $this->pdo->prepare("INSERT INTO bus_companies (id, name, logo_path) VALUES (?, ?, ?)");
        return $stmt->execute([$id, $name, $logoPath]);
    }

    public function deleteCompany($id) {
        // ✅ Zincirleme silme desteği: trips, coupons ve users da silinir
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM trips WHERE company_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM coupons WHERE company_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM users WHERE company_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM bus_companies WHERE id = ?")->execute([$id]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Firma silme hatası: ' . $e->getMessage());
            return false;
        }
    }

    public function getAllCompanies() {
        $stmt = $this->pdo->prepare("SELECT * FROM bus_companies ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =======================
       SEFER İŞLEMLERİ
    ======================= */
    public function addRoute($companyId, $departureCity, $destinationCity, $departureTime, $arrivalTime, $price, $capacity) {
        $id = uniqid('T');
        $stmt = $this->pdo->prepare("
            INSERT INTO trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$id, $companyId, $departureCity, $destinationCity, $departureTime, $arrivalTime, $price, $capacity]);
    }

    public function getTripById($id) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, bc.name as company_name
            FROM trips t
            JOIN bus_companies bc ON t.company_id = bc.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchTrips($departureCity, $destinationCity, $departureDate) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, bc.name as company_name 
            FROM trips t 
            JOIN bus_companies bc ON t.company_id = bc.id 
            WHERE t.departure_city = ? 
            AND t.destination_city = ? 
            AND DATE(t.departure_time) = ?
            ORDER BY t.departure_time
        ");
        $stmt->execute([$departureCity, $destinationCity, $departureDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompanyRoutes($companyId) {
        $stmt = $this->pdo->prepare("SELECT * FROM trips WHERE company_id = ? ORDER BY departure_time DESC");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =======================
       KUPON İŞLEMLERİ
    ======================= */
    public function getAllCoupons() {
        $stmt = $this->pdo->prepare("SELECT * FROM coupons ORDER BY expire_date DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompanyCoupons($company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE company_id = ? ORDER BY expire_date DESC");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCompanyCoupon($company_id, $code, $discount, $expire_date) {
        $stmt = $this->pdo->prepare("
            INSERT INTO coupons (id, company_id, code, discount, expire_date, usage_limit)
            VALUES (?, ?, ?, ?, ?, 100)
        ");
        return $stmt->execute([uniqid('CP'), $company_id, $code, $discount, $expire_date]);
    }

    public function addGlobalCoupon($code, $discount, $expire_date, $usage_limit = 100) {
        $stmt = $this->pdo->prepare("
            INSERT INTO coupons (id, code, discount, usage_limit, expire_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([uniqid('GC'), $code, $discount, $usage_limit, $expire_date]);
    }

    public function deleteCompanyCoupon($coupon_id, $company_id) {
        $stmt = $this->pdo->prepare("DELETE FROM coupons WHERE id = ? AND company_id = ?");
        return $stmt->execute([$coupon_id, $company_id]);
    }

    public function deleteGlobalCoupon($coupon_id) {
        $stmt = $this->pdo->prepare("DELETE FROM coupons WHERE id = ?");
        return $stmt->execute([$coupon_id]);
    }

    /* =======================
       BİLET İŞLEMLERİ
    ======================= */
    public function createTicket($tripId, $userId, $totalPrice, $seatNumber) {
        $ticketId = uniqid('TKT');
        $seatId = uniqid('S');
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("
                INSERT INTO tickets (id, trip_id, user_id, total_price, status, created_at)
                VALUES (?, ?, ?, ?, 'active', datetime('now'))
            ")->execute([$ticketId, $tripId, $userId, $totalPrice]);

            $this->pdo->prepare("
                INSERT INTO booked_seats (id, ticket_id, seat_number)
                VALUES (?, ?, ?)
            ")->execute([$seatId, $ticketId, $seatNumber]);

            $this->pdo->prepare("
                UPDATE users SET balance = balance - ? WHERE id = ?
            ")->execute([$totalPrice, $userId]);

            $this->pdo->commit();
            return $ticketId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function cancelTicket($ticketId) {
        $stmt = $this->pdo->prepare("SELECT user_id, total_price, status FROM tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket || $ticket['status'] !== 'active') return false;

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("UPDATE tickets SET status = 'canceled' WHERE id = ?")
                      ->execute([$ticketId]);

            $this->pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
                      ->execute([$ticket['total_price'], $ticket['user_id']]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Bilet iptali hatası: ' . $e->getMessage());
            return false;
        }
    }

    public function getBookedSeats($tripId) {
        $stmt = $this->pdo->prepare("
            SELECT bs.seat_number 
            FROM booked_seats bs 
            JOIN tickets t ON bs.ticket_id = t.id 
            WHERE t.trip_id = ? AND t.status = 'active'
        ");
        $stmt->execute([$tripId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getUserTickets($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                t.id,
                t.total_price,
                t.status,
                t.created_at,
                tr.departure_city,
                tr.destination_city,
                tr.departure_time,
                tr.arrival_time,
                bc.name AS company_name,
                GROUP_CONCAT(bs.seat_number) AS seat_numbers
            FROM tickets t
            JOIN trips tr ON t.trip_id = tr.id
            JOIN bus_companies bc ON tr.company_id = bc.id
            LEFT JOIN booked_seats bs ON bs.ticket_id = t.id
            WHERE t.user_id = ?
            GROUP BY t.id
            ORDER BY datetime(t.created_at) DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketById($ticketId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                t.id AS ticket_id,
                t.user_id,
                t.total_price,
                t.status,
                tr.departure_time,
                tr.arrival_time,
                tr.departure_city,
                tr.destination_city,
                bc.name AS company_name,
                bs.seat_number
            FROM tickets t
            JOIN trips tr ON t.trip_id = tr.id
            JOIN bus_companies bc ON tr.company_id = bc.id
            LEFT JOIN booked_seats bs ON bs.ticket_id = t.id
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>
