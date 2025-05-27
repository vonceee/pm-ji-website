<?php
// pages/admin/dashboard/models/ReportGenerator.php

namespace Models;

class ReportGenerator
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * get booking summary report
     */
    public function getBookingSummaryReport($startDate, $endDate, $status = '', $eventType = '')
    {
        $sql = "
            SELECT 
                b.reference_id,
                b.event_type,
                b.reservation_date,
                b.start_time,
                b.end_time,
                b.status,
                b.city,
                b.barangay,
                b.street_address,
                p.amount_paid,
                p.status as payment_status,
                p.payment_method,
                p.payment_date,
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                u.email as client_email,
                u.contact_no as client_phone
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            LEFT JOIN tbl_users u ON b.user_id = u.id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($status)) {
            $sql .= " AND b.status = :status";
            $params['status'] = $status;
        }

        if (!empty($eventType)) {
            $sql .= " AND b.event_type = :event_type";
            $params['event_type'] = $eventType;
        }

        $sql .= " ORDER BY b.reservation_date DESC, b.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'bookings' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'summary' => $this->getBookingSummaryStats($startDate, $endDate, $status, $eventType)
        ];
    }

    /**
     * Get booking summary statistics
     */
    private function getBookingSummaryStats($startDate, $endDate, $status = '', $eventType = '')
    {
        $sql = "
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN b.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
                SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN p.status = 'pending' OR p.status IS NULL THEN COALESCE(p.amount_paid, 0) ELSE 0 END), 0) as pending_amount
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($status)) {
            $sql .= " AND b.status = :status";
            $params['status'] = $status;
        }

        if (!empty($eventType)) {
            $sql .= " AND b.event_type = :event_type";
            $params['event_type'] = $eventType;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * get revenue report with payment transactions
     */
    public function getRevenueReport($startDate, $endDate, $eventType = '')
    {
        // Revenue data with all payment transactions
        $transactionsSql = "
            SELECT 
                p.id,
                p.booking_id as payment_reference,
                b.reference_id,
                b.event_type,
                b.reservation_date as event_date,
                p.amount_paid as amount,
                p.status as payment_status,
                p.payment_method,
                p.payment_date,
                b.reference_number,
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                u.email as client_email
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.booking_id = b.id
            JOIN tbl_users u ON b.user_id = u.id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($eventType)) {
            $transactionsSql .= " AND b.event_type = :event_type";
            $params['event_type'] = $eventType;
        }

        $transactionsSql .= " ORDER BY p.payment_date DESC";

        $stmt = $this->pdo->prepare($transactionsSql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Revenue by event type
        $sql = "
            SELECT 
                b.event_type,
                COUNT(b.id) as booking_count,
                COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END), 0) as total_revenue,
                COALESCE(AVG(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE NULL END), 0) as avg_revenue_per_booking,
                COALESCE(SUM(CASE WHEN p.status = 'pending' OR p.status IS NULL THEN COALESCE(p.amount_paid, 0) ELSE 0 END), 0) as pending_revenue
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        if (!empty($eventType)) {
            $sql .= " AND b.event_type = :event_type";
        }

        $sql .= " GROUP BY b.event_type ORDER BY total_revenue DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $revenueByType = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Monthly revenue trend
        $trendSql = "
            SELECT 
                DATE_FORMAT(b.reservation_date, '%Y-%m') as month,
                DATE_FORMAT(b.reservation_date, '%M %Y') as month_year,
                COUNT(b.id) as bookings,
                COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END), 0) as revenue,
                COALESCE(SUM(CASE WHEN p.status = 'pending' OR p.status IS NULL THEN COALESCE(p.amount_paid, 0) ELSE 0 END), 0) as pending_amount
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN DATE_SUB(:start_date, INTERVAL 6 MONTH) AND :end_date
            GROUP BY DATE_FORMAT(b.reservation_date, '%Y-%m')
            ORDER BY month
        ";

        $stmt = $this->pdo->prepare($trendSql);
        $stmt->execute($params);
        $revenueTrend = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // payment method breakdown
        $paymentSql = "
            SELECT 
                p.payment_method,
                COUNT(*) as transaction_count,
                SUM(p.amount_paid) as total_amount,
                ROUND((SUM(p.amount_paid) / (SELECT SUM(amount_paid) FROM tbl_payments p2 JOIN tbl_bookings b2 ON p2.booking_id = b2.id WHERE b2.reservation_date BETWEEN :start_date AND :end_date AND p2.status = 'paid')) * 100, 2) as percentage
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.booking_id = b.id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
            AND p.status = 'paid'
            GROUP BY p.payment_method
            ORDER BY total_amount DESC
        ";

        $stmt = $this->pdo->prepare($paymentSql);
        $stmt->execute($params);
        $paymentMethods = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'revenue_data' => $transactions,
            'revenue_by_type' => $revenueByType,
            'revenue_trend' => $revenueTrend,
            'payment_methods' => $paymentMethods,
            'monthly_breakdown' => $this->getMonthlyBreakdown($startDate, $endDate, $eventType),
            'event_type_revenue' => $this->getEventTypeRevenue($startDate, $endDate, $eventType),
            'payment_method_stats' => $this->getPaymentMethodStats($startDate, $endDate, $eventType),
            'summary' => $this->getRevenueSummary($startDate, $endDate, $eventType)
        ];
    }

    /**
     * Get monthly breakdown for revenue report
     */
    private function getMonthlyBreakdown($startDate, $endDate, $eventType = '')
    {
        $sql = "
            SELECT 
                DATE_FORMAT(b.reservation_date, '%M %Y') as month_year,
                COUNT(b.id) as booking_count,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END), 0) as collected_amount,
                COALESCE(SUM(CASE WHEN p.status = 'pending' OR p.status IS NULL THEN COALESCE(p.amount_paid, 0) ELSE 0 END), 0) as pending_amount,
                COALESCE(AVG(p.amount_paid), 0) as avg_booking_value
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($eventType)) {
            $sql .= " AND b.event_type = :event_type";
            $params['event_type'] = $eventType;
        }

        $sql .= " GROUP BY DATE_FORMAT(b.reservation_date, '%Y-%m') ORDER BY DATE_FORMAT(b.reservation_date, '%Y-%m')";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get event type revenue breakdown
     */
    private function getEventTypeRevenue($startDate, $endDate, $eventType = '')
    {
        $sql = "
            SELECT 
                b.event_type,
                '' as description,
                COUNT(b.id) as booking_count,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue,
                COALESCE(AVG(p.amount_paid), 0) as avg_price,
                COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END), 0) as collected_amount,
                ROUND((COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END), 0) / NULLIF(COALESCE(SUM(p.amount_paid), 0), 0)) * 100, 2) as collection_rate,
                0 as revenue_percentage
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($eventType)) {
            $sql .= " AND b.event_type = :event_type";
            $params['event_type'] = $eventType;
        }

        $sql .= " GROUP BY b.event_type ORDER BY total_revenue DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate revenue percentage
        $totalRevenue = array_sum(array_column($results, 'total_revenue'));
        foreach ($results as &$result) {
            $result['revenue_percentage'] = $totalRevenue > 0 
                ? round(($result['total_revenue'] / $totalRevenue) * 100, 2) 
                : 0;
        }

        return $results;
    }

    /**
     * Get payment method statistics
     */
    private function getPaymentMethodStats($startDate, $endDate, $eventType = '')
    {
        $sql = "
            SELECT 
                p.payment_method,
                COUNT(*) as transaction_count,
                SUM(p.amount_paid) as total_amount,
                0 as percentage,
                CASE 
                    WHEN p.payment_method = 'credit_card' THEN 'fa-credit-card'
                    WHEN p.payment_method = 'bank_transfer' THEN 'fa-university'
                    WHEN p.payment_method = 'cash' THEN 'fa-money-bill'
                    WHEN p.payment_method = 'gcash' THEN 'fa-mobile-alt'
                    ELSE 'fa-money-bill'
                END as icon
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.booking_id = b.id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
            AND p.status = 'paid'
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($eventType)) {
            $sql .= " AND b.event_type = :event_type";
            $params['event_type'] = $eventType;
        }

        $sql .= " GROUP BY p.payment_method ORDER BY total_amount DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate percentages
        $totalAmount = array_sum(array_column($results, 'total_amount'));
        foreach ($results as &$result) {
            $result['percentage'] = $totalAmount > 0 
                ? round(($result['total_amount'] / $totalAmount) * 100, 2) 
                : 0;
        }

        return $results;
    }

    /**
     * get revenue summary
     */
    private function getRevenueSummary($startDate, $endDate, $eventType = '')
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT p.id) as total_transactions,
                COUNT(DISTINCT b.id) as total_bookings,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue,
                COALESCE(AVG(p.amount_paid), 0) as avg_transaction_amount,
                COALESCE(AVG(p.amount_paid), 0) as average_booking_value,
                COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END), 0) as paid_amount,
                COALESCE(SUM(CASE WHEN p.status = 'pending' THEN p.amount_paid ELSE 0 END), 0) as pending_amount,
                COALESCE(SUM(CASE WHEN p.status = 'partial' THEN p.amount_paid ELSE 0 END), 0) as partial_amount,
                COUNT(CASE WHEN p.status = 'pending' THEN 1 END) as pending_bookings
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($eventType)) {
            $sql .= " AND b.event_type = :event_type";
            $params['event_type'] = $eventType;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * get payment report
     */
    public function getPaymentReport($startDate, $endDate, $paymentStatus = '')
    {
        $sql = "
            SELECT 
                p.payment_id,
                p.reference_id,
                b.reference_id as booking_reference,
                b.event_type,
                b.reservation_date,
                p.amount_paid,
                p.status as payment_status,
                p.payment_method,
                p.payment_date,
                p.transaction_id,
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                u.email as client_email
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.booking_id = b.id
            JOIN tbl_users u ON b.user_id = u.id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($paymentStatus)) {
            $sql .= " AND p.status = :payment_status";
            $params['payment_status'] = $paymentStatus;
        }

        $sql .= " ORDER BY p.payment_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'payments' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'summary' => $this->getPaymentSummary($startDate, $endDate, $paymentStatus)
        ];
    }

    /**
     * get payment summary
     */
    private function getPaymentSummary($startDate, $endDate, $paymentStatus = '')
    {
        $sql = "
            SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN p.status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN p.status = 'partial' THEN 1 ELSE 0 END) as partial_count,
                SUM(CASE WHEN p.status = 'refunded' THEN 1 ELSE 0 END) as refunded_count,
                COALESCE(SUM(p.amount_paid), 0) as total_amount,
                SUM(CASE WHEN p.status = 'paid' THEN p.amount_paid ELSE 0 END) as paid_amount,
                SUM(CASE WHEN p.status = 'pending' THEN p.amount_paid ELSE 0 END) as pending_amount
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.booking_id = b.id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($paymentStatus)) {
            $sql .= " AND p.status = :payment_status";
            $params['payment_status'] = $paymentStatus;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * get event analysis report
     */
    public function getEventAnalysisReport($startDate, $endDate)
    {
        $sql = "
            SELECT 
                b.event_type,
                COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                ROUND((SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) / COUNT(b.id)) * 100, 2) as completion_rate,
                ROUND((SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) / COUNT(b.id)) * 100, 2) as cancellation_rate,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue,
                COALESCE(AVG(p.amount_paid), 0) as avg_revenue_per_booking
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
            GROUP BY b.event_type
            ORDER BY total_bookings DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get available event types
     */
    public function getEventTypes()
    {
        $sql = "SELECT DISTINCT event_type FROM tbl_bookings ORDER BY event_type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}