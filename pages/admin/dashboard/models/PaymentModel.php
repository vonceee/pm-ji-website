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
    public function markAsPaid($paymentId, $paymentMethod = 'cash', $notes = '')
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
                        status = 'paid',
                        payment_method = ?,
                        payment_date = CURDATE(),
                        updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$newAmountPaid, $paymentMethod, $paymentId]);

            if (!$result) {
                throw new \Exception('Failed to update payment');
            }

            // log the payment action (optional - create a payment_logs table if needed)
            $this->logPaymentAction($paymentId, 'marked_paid', $notes);

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
}