<?php

// models/BookingModel.php
namespace models;

use config\Database;
use PDO;

class BookingModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getBookingsByStatus($status, $filters = []) {
        $sql = "
            SELECT 
                b.*, u.first_name, u.last_name, u.email, u.contact_no as phone,
                p.amount_paid, p.balance, p.payment_method, p.payment_type, 
                p.status as payment_status, p.payment_date, 
                p.refund_amount, p.refund_date
            FROM tbl_bookings b
            LEFT JOIN tbl_users u ON b.user_id = u.id
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.status $status
        ";

        if (!empty($filters['date_from'])) {
            $sql .= " AND b.reservation_date >= :date_from";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND b.reservation_date <= :date_to";
        }

        $sql .= ($status === "'approved'") ? " ORDER BY b.reservation_date ASC, b.start_time ASC" : " ORDER BY b.updated_at DESC";

        $stmt = $this->pdo->prepare($sql);

        foreach ($filters as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>