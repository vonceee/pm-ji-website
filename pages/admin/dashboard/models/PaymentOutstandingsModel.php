<?php

namespace Models;

class PaymentOutstandingsModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get outstanding payments with pagination
     */
    public function getOutstandingPayments($dateFrom = '', $dateTo = '', $status = '', $method = '', $search = '', $limit = 10, $offset = 0)
    {
        $sql = "SELECT 
                    p.payment_id,
                    p.booking_id,
                    p.user_id,
                    p.amount_due,
                    p.amount_paid,
                    p.payment_status,
                    p.payment_method,
                    p.due_date,
                    p.created_at,
                    p.updated_at,
                    b.reference_number,
                    b.event_name,
                    b.event_date,
                    u.first_name,
                    u.last_name,
                    u.email,
                    (p.amount_due - p.amount_paid) as remaining_balance
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN users u ON p.user_id = u.user_id
                WHERE p.payment_status IN ('pending', 'partial')";

        $params = [];

        // Add date filters
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(p.created_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(p.created_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (b.reference_number LIKE :search 
                         OR b.event_name LIKE :search 
                         OR u.first_name LIKE :search 
                         OR u.last_name LIKE :search 
                         OR u.email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY p.due_date ASC, p.created_at DESC";
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
     * Get total count of outstanding payments
     */
    public function getOutstandingPaymentsCount($dateFrom = '', $dateTo = '', $status = '', $method = '', $search = '')
    {
        $sql = "SELECT COUNT(*) as total
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN users u ON p.user_id = u.user_id
                WHERE p.payment_status IN ('pending', 'partial')";

        $params = [];

        // Add date filters
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(p.created_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(p.created_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (b.reference_number LIKE :search 
                         OR b.event_name LIKE :search 
                         OR u.first_name LIKE :search 
                         OR u.last_name LIKE :search 
                         OR u.email LIKE :search)";
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

    /**
     * Get payment history with pagination
     */
    public function getAllPaymentsHistory($limit = 20, $offset = 0, $dateFrom = '', $dateTo = '', $status = '', $method = '', $search = '')
    {
        $sql = "SELECT 
                    p.payment_id,
                    p.booking_id,
                    p.user_id,
                    p.amount_due,
                    p.amount_paid,
                    p.payment_status,
                    p.payment_method,
                    p.due_date,
                    p.created_at,
                    p.updated_at,
                    b.reference_number,
                    b.event_name,
                    b.event_date,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN users u ON p.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        // Add date filters
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(p.created_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(p.created_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (b.reference_number LIKE :search 
                         OR b.event_name LIKE :search 
                         OR u.first_name LIKE :search 
                         OR u.last_name LIKE :search 
                         OR u.email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY p.updated_at DESC, p.created_at DESC";
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
     * Get total count of payment history
     */
    public function getTotalPaymentsCount($dateFrom = '', $dateTo = '', $status = '', $method = '', $search = '')
    {
        $sql = "SELECT COUNT(*) as total
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN users u ON p.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        // Add date filters
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(p.created_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(p.created_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (b.reference_number LIKE :search 
                         OR b.event_name LIKE :search 
                         OR u.first_name LIKE :search 
                         OR u.last_name LIKE :search 
                         OR u.email LIKE :search)";
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

    // ... existing methods (markAsPaid, getPaymentById, etc.) remain unchanged
    
    public function markAsPaid($paymentId)
    {
        $sql = "UPDATE payments SET 
                payment_status = 'paid', 
                amount_paid = amount_due,
                updated_at = NOW() 
                WHERE payment_id = :payment_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':payment_id' => $paymentId]);
    }

    public function getPaymentById($paymentId)
    {
        $sql = "SELECT 
                    p.*,
                    b.reference_number,
                    b.event_name,
                    b.event_date,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN users u ON p.user_id = u.user_id
                WHERE p.payment_id = :payment_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':payment_id' => $paymentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}