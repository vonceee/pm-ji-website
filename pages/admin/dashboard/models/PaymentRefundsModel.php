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
     * Get all refund payments with optional date filtering and pagination
     *
     * @param string $dateFrom Start date filter (optional)
     * @param string $dateTo End date filter (optional)
     * @param int $limit Number of records per page (optional)
     * @param int $offset Number of records to skip (optional)
     * @return array Array of refund payment records
     */
    public function getAllRefundPayments($dateFrom = '', $dateTo = '', $limit = null, $offset = null)
    {
        try {
            $sql = "SELECT 
                        rp.refund_id,
                        rp.reservation_id,
                        rp.payment_id,
                        rp.refund_amount,
                        rp.refund_reason,
                        rp.refund_status,
                        rp.refund_method,
                        rp.processed_by,
                        rp.processed_at,
                        rp.created_at,
                        rp.updated_at,
                        r.guest_name,
                        r.guest_email,
                        r.guest_phone,
                        r.check_in_date,
                        r.check_out_date,
                        r.room_type,
                        p.payment_amount as original_payment_amount,
                        p.payment_method as original_payment_method,
                        p.payment_date,
                        u.username as processed_by_username,
                        u.first_name as processor_first_name,
                        u.last_name as processor_last_name
                    FROM refund_payments rp
                    LEFT JOIN reservations r ON rp.reservation_id = r.reservation_id
                    LEFT JOIN payments p ON rp.payment_id = p.payment_id
                    LEFT JOIN users u ON rp.processed_by = u.user_id
                    WHERE 1=1";

            $params = [];

            // Add date filtering
            if (!empty($dateFrom)) {
                $sql .= " AND DATE(rp.created_at) >= :date_from";
                $params[':date_from'] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $sql .= " AND DATE(rp.created_at) <= :date_to";
                $params[':date_to'] = $dateTo;
            }

            // Order by created date (newest first)
            $sql .= " ORDER BY rp.created_at DESC";

            // Add pagination
            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$limit;

                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                    $params[':offset'] = (int)$offset;
                }
            }

            $stmt = $this->pdo->prepare($sql);

            // Bind parameters
            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error fetching refund payments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of refund payments with optional date filtering
     *
     * @param string $dateFrom Start date filter (optional)
     * @param string $dateTo End date filter (optional)
     * @return int Total count of refund payments
     */
    public function getTotalRefundPaymentsCount($dateFrom = '', $dateTo = '')
    {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM refund_payments rp
                    WHERE 1=1";

            $params = [];

            // Add date filtering
            if (!empty($dateFrom)) {
                $sql .= " AND DATE(rp.created_at) >= :date_from";
                $params[':date_from'] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $sql .= " AND DATE(rp.created_at) <= :date_to";
                $params[':date_to'] = $dateTo;
            }

            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)$result['total'];

        } catch (\PDOException $e) {
            error_log("Error counting refund payments: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get pending refund payments (status = 'pending')
     *
     * @param string $dateFrom Start date filter (optional)
     * @param string $dateTo End date filter (optional)
     * @param int $limit Number of records per page (optional)
     * @param int $offset Number of records to skip (optional)
     * @return array Array of pending refund payment records
     */
    public function getPendingRefundPayments($dateFrom = '', $dateTo = '', $limit = null, $offset = null)
    {
        try {
            $sql = "SELECT 
                        rp.refund_id,
                        rp.reservation_id,
                        rp.payment_id,
                        rp.refund_amount,
                        rp.refund_reason,
                        rp.refund_status,
                        rp.refund_method,
                        rp.processed_by,
                        rp.processed_at,
                        rp.created_at,
                        rp.updated_at,
                        r.guest_name,
                        r.guest_email,
                        r.guest_phone,
                        r.check_in_date,
                        r.check_out_date,
                        r.room_type,
                        p.payment_amount as original_payment_amount,
                        p.payment_method as original_payment_method,
                        p.payment_date
                    FROM refund_payments rp
                    LEFT JOIN reservations r ON rp.reservation_id = r.reservation_id
                    LEFT JOIN payments p ON rp.payment_id = p.payment_id
                    WHERE rp.refund_status = 'pending'";

            $params = [];

            // Add date filtering
            if (!empty($dateFrom)) {
                $sql .= " AND DATE(rp.created_at) >= :date_from";
                $params[':date_from'] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $sql .= " AND DATE(rp.created_at) <= :date_to";
                $params[':date_to'] = $dateTo;
            }

            // Order by created date (oldest first for pending items)
            $sql .= " ORDER BY rp.created_at ASC";

            // Add pagination
            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$limit;

                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                    $params[':offset'] = (int)$offset;
                }
            }

            $stmt = $this->pdo->prepare($sql);

            // Bind parameters
            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error fetching pending refund payments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get refund payment by ID
     *
     * @param int $refundId The refund ID
     * @return array|null Refund payment record or null if not found
     */
    public function getRefundPaymentById($refundId)
    {
        try {
            $sql = "SELECT 
                        rp.*,
                        r.guest_name,
                        r.guest_email,
                        r.guest_phone,
                        r.check_in_date,
                        r.check_out_date,
                        r.room_type,
                        p.payment_amount as original_payment_amount,
                        p.payment_method as original_payment_method,
                        p.payment_date,
                        u.username as processed_by_username,
                        u.first_name as processor_first_name,
                        u.last_name as processor_last_name
                    FROM refund_payments rp
                    LEFT JOIN reservations r ON rp.reservation_id = r.reservation_id
                    LEFT JOIN payments p ON rp.payment_id = p.payment_id
                    LEFT JOIN users u ON rp.processed_by = u.user_id
                    WHERE rp.refund_id = :refund_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':refund_id', $refundId, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error fetching refund payment by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new refund payment record
     *
     * @param array $data Refund payment data
     * @return int|false Refund ID if successful, false on failure
     */
    public function createRefundPayment($data)
    {
        try {
            $sql = "INSERT INTO refund_payments (
                        reservation_id, 
                        payment_id, 
                        refund_amount, 
                        refund_reason, 
                        refund_status, 
                        refund_method,
                        created_at
                    ) VALUES (
                        :reservation_id, 
                        :payment_id, 
                        :refund_amount, 
                        :refund_reason, 
                        :refund_status, 
                        :refund_method,
                        NOW()
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $result = $stmt->execute([
                ':reservation_id' => $data['reservation_id'],
                ':payment_id' => $data['payment_id'],
                ':refund_amount' => $data['refund_amount'],
                ':refund_reason' => $data['refund_reason'],
                ':refund_status' => $data['refund_status'] ?? 'pending',
                ':refund_method' => $data['refund_method'] ?? null
            ]);

            if ($result) {
                return $this->pdo->lastInsertId();
            }
            
            return false;

        } catch (\PDOException $e) {
            error_log("Error creating refund payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update refund payment status
     *
     * @param int $refundId The refund ID
     * @param string $status New status
     * @param int $processedBy User ID who processed the refund
     * @return bool True if successful, false on failure
     */
    public function updateRefundStatus($refundId, $status, $processedBy = null)
    {
        try {
            $sql = "UPDATE refund_payments 
                    SET refund_status = :status, 
                        processed_by = :processed_by,
                        processed_at = NOW(),
                        updated_at = NOW()
                    WHERE refund_id = :refund_id";

            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':status' => $status,
                ':processed_by' => $processedBy,
                ':refund_id' => $refundId
            ]);

        } catch (\PDOException $e) {
            error_log("Error updating refund status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete refund payment record
     *
     * @param int $refundId The refund ID
     * @return bool True if successful, false on failure
     */
    public function deleteRefundPayment($refundId)
    {
        try {
            $sql = "DELETE FROM refund_payments WHERE refund_id = :refund_id";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([':refund_id' => $refundId]);

        } catch (\PDOException $e) {
            error_log("Error deleting refund payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get refund statistics
     *
     * @param string $dateFrom Start date filter (optional)
     * @param string $dateTo End date filter (optional)
     * @return array Statistics array
     */
    public function getRefundStatistics($dateFrom = '', $dateTo = '')
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_refunds,
                        SUM(CASE WHEN refund_status = 'pending' THEN 1 ELSE 0 END) as pending_refunds,
                        SUM(CASE WHEN refund_status = 'completed' THEN 1 ELSE 0 END) as completed_refunds,
                        SUM(CASE WHEN refund_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_refunds,
                        SUM(refund_amount) as total_refund_amount,
                        SUM(CASE WHEN refund_status = 'completed' THEN refund_amount ELSE 0 END) as completed_refund_amount,
                        SUM(CASE WHEN refund_status = 'pending' THEN refund_amount ELSE 0 END) as pending_refund_amount
                    FROM refund_payments rp
                    WHERE 1=1";

            $params = [];

            // Add date filtering
            if (!empty($dateFrom)) {
                $sql .= " AND DATE(rp.created_at) >= :date_from";
                $params[':date_from'] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $sql .= " AND DATE(rp.created_at) <= :date_to";
                $params[':date_to'] = $dateTo;
            }

            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error fetching refund statistics: " . $e->getMessage());
            return [
                'total_refunds' => 0,
                'pending_refunds' => 0,
                'completed_refunds' => 0,
                'cancelled_refunds' => 0,
                'total_refund_amount' => 0,
                'completed_refund_amount' => 0,
                'pending_refund_amount' => 0
            ];
        }
    }
}