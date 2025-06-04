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
     * get refund by ID with full details
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

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error rejecting refund: " . $e->getMessage());
            throw $e;
        }
    }
}