<?php
/**
 * BookingService - handles all admin side booking-related database operations
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

class BookingService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * get all pending bookings with user and payment information
     * @return array
     */
    public function getPendingBookings(): array
    {
        $sql = "
            SELECT 
                b.*,
                u.first_name,
                u.last_name,
                u.email,
                u.contact_no as phone,
                p.amount_paid,
                p.balance,
                p.payment_method,
                p.payment_type,
                p.status as payment_status,
                p.payment_date
            FROM tbl_bookings b
            LEFT JOIN tbl_users u ON b.user_id = u.id
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.status = 'pending'
            ORDER BY b.created_at DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching pending bookings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * get all approved bookings with user and payment information
     * @return array
     */
    public function getApprovedBookings(): array
    {
        $sql = "
            SELECT 
                b.*,
                u.first_name,
                u.last_name,
                u.email,
                u.contact_no as phone,
                p.amount_paid,
                p.balance,
                p.payment_method,
                p.payment_type,
                p.status as payment_status,
                p.payment_date
            FROM tbl_bookings b
            LEFT JOIN tbl_users u ON b.user_id = u.id
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.status = 'approved'
            ORDER BY b.reservation_date ASC, b.start_time ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching approved bookings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * get booking history (completed and cancelled bookings)
     * @return array
     */
    public function getBookingHistory(): array
    {
        $sql = "
            SELECT 
                b.*,
                u.first_name,
                u.last_name,
                u.email,
                u.contact_no as phone,
                p.amount_paid,
                p.balance,
                p.payment_method,
                p.payment_type,
                p.status as payment_status,
                p.payment_date,
                p.refund_amount,
                p.refund_date
            FROM tbl_bookings b
            LEFT JOIN tbl_users u ON b.user_id = u.id
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.status IN ('completed', 'cancelled')
            ORDER BY b.updated_at DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching booking history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * get bookings by status with optional filters
     * @param string $status
     * @param array $filters Optional filters (date_from, date_to, user_id, etc.)
     * @return array
     */
    public function getBookingsByStatus(string $status, array $filters = []): array
    {
        $sql = "
            SELECT 
                b.*,
                u.first_name,
                u.last_name,
                u.email,
                u.contact_no as phone,
                p.amount_paid,
                p.balance,
                p.payment_method,
                p.payment_type,
                p.status as payment_status,
                p.payment_date,
                p.refund_amount,
                p.refund_date
            FROM tbl_bookings b
            LEFT JOIN tbl_users u ON b.user_id = u.id
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.status = :status
        ";

        $params = ['status' => $status];

        // add filters if provided
        if (!empty($filters['date_from'])) {
            $sql .= " AND b.reservation_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND b.reservation_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND b.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['event_type'])) {
            $sql .= " AND b.event_type = :event_type";
            $params['event_type'] = $filters['event_type'];
        }

        // add appropriate ordering
        switch ($status) {
            case 'pending':
                $sql .= " ORDER BY b.created_at DESC";
                break;
            case 'approved':
                $sql .= " ORDER BY b.reservation_date ASC, b.start_time ASC";
                break;
            default:
                $sql .= " ORDER BY b.updated_at DESC";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching bookings by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * get booking statistics/counts
     * @return array
     */
    public function getBookingStats(): array
    {
        $sql = "
            SELECT 
                status,
                COUNT(*) as count
            FROM tbl_bookings 
            GROUP BY status
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // convert to associative array for easier access
            $stats = [];
            foreach ($results as $row) {
                $stats[$row['status']] = (int)$row['count'];
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error fetching booking stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * get a specific booking by ID with all related data
     * @param int $bookingId
     * @return array|null
     */
    public function getBookingById(int $bookingId): ?array
    {
        $sql = "
            SELECT 
                b.*,
                u.first_name,
                u.last_name,
                u.email,
                u.contact_no as phone,
                p.amount_paid,
                p.balance,
                p.payment_method,
                p.payment_type,
                p.status as payment_status,
                p.payment_date,
                p.refund_amount,
                p.refund_date
            FROM tbl_bookings b
            LEFT JOIN tbl_users u ON b.user_id = u.id
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.id = :booking_id
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['booking_id' => $bookingId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching booking by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * update booking status
     * @param int $bookingId
     * @param string $status
     * @return bool
     */
    public function updateBookingStatus(int $bookingId, string $status): bool
    {
        $sql = "UPDATE tbl_bookings SET status = :status, updated_at = NOW() WHERE id = :booking_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'status' => $status,
                'booking_id' => $bookingId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating booking status: " . $e->getMessage());
            return false;
        }
    }
}