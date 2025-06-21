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
     * Get all refund payments with pagination
     */
    public function getAllRefundPayments($dateFrom = '', $dateTo = '', $search = '', $limit = 10, $offset = 0)
    {
        $sql = "SELECT 
                    pr.refund_id,
                    pr.booking_id,
                    pr.payment_id,
                    pr.user_id,
                    pr.refund_amount,
                    pr.refund_reason,
                    pr.refund_status,
                    pr.refund_method,
                    pr.admin_notes,
                    pr.user_notes,
                    pr.refund_reference,
                    pr.requested_at,
                    pr.processed_at,
                    pr.rejection_reason,
                    pr.rejection_notes,
                    b.reference_number,
                    b.event_name,
                    b.event_date,
                    u.first_name,
                    u.last_name,
                    u.email,
                    p.amount_due,
                    p.amount_paid
                FROM payment_refunds pr
                JOIN bookings b ON pr.booking_id = b.booking_id
                JOIN users u ON pr.user_id = u.user_id
                LEFT JOIN payments p ON pr.payment_id = p.payment_id
                WHERE 1=1";

        $params = [];

        // Add date filters
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(pr.requested_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(pr.requested_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (b.reference_number LIKE :search 
                         OR b.event_name LIKE :search 
                         OR u.first_name LIKE :search 
                         OR u.last_name LIKE :search 
                         OR u.email LIKE :search
                         OR pr.refund_reason LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY 
                    CASE pr.refund_status 
                        WHEN 'pending' THEN 1 
                        WHEN 'processed' THEN 2 
                        WHEN 'rejected' THEN 3 
                        ELSE 4 
                    END,
                    pr.requested_at DESC";
        
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of refund payments
     */
    public function getRefundPaymentsCount($dateFrom = '', $dateTo = '', $search = '')
    {
        $sql = "SELECT COUNT(*) as total
                FROM payment_refunds pr
                JOIN bookings b ON pr.booking_id = b.booking_id
                JOIN users u ON pr.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        // Add date filters
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(pr.requested_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(pr.requested_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (b.reference_number LIKE :search 
                         OR b.event_name LIKE :search 
                         OR u.first_name LIKE :search 
                         OR u.last_name LIKE :search 
                         OR u.email LIKE :search
                         OR pr.refund_reason LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    // ... existing methods remain unchanged

    public function processRefund($refundId, $refundMethod, $adminNotes = '', $refundReference = '')
    {
        $sql = "UPDATE payment_refunds SET 
                refund_status = 'processed',
                refund_method = :refund_method,
                admin_notes = :admin_notes,
                refund_reference = :refund_reference,
                processed_at = NOW()
                WHERE refund_id = :refund_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':refund_id' => $refundId,
            ':refund_method' => $refundMethod,
            ':admin_notes' => $adminNotes,
            ':refund_reference' => $refundReference
        ]);
    }

    public function rejectRefund($refundId, $rejectionReason, $rejectionNotes = '')
    {
        $sql = "UPDATE payment_refunds SET 
                refund_status = 'rejected',
                rejection_reason = :rejection_reason,
                rejection_notes = :rejection_notes,
                processed_at = NOW()
                WHERE refund_id = :refund_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':refund_id' => $refundId,
            ':rejection_reason' => $rejectionReason,
            ':rejection_notes' => $rejectionNotes
        ]);
    }

    public function getRefundById($refundId)
    {
        $sql = "SELECT 
                    pr.*,
                    b.reference_number,
                    b.event_name,
                    b.event_date,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM payment_refunds pr
                JOIN bookings b ON pr.booking_id = b.booking_id
                JOIN users u ON pr.user_id = u.user_id
                WHERE pr.refund_id = :refund_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':refund_id' => $refundId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}