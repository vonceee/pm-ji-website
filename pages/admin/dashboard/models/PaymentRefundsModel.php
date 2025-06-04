<?php

namespace Models;

class PaymentRefundsModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * get all pending refunds with booking and user details
     */
    public function getAllRefundPayments($limit = 20, $offset = 0, $status = 'pending')
    {
        try {
            $sql = "
                SELECT 
                    c.id as refund_id,
                    c.booking_id,
                    c.user_id,
                    c.reason,
                    c.cancelled_at,
                    c.refund_status,
                    c.refund_amount,
                    c.refund_processed_at,
                    c.admin_notes,
                    b.reference_id,
                    b.event_type,
                    b.city,
                    b.reservation_date,
                    b.start_time,
                    b.total_amount,
                    p.amount_paid,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.phone_number,
                    u.email
                FROM tbl_cancellations c
                INNER JOIN tbl_bookings b ON c.booking_id = b.id
                INNER JOIN tbl_users u ON c.user_id = u.id
                LEFT JOIN (
                    SELECT booking_id, SUM(amount) as amount_paid
                    FROM tbl_payments 
                    WHERE payment_status = 'completed'
                    GROUP BY booking_id
                ) p ON b.id = p.booking_id
                WHERE c.refund_status = :status
                ORDER BY c.cancelled_at DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("error fetching refund payments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get refund by ID with full details
     */
    public function getRefundById($refundId)
    {
        try {
            $sql = "
                SELECT 
                    c.*,
                    b.reference_id,
                    b.event_type,
                    b.city,
                    b.reservation_date,
                    b.start_time,
                    b.total_amount,
                    p.amount_paid,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.phone_number,
                    u.email
                FROM tbl_cancellations c
                INNER JOIN tbl_bookings b ON c.booking_id = b.id
                INNER JOIN tbl_users u ON c.user_id = u.id
                LEFT JOIN (
                    SELECT booking_id, SUM(amount) as amount_paid
                    FROM tbl_payments 
                    WHERE payment_status = 'completed'
                    GROUP BY booking_id
                ) p ON b.id = p.booking_id
                WHERE c.id = :refund_id
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching refund by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Process a refund (approve and mark as processed)
     */
    public function processRefund($refundId, $refundMethod, $adminNotes = '', $refundReference = '')
    {
        try {
            $this->pdo->beginTransaction();

            // Update the cancellation record
            $sql = "
                UPDATE tbl_cancellations 
                SET 
                    refund_status = 'processed',
                    refund_processed_at = NOW(),
                    admin_notes = :admin_notes,
                    updated_at = NOW()
                WHERE id = :refund_id AND refund_status = 'pending'
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);
            $stmt->bindParam(':admin_notes', $adminNotes, \PDO::PARAM_STR);
            $result = $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new \Exception('Refund not found or already processed');
            }

            // Log the refund transaction
            $this->logRefundTransaction($refundId, 'processed', $refundMethod, $adminNotes, $refundReference);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error processing refund: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reject a refund request
     */
    public function rejectRefund($refundId, $rejectionReason, $rejectionNotes = '')
    {
        try {
            $this->pdo->beginTransaction();

            // Update the cancellation record
            $adminNotes = "REJECTED - Reason: " . $rejectionReason;
            if (!empty($rejectionNotes)) {
                $adminNotes .= " | Notes: " . $rejectionNotes;
            }

            $sql = "
                UPDATE tbl_cancellations 
                SET 
                    refund_status = 'failed',
                    admin_notes = :admin_notes,
                    updated_at = NOW()
                WHERE id = :refund_id AND refund_status = 'pending'
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);
            $stmt->bindParam(':admin_notes', $adminNotes, \PDO::PARAM_STR);
            $result = $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new \Exception('Refund not found or already processed');
            }

            // Log the refund rejection
            $this->logRefundTransaction($refundId, 'rejected', '', $adminNotes, '');

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error rejecting refund: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get total count of refunds by status
     */
    public function getTotalRefundsCount($status = 'pending')
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_cancellations WHERE refund_status = :status";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error getting refunds count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get refunds with filters
     */
    public function getRefundsWithFilters($filters = [], $limit = 20, $offset = 0)
    {
        try {
            $whereConditions = [];
            $params = [];

            // Status filter
            if (!empty($filters['status'])) {
                $whereConditions[] = "c.refund_status = :status";
                $params[':status'] = $filters['status'];
            }

            // Amount range filter
            if (!empty($filters['amount_min'])) {
                $whereConditions[] = "c.refund_amount >= :amount_min";
                $params[':amount_min'] = $filters['amount_min'];
            }
            if (!empty($filters['amount_max'])) {
                $whereConditions[] = "c.refund_amount <= :amount_max";
                $params[':amount_max'] = $filters['amount_max'];
            }

            // Date range filter
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "DATE(c.cancelled_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "DATE(c.cancelled_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            // Search term filter
            if (!empty($filters['search'])) {
                $whereConditions[] = "(
                    b.reference_id LIKE :search OR 
                    CONCAT(u.first_name, ' ', u.last_name) LIKE :search OR 
                    u.phone_number LIKE :search OR
                    u.email LIKE :search
                )";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

            $sql = "
                SELECT 
                    c.id as refund_id,
                    c.booking_id,
                    c.user_id,
                    c.reason,
                    c.cancelled_at,
                    c.refund_status,
                    c.refund_amount,
                    c.refund_processed_at,
                    c.admin_notes,
                    b.reference_id,
                    b.event_type,
                    b.city,
                    b.reservation_date,
                    b.start_time,
                    b.total_amount,
                    p.amount_paid,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.phone_number,
                    u.email
                FROM tbl_cancellations c
                INNER JOIN tbl_bookings b ON c.booking_id = b.id
                INNER JOIN tbl_users u ON c.user_id = u.id
                LEFT JOIN (
                    SELECT booking_id, SUM(amount) as amount_paid
                    FROM tbl_payments 
                    WHERE payment_status = 'completed'
                    GROUP BY booking_id
                ) p ON b.id = p.booking_id
                $whereClause
                ORDER BY c.cancelled_at DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->pdo->prepare($sql);
            
            // Bind filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind pagination parameters
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching filtered refunds: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get refund statistics
     */
    public function getRefundStatistics()
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_refunds,
                    SUM(CASE WHEN refund_status = 'pending' THEN 1 ELSE 0 END) as pending_refunds,
                    SUM(CASE WHEN refund_status = 'processed' THEN 1 ELSE 0 END) as processed_refunds,
                    SUM(CASE WHEN refund_status = 'failed' THEN 1 ELSE 0 END) as failed_refunds,
                    SUM(CASE WHEN refund_status = 'processed' THEN refund_amount ELSE 0 END) as total_refunded_amount,
                    SUM(CASE WHEN refund_status = 'pending' THEN refund_amount ELSE 0 END) as pending_refund_amount
                FROM tbl_cancellations
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting refund statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log refund transaction for audit trail
     */
    private function logRefundTransaction($refundId, $action, $method = '', $notes = '', $reference = '')
    {
        try {
            // You can create a separate audit log table for refund transactions
            // For now, we'll just log to error_log
            $logData = [
                'refund_id' => $refundId,
                'action' => $action,
                'method' => $method,
                'notes' => $notes,
                'reference' => $reference,
                'timestamp' => date('Y-m-d H:i:s'),
                'admin_user' => $_SESSION['admin_id'] ?? 'system'
            ];

            error_log("Refund Transaction Log: " . json_encode($logData));

            // If you have an audit table, insert here:
            /*
            $sql = "
                INSERT INTO tbl_refund_audit_log 
                (refund_id, action, method, notes, reference_number, admin_id, created_at)
                VALUES (:refund_id, :action, :method, :notes, :reference, :admin_id, NOW())
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':refund_id' => $refundId,
                ':action' => $action,
                ':method' => $method,
                ':notes' => $notes,
                ':reference' => $reference,
                ':admin_id' => $_SESSION['admin_id'] ?? null
            ]);
            */

        } catch (\Exception $e) {
            error_log("Error logging refund transaction: " . $e->getMessage());
        }
    }

    /**
     * Calculate refund amount based on cancellation policy
     */
    public function calculateRefundAmount($bookingId, $totalAmount, $cancellationDate)
    {
        try {
            // Get booking details
            $sql = "SELECT reservation_date, start_time FROM tbl_bookings WHERE id = :booking_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':booking_id', $bookingId, \PDO::PARAM_INT);
            $stmt->execute();
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                return 0;
            }

            // Calculate days between cancellation and event
            $eventDateTime = new \DateTime($booking['reservation_date'] . ' ' . $booking['start_time']);
            $cancellationDateTime = new \DateTime($cancellationDate);
            $daysDifference = $cancellationDateTime->diff($eventDateTime)->days;

            // Refund policy logic (you can customize this)
            $refundPercentage = 0;

            if ($daysDifference >= 30) {
                $refundPercentage = 1.0; // 100% refund
            } elseif ($daysDifference >= 14) {
                $refundPercentage = 0.8; // 80% refund
            } elseif ($daysDifference >= 7) {
                $refundPercentage = 0.5; // 50% refund
            } elseif ($daysDifference >= 3) {
                $refundPercentage = 0.25; // 25% refund
            } else {
                $refundPercentage = 0; // No refund
            }

            return round($totalAmount * $refundPercentage, 2);

        } catch (\Exception $e) {
            error_log("Error calculating refund amount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update refund amount
     */
    public function updateRefundAmount($refundId, $newAmount)
    {
        try {
            $sql = "
                UPDATE tbl_cancellations 
                SET refund_amount = :amount, updated_at = NOW() 
                WHERE id = :refund_id
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':amount', $newAmount, \PDO::PARAM_STR);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error updating refund amount: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get refunds for export
     */
    public function getRefundsForExport($filters = [])
    {
        try {
            $whereConditions = [];
            $params = [];

            // Apply filters similar to getRefundsWithFilters but without pagination
            if (!empty($filters['status'])) {
                $whereConditions[] = "c.refund_status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $whereConditions[] = "DATE(c.cancelled_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $whereConditions[] = "DATE(c.cancelled_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

            $sql = "
                SELECT 
                    c.id as refund_id,
                    b.reference_id,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.phone_number,
                    u.email,
                    b.event_type,
                    b.city,
                    b.reservation_date,
                    b.start_time,
                    c.reason,
                    c.cancelled_at,
                    c.refund_status,
                    b.total_amount,
                    p.amount_paid,
                    c.refund_amount,
                    c.refund_processed_at,
                    c.admin_notes
                FROM tbl_cancellations c
                INNER JOIN tbl_bookings b ON c.booking_id = b.id
                INNER JOIN tbl_users u ON c.user_id = u.id
                LEFT JOIN (
                    SELECT booking_id, SUM(amount) as amount_paid
                    FROM tbl_payments 
                    WHERE payment_status = 'completed'
                    GROUP BY booking_id
                ) p ON b.id = p.booking_id
                $whereClause
                ORDER BY c.cancelled_at DESC
            ";

            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching refunds for export: " . $e->getMessage());
            return [];
        }
    }
}