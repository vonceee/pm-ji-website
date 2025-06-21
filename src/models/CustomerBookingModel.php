<?php
// src/models/CustomerBookingModel.php
namespace Models;

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;
use PDO;

class CustomerBookingModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get user ID by email
     */
    public function getUserIdByEmail($email)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT id FROM tbl_users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetchColumn();
            return $result ? (int) $result : false;
        } catch (\PDOException $e) {
            error_log("Database error in getUserIdByEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count total bookings with filters
     */
    public function countBookings($userId, $search = '', $filter_date = '', $filter_status = '')
    {
        try {
            $where = 'b.user_id = :user_id';
            $params = [':user_id' => $userId];

            // Apply filters
            $this->applyFilters($where, $params, $search, $filter_date, $filter_status);

            $sql = "SELECT COUNT(*) FROM tbl_bookings b WHERE {$where}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Database error in countBookings: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get bookings with pagination and filters
     */
    public function getBookings($userId, $limit, $offset, $search = '', $filter_date = '', $filter_status = '')
    {
        try {
            $where = 'b.user_id = :user_id';
            $params = [':user_id' => $userId];

            // Apply filters
            $this->applyFilters($where, $params, $search, $filter_date, $filter_status);

            $sql = $this->buildBookingsQuery($where);
            
            // Add pagination
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameters
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            foreach ($params as $key => $value) {
                if (in_array($key, [':limit', ':offset'], true)) {
                    continue;
                }
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in getBookings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single booking by ID for the user
     */
    public function getBookingById($bookingId, $userId)
    {
        try {
            $sql = $this->buildBookingsQuery('b.id = :booking_id AND b.user_id = :user_id');
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':booking_id' => $bookingId,
                ':user_id' => $userId
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in getBookingById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get booking by reference ID for the user
     */
    public function getBookingByReferenceId($referenceId, $userId)
    {
        try {
            $sql = $this->buildBookingsQuery('b.reference_id = :reference_id AND b.user_id = :user_id');
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':reference_id' => $referenceId,
                ':user_id' => $userId
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in getBookingByReferenceId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus($bookingId, $status, $userId)
    {
        try {
            $sql = "UPDATE tbl_bookings SET status = :status WHERE id = :booking_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':booking_id' => $bookingId,
                ':user_id' => $userId
            ]);
        } catch (\PDOException $e) {
            error_log("Database error in updateBookingStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's booking statistics
     */
    public function getUserBookingStats($userId)
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN status IN ('cancelled_by_user', 'cancelled_by_admin') THEN 1 ELSE 0 END) as cancelled_bookings
                FROM tbl_bookings 
                WHERE user_id = :user_id
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error in getUserBookingStats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if booking exists and belongs to user
     */
    public function bookingBelongsToUser($bookingId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tbl_bookings WHERE id = :booking_id AND user_id = :user_id");
            $stmt->execute([':booking_id' => $bookingId, ':user_id' => $userId]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Database error in bookingBelongsToUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Private helper methods
     */
    private function applyFilters(&$where, &$params, $search, $filter_date, $filter_status)
    {
        if (!empty($search)) {
            $where .= ' AND (b.event_type LIKE :search1 OR b.reference_number LIKE :search2)';
            $params[':search1'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
        }

        if (!empty($filter_date)) {
            $where .= ' AND b.reservation_date = :filter_date';
            $params[':filter_date'] = $filter_date;
        }

        if (!empty($filter_status)) {
            $where .= ' AND b.status = :filter_status';
            $params[':filter_status'] = $filter_status;
        }
    }

    private function buildBookingsQuery($whereClause)
    {
        return "
            SELECT
                b.id,
                b.event_type,
                b.duration,
                b.reservation_date,
                b.start_time,
                b.end_time,
                b.street_address,
                b.barangay,
                b.city,
                b.reference_number,
                b.reference_id,
                b.status,
                b.created_at,
                b.full_address,
                p.payment_method,
                p.payment_type,
                p.status AS payment_status,
                p.amount_paid,
                p.balance,
                p.payment_date,
                p.payment_screenshot_path,
                p.payment_screenshot_thumbnail,
                c.reason AS cancellation_reason,
                c.cancelled_at,
                c.refund_status,
                c.refund_amount,
                c.admin_notes AS cancellation_admin_notes
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            LEFT JOIN tbl_cancellations c ON b.id = c.booking_id
            WHERE {$whereClause}
            ORDER BY b.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
    }
}