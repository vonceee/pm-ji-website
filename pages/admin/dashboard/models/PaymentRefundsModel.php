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
     * get all pending refunds with booking and user details - Updated to handle filters
     */
    public function getAllRefundPayments($param1 = 20, $param2 = 0, $param3 = 'pending', $param4 = '', $param5 = '')
    {
        // Handle both old and new parameter styles
        if (is_string($param1) && !is_numeric($param1)) {
            // Old style: getAllRefundPayments($dateFrom, $dateTo, ...)
            $dateFrom = $param1;
            $dateTo = $param2;
            $limit = 20;
            $offset = 0;
            $status = 'pending';
        } else {
            // New style: getAllRefundPayments($limit, $offset, $status, $dateFrom, $dateTo)
            $limit = $param1;
            $offset = $param2;
            $status = $param3;
            $dateFrom = $param4;
            $dateTo = $param5;
        }

        try {
            $whereConditions = ["c.refund_status = :status"];
            $params = [':status' => $status];

            // Add date filters if provided
            if (!empty($dateFrom)) {
                $whereConditions[] = "DATE(c.cancelled_at) >= :date_from";
                $params[':date_from'] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $whereConditions[] = "DATE(c.cancelled_at) <= :date_to";
                $params[':date_to'] = $dateTo;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

            $sql = "
                SELECT 
                    c.id as refund_id,
                    c.booking_id,
                    c.user_id,
                    c.reason,
                    c.cancelled_at,
                    c.refund_status,
                    c.refund_amount,
                    b.reference_id,
                    b.event_type,
                    b.city,
                    b.reservation_date,
                    b.start_time,
                    p.amount_paid,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.contact_no,
                    u.email
                FROM tbl_cancellations c
                INNER JOIN tbl_bookings b ON c.booking_id = b.id
                INNER JOIN tbl_users u ON c.user_id = u.id
                LEFT JOIN (
                    SELECT booking_id, SUM(amount_paid) as amount_paid  -- Fixed: was 'amount'
                    FROM tbl_payments 
                    WHERE status = 'Partial'  -- Fixed: was 'payment_status'
                    GROUP BY booking_id
                ) p ON b.id = p.booking_id
                {$whereClause}
                ORDER BY c.cancelled_at DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->pdo->prepare($sql);

            // Bind all parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int) $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("error fetching refund payments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * get total count of refunds
     */
    public function getTotalRefundsCount($status = 'pending')
    {
        try {
            $sql = "
                SELECT COUNT(*) as total
                FROM tbl_cancellations c
                INNER JOIN tbl_bookings b ON c.booking_id = b.id
                INNER JOIN tbl_users u ON c.user_id = u.id
                WHERE c.refund_status = :status
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (\PDOException $e) {
            error_log("Error fetching refunds count: " . $e->getMessage());
            return 0;
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
     * approve a refund and update payment records
     */
    public function processRefund($refundId, $refundMethod, $adminNotes = '', $refundReference = '')
    {
        try {
            $this->pdo->beginTransaction();

            // First, get the refund details including booking_id and refund_amount
            $refundDetails = $this->getRefundDetailsForProcessing($refundId);
            if (!$refundDetails) {
                throw new \Exception('Refund not found');
            }

            // Update the cancellation record
            $sql = "
                UPDATE tbl_cancellations 
                SET 
                    refund_status = 'refunded',
                    refund_processed_at = NOW(),
                    admin_notes = :admin_notes,
                    updated_at = NOW()
                WHERE id = :refund_id AND refund_status = 'pending'
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);
            $stmt->bindParam(':admin_notes', $adminNotes, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new \Exception('Refund not found or already processed');
            }

            // update payment records for this booking
            $this->updatePaymentRecordsForRefund(
                $refundDetails['booking_id'],
                $refundDetails['refund_amount'],
                $refundReference
            );

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error processing refund: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * reject a refund and update payment records
     */
    public function rejectRefund($refundId, $rejectionReason, $rejectionNotes = '')
    {
        try {
            $this->pdo->beginTransaction();

            // First, get the refund details including booking_id
            $refundDetails = $this->getRefundDetailsForProcessing($refundId);
            if (!$refundDetails) {
                throw new \Exception('Refund not found');
            }

            // Update the cancellation record
            $adminNotes = "REJECTED - Reason: " . $rejectionReason;
            if (!empty($rejectionNotes)) {
                $adminNotes .= " | Notes: " . $rejectionNotes;
            }

            $sql = "
                UPDATE tbl_cancellations 
                SET 
                    refund_status = 'rejected',
                    admin_notes = :admin_notes,
                    updated_at = NOW()
                WHERE id = :refund_id AND refund_status = 'pending'
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);
            $stmt->bindParam(':admin_notes', $adminNotes, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new \Exception('Refund not found or already processed');
            }

            // Update payment records to restore original status (remove refund fields)
            $this->revertPaymentRecordsFromRefund($refundDetails['booking_id']);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error rejecting refund: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper method to get refund details for processing
     */
    private function getRefundDetailsForProcessing($refundId)
    {
        try {
            $sql = "
                SELECT 
                    c.booking_id,
                    c.refund_amount,
                    c.refund_status
                FROM tbl_cancellations c
                WHERE c.id = :refund_id
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching refund details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * update payment records when refund is approved
     */
    private function updatePaymentRecordsForRefund($bookingId, $refundAmount, $refundReference = '')
    {
        try {
            // update all payment records for this booking to 'Refunded' status
            $sql = "
                UPDATE tbl_payments 
                SET 
                    status = 'refunded',
                    refund_amount = :refund_amount,
                    refund_date = NOW(),
                    refund_reference = :refund_reference,
                    updated_at = NOW()
                WHERE booking_id = :booking_id 
                AND status IN ('Paid', 'Partial')
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':booking_id', $bookingId, \PDO::PARAM_INT);
            $stmt->bindParam(':refund_amount', $refundAmount, \PDO::PARAM_STR);
            $stmt->bindParam(':refund_reference', $refundReference, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error updating payment records for refund: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * revert payment records when refund is rejected
     */
    private function revertPaymentRecordsFromRefund($bookingId)
    {
        try {
            // assuming payments were 'Paid' or 'Partial' before refund request
            $sql = "
                UPDATE tbl_payments 
                SET 
                    status = CASE 
                        WHEN amount_paid < balance THEN 'partial'
                        ELSE 'paid'
                    END,
                    refund_amount = NULL,
                    refund_date = NULL,
                    refund_reference = NULL,
                    updated_at = NOW()
                WHERE booking_id = :booking_id
                AND (refund_amount IS NOT NULL OR refund_date IS NOT NULL)
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':booking_id', $bookingId, \PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            error_log("Error reverting payment records from refund: " . $e->getMessage());
            throw $e;
        }
    }
}