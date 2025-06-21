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
     * get all outstanding payments with booking details
     */
    public function getOutstandingPayments($dateFrom = '', $dateTo = '', $statusFilter = '', $paymentMethodFilter = '', $amountRangeFilter = '')
    {
        $whereConditions = ["p.balance > 0 AND p.status IN ('pending', 'partial')"];
        $params = [];

        // Apply filters
        if (!empty($dateFrom)) {
            $whereConditions[] = "DATE(p.created_at) >= ?";
            $params[] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $whereConditions[] = "DATE(p.created_at) <= ?";
            $params[] = $dateTo;
        }

        if (!empty($statusFilter)) {
            $whereConditions[] = "p.status = ?";
            $params[] = $statusFilter;
        }

        if (!empty($paymentMethodFilter)) {
            $whereConditions[] = "p.payment_method = ?";
            $params[] = $paymentMethodFilter;
        }

        if (!empty($amountRangeFilter)) {
            switch ($amountRangeFilter) {
                case 'under_1000':
                    $whereConditions[] = "p.balance < 1000";
                    break;
                case '1000_5000':
                    $whereConditions[] = "p.balance BETWEEN 1000 AND 5000";
                    break;
                case '5000_10000':
                    $whereConditions[] = "p.balance BETWEEN 5000 AND 10000";
                    break;
                case '10000_25000':
                    $whereConditions[] = "p.balance BETWEEN 10000 AND 25000";
                    break;
                case 'over_25000':
                    $whereConditions[] = "p.balance > 25000";
                    break;
            }
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

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
                {$whereClause}
                ORDER BY b.reservation_date ASC, p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
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

            // get current payment details
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

            $this->pdo->commit();
            return true;

        } catch (\Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * get all payments history with pagination - Updated to handle both parameter styles
     */
    public function getAllPaymentsHistory($limit = 20, $offset = 0, $param3 = [], $param4 = '', $param5 = '', $param6 = '')
    {
        // Handle both old and new parameter styles
        if (is_array($param3)) {
            // New style: getAllPaymentsHistory($limit, $offset, $filters)
            $filters = $param3;
        } else {
            // Old style: getAllPaymentsHistory($limit, $offset, $dateFrom, $dateTo, $statusFilter, $paymentMethodFilter)
            $filters = [
                'date_from' => $param3,
                'date_to' => $param4,
                'status' => $param5,
                'payment_method' => $param6
            ];
        }

        $whereConditions = [];
        $params = [];

        // apply filters if provided
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
     * get total count of payments for pagination - Updated to handle both parameter styles
     */
    public function getTotalPaymentsCount($param1 = [], $param2 = '', $param3 = '', $param4 = '')
    {
        // Handle both old and new parameter styles
        if (is_array($param1)) {
            // New style: getTotalPaymentsCount($filters)
            $filters = $param1;
        } else {
            // Old style: getTotalPaymentsCount($dateFrom, $dateTo, $statusFilter, $paymentMethodFilter)
            $filters = [
                'date_from' => $param1,
                'date_to' => $param2,
                'status' => $param3,
                'payment_method' => $param4
            ];
        }

        $whereConditions = [];
        $params = [];

        // apply same filters as in getAllPaymentsHistory
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
     * Get total count of outstanding payments
     */
    public function getOutstandingPaymentsCount($dateFrom = '', $dateTo = '', $status = '', $method = '', $search = '')
    {
        $sql = "SELECT COUNT(*) as total
                FROM tbl_payments p
                JOIN tbl_bookings b ON p.booking_id = b.id
                JOIN tbl_users u ON b.user_id = u.id
                WHERE p.status IN ('pending', 'partial')";

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

}