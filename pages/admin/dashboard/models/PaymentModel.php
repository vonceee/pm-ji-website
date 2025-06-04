<?php

namespace Models;

class PaymentModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * get all outstanding payments with booking details
     */
    public function getOutstandingPayments()
    {
        $sql = "SELECT 
                    p.id as payment_id,
                    p.booking_id,
                    p.amount_paid,
                    p.balance,
                    p.payment_method,
                    p.payment_type,
                    p.status as payment_status,
                    p.payment_date,
                    p.created_at,
                    b.reference_id,
                    b.event_type,
                    b.reservation_date,
                    b.start_time,
                    b.end_time,
                    b.city,
                    b.status as booking_status,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.contact_no as phone_number,
                    u.email as email_address
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users u ON b.user_id = u.id
                WHERE p.balance > 0 AND p.status IN ('pending', 'partial')
                ORDER BY b.reservation_date ASC, p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get payment details by payment ID
     */
    public function getPaymentById($paymentId)
    {
        $sql = "SELECT 
                    p.*,
                    b.reference_id,
                    b.event_type,
                    b.reservation_date,
                    b.start_time,
                    b.end_time,
                    b.city,
                    b.status as booking_status,
                    CONCAT(c.first_name, ' ', c.last_name) as client_name,
                    c.contact_no as phone_number,
                    c.email as email_address
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users c ON b.user_id = c.id
                WHERE p.id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$paymentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * mark payment as fully paid
     */
    public function markAsPaid($paymentId)
    {
        try {
            $this->pdo->beginTransaction();

            // Get current payment details
            $payment = $this->getPaymentById($paymentId);
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            // calculate new amount paid (add balance to existing amount)
            $newAmountPaid = $payment['amount_paid'] + $payment['balance'];

            // update payment record
            $sql = "UPDATE tbl_payments 
                    SET amount_paid = ?, 
                        balance = 0, 
                        status = 'Paid',
                        payment_date = CURDATE(),
                        updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$newAmountPaid, $paymentId]);

            if (!$result) {
                throw new \Exception('failed to update payment');
            }

            // log the payment action (optional - create a payment_logs table if needed)
            $this->logPaymentAction($paymentId, 'marked_paid');

            $this->pdo->commit();
            return true;

        } catch (\Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * update partial payment
     */
    public function updatePartialPayment($paymentId, $additionalAmount, $paymentMethod = 'cash', $notes = '')
    {
        try {
            $this->pdo->beginTransaction();

            // get current payment details
            $payment = $this->getPaymentById($paymentId);
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            if ($additionalAmount <= 0 || $additionalAmount > $payment['balance']) {
                throw new \Exception('Invalid payment amount');
            }

            // calculate new amounts
            $newAmountPaid = $payment['amount_paid'] + $additionalAmount;
            $newBalance = $payment['balance'] - $additionalAmount;
            $newStatus = $newBalance > 0 ? 'partial' : 'paid';

            // update payment record
            $sql = "UPDATE tbl_payments 
                    SET amount_paid = ?, 
                        balance = ?, 
                        status = ?,
                        payment_method = ?,
                        payment_date = CURDATE(),
                        updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$newAmountPaid, $newBalance, $newStatus, $paymentMethod, $paymentId]);

            if (!$result) {
                throw new \Exception('Failed to update payment');
            }

            // log the payment action
            $this->logPaymentAction($paymentId, 'partial_payment', $notes . " - Amount: ₱" . number_format($additionalAmount, 2));

            $this->pdo->commit();
            return true;

        } catch (\Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * get all payments history with pagination
     */
    public function getAllPaymentsHistory($limit = 20, $offset = 0, $filters = [])
    {
        $whereConditions = [];
        $params = [];

        // Apply filters if provided
        if (!empty($filters['status'])) {
            $whereConditions[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(p.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(p.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['payment_method'])) {
            $whereConditions[] = "p.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(b.reference_id LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR b.event_type LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "SELECT 
                    p.id as payment_id,
                    p.booking_id,
                    p.amount_paid,
                    p.balance,
                    p.payment_method,
                    p.payment_type,
                    p.status as payment_status,
                    p.payment_date,
                    p.created_at,
                    p.updated_at,
                    (p.amount_paid + p.balance) as total_amount,
                    b.reference_id,
                    b.event_type,
                    b.reservation_date,
                    b.start_time,
                    b.end_time,
                    b.city,
                    b.status as booking_status,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.contact_no as phone_number,
                    u.email as email_address
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users u ON b.user_id = u.id
                {$whereClause}
                ORDER BY p.created_at DESC, p.updated_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get total count of payments for pagination
     */
    public function getTotalPaymentsCount($filters = [])
    {
        $whereConditions = [];
        $params = [];

        // Apply same filters as in getAllPaymentsHistory
        if (!empty($filters['status'])) {
            $whereConditions[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(p.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(p.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['payment_method'])) {
            $whereConditions[] = "p.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(b.reference_id LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR b.event_type LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "SELECT COUNT(*) as total_count
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users u ON b.user_id = u.id
                {$whereClause}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total_count'] ?? 0;
    }

    /**
     * get payments by date range
     */
    public function getPaymentsByDateRange($startDate, $endDate, $limit = null, $offset = 0)
    {
        $limitClause = $limit ? "LIMIT ? OFFSET ?" : "";
        $params = [$startDate, $endDate];
        
        if ($limit) {
            $params[] = $limit;
            $params[] = $offset;
        }

        $sql = "SELECT 
                    p.id as payment_id,
                    p.booking_id,
                    p.amount_paid,
                    p.balance,
                    p.payment_method,
                    p.payment_type,
                    p.status as payment_status,
                    p.payment_date,
                    p.created_at,
                    p.updated_at,
                    (p.amount_paid + p.balance) as total_amount,
                    b.reference_id,
                    b.event_type,
                    b.reservation_date,
                    b.start_time,
                    b.end_time,
                    b.city,
                    b.status as booking_status,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.contact_no as phone_number,
                    u.email as email_address
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users u ON b.user_id = u.id
                WHERE DATE(p.created_at) BETWEEN ? AND ?
                ORDER BY p.created_at DESC
                {$limitClause}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get payments by status
     */
    public function getPaymentsByStatus($status, $limit = null, $offset = 0)
    {
        $limitClause = $limit ? "LIMIT ? OFFSET ?" : "";
        $params = [$status];
        
        if ($limit) {
            $params[] = $limit;
            $params[] = $offset;
        }

        $sql = "SELECT 
                    p.id as payment_id,
                    p.booking_id,
                    p.amount_paid,
                    p.balance,
                    p.payment_method,
                    p.payment_type,
                    p.status as payment_status,
                    p.payment_date,
                    p.created_at,
                    p.updated_at,
                    (p.amount_paid + p.balance) as total_amount,
                    b.reference_id,
                    b.event_type,
                    b.reservation_date,
                    b.start_time,
                    b.end_time,
                    b.city,
                    b.status as booking_status,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.contact_no as phone_number,
                    u.email as email_address
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users u ON b.user_id = u.id
                WHERE p.status = ?
                ORDER BY p.created_at DESC
                {$limitClause}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * search payments by reference ID or client name
     */
    public function searchPayments($searchTerm, $limit = 20, $offset = 0)
    {
        $searchPattern = '%' . $searchTerm . '%';
        
        $sql = "SELECT 
                    p.id as payment_id,
                    p.booking_id,
                    p.amount_paid,
                    p.balance,
                    p.payment_method,
                    p.payment_type,
                    p.status as payment_status,
                    p.payment_date,
                    p.created_at,
                    p.updated_at,
                    (p.amount_paid + p.balance) as total_amount,
                    b.reference_id,
                    b.event_type,
                    b.reservation_date,
                    b.start_time,
                    b.end_time,
                    b.city,
                    b.status as booking_status,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.contact_no as phone_number,
                    u.email as email_address
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users u ON b.user_id = u.id
                WHERE (b.reference_id LIKE ? 
                    OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
                    OR b.event_type LIKE ?
                    OR u.contact_no LIKE ?)
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get payment statistics
     */
    public function getPaymentStats()
    {
        $stats = [];

        // total outstanding amount
        $sql = "SELECT SUM(balance) as total_outstanding FROM tbl_payments WHERE balance > 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['total_outstanding'] = $stmt->fetchColumn() ?: 0;

        // count of outstanding payments
        $sql = "SELECT COUNT(*) as count_outstanding FROM tbl_payments WHERE balance > 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['count_outstanding'] = $stmt->fetchColumn() ?: 0;

        // overdue payments (past event date)
        $sql = "SELECT COUNT(*) as overdue_count 
                FROM tbl_payments p 
                INNER JOIN tbl_bookings b ON p.booking_id = b.id 
                WHERE p.balance > 0 AND b.reservation_date < CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['overdue_count'] = $stmt->fetchColumn() ?: 0;

        // total paid amount (this month)
        $sql = "SELECT SUM(amount_paid) as monthly_revenue 
                FROM tbl_payments 
                WHERE MONTH(created_at) = MONTH(CURDATE()) 
                AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['monthly_revenue'] = $stmt->fetchColumn() ?: 0;

        // total paid amount (all time)
        $sql = "SELECT SUM(amount_paid) as total_revenue FROM tbl_payments";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

        // average payment amount
        $sql = "SELECT AVG(amount_paid) as avg_payment FROM tbl_payments WHERE amount_paid > 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['avg_payment'] = $stmt->fetchColumn() ?: 0;

        return $stats;
    }

    /**
     * get detailed payment statistics by date range
     */
    public function getPaymentStatsByDateRange($startDate, $endDate)
    {
        $stats = [];

        // Total payments in date range
        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(amount_paid) as total_amount_paid,
                    SUM(balance) as total_balance,
                    AVG(amount_paid) as avg_payment
                FROM tbl_payments 
                WHERE DATE(created_at) BETWEEN ? AND ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $stats['total_payments'] = $result['total_payments'] ?: 0;
        $stats['total_amount_paid'] = $result['total_amount_paid'] ?: 0;
        $stats['total_balance'] = $result['total_balance'] ?: 0;
        $stats['avg_payment'] = $result['avg_payment'] ?: 0;

        // Payment status breakdown
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(amount_paid) as total_amount
                FROM tbl_payments 
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY status";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $stats['status_breakdown'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Payment method breakdown
        $sql = "SELECT 
                    payment_method,
                    COUNT(*) as count,
                    SUM(amount_paid) as total_amount
                FROM tbl_payments 
                WHERE DATE(created_at) BETWEEN ? AND ? 
                AND payment_method IS NOT NULL
                GROUP BY payment_method";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $stats['method_breakdown'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * log payment actions for audit trail
     */
    private function logPaymentAction($paymentId, $action, $notes = '')
    {
        // this would require a payment_logs table - optional implementation
        // for now, we can just return true
        return true;
    }

    /**
     * get recent payment activities
     */
    public function getRecentPaymentActivities($limit = 10)
    {
        $sql = "SELECT 
                    p.id as payment_id,
                    p.booking_id,
                    p.amount_paid,
                    p.balance,
                    p.status,
                    p.updated_at,
                    b.reference_id,
                    b.event_type,
                    CONCAT(c.first_name, ' ', c.last_name) as client_name
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users c ON b.user_id = c.id
                WHERE p.updated_at IS NOT NULL
                ORDER BY p.updated_at DESC
                LIMIT ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get overdue payments
     */
    public function getOverduePayments()
    {
        $sql = "SELECT 
                    p.id as payment_id,
                    p.booking_id,
                    p.amount_paid,
                    p.balance,
                    p.payment_method,
                    p.payment_type,
                    p.status as payment_status,
                    p.payment_date,
                    p.created_at,
                    b.reference_id,
                    b.event_type,
                    b.reservation_date,
                    b.start_time,
                    b.end_time,
                    b.city,
                    b.status as booking_status,
                    CONCAT(u.first_name, ' ', u.last_name) as client_name,
                    u.contact_no as phone_number,
                    u.email as email_address,
                    DATEDIFF(CURDATE(), b.reservation_date) as days_overdue
                FROM tbl_payments p
                INNER JOIN tbl_bookings b ON p.booking_id = b.id
                INNER JOIN tbl_users u ON b.user_id = u.id
                WHERE p.balance > 0 
                AND b.reservation_date < CURDATE()
                ORDER BY days_overdue DESC, p.balance DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get payment methods summary
     */
    public function getPaymentMethodsSummary()
    {
        $sql = "SELECT 
                    payment_method,
                    COUNT(*) as transaction_count,
                    SUM(amount_paid) as total_amount,
                    AVG(amount_paid) as avg_amount
                FROM tbl_payments 
                WHERE payment_method IS NOT NULL 
                AND amount_paid > 0
                GROUP BY payment_method
                ORDER BY total_amount DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}