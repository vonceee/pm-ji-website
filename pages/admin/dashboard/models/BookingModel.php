<?php

// models/BookingModel.php
namespace models;

use config\Database;
use PDO;

class BookingModel {
    private $pdo;

    public function __construct() {
        // get the PDO database connection
        $this->pdo = Database::getConnection();
    }

    /**
     * fetch bookings by status, handles; date-filtering, pagination
     * 
     * @param string $status SQL status condition (e.g., "= 'pending'")
     * @param array $filters handle date-filtering: ['date_from' => ..., 'date_to' => ...]
     * @param int $page track current page number for pagination
     * @param int $limit define number of items per page (e.g, 6 means will show 6 items per page)
     * @return array return list of bookings with all booking details, payments details, and user details
     */
    public function getBookingsByStatus($status, $filters = [], $page = 1, $limit = 6) {

        // calculate the offset for pagination ()
        $offset = ($page - 1) * $limit;
        
        // build the base SQL query with joins for booking details, payments details, and user details
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

        $params = [];
        
        // handle date filtering if provided (else if no date filtering is set, will return all bookings)
        if (!empty($filters['date_from'])) {
            $sql .= " AND b.reservation_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND b.reservation_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        // arrange order of results depending on status (ascending; date, time)
        $sql .= ($status === "'approved'") ? " ORDER BY b.reservation_date ASC, b.start_time ASC" : " ORDER BY b.updated_at DESC";
        // limit number of results based on pagination $limit
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        // bind date-filtering parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        // bind pagination parameters
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * count total bookings by status including date-filtering.
     * 
     * @param string $status SQL status condition (e.g., "= 'pending'")
     * @param array $filters handle date-filtering: ['date_from' => ..., 'date_to' => ...]
     * @return int return total number of bookings matching the criteria
     */
    public function getBookingsCountByStatus($status, $filters = []) {

        // build SQL query
        $sql = "
            SELECT COUNT(*) as total
            FROM tbl_bookings b
            WHERE b.status $status
        ";

        $params = [];
        
        // handle date filtering if provided (else if no date filtering is set, will return all bookings)
        if (!empty($filters['date_from'])) {
            $sql .= " AND b.reservation_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND b.reservation_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $stmt = $this->pdo->prepare($sql);

        // bind date-filtering parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}

?>