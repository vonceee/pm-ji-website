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
     * Get booking summary report
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
                p.status,
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
                COALESCE(SUM(p.amount_paid), 0) as total_revenue
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
     * Get revenue report
     */
    public function getRevenueReport($startDate, $endDate, $eventType = '')
    {
        // Revenue by event type
        $sql = "
            SELECT 
                b.event_type,
                COUNT(b.booking_id) as booking_count,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue,
                AVG(p.amount_paid) as avg_revenue_per_booking
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
        $revenueByType = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Monthly revenue trend
        $trendSql = "
            SELECT 
                DATE_FORMAT(b.reservation_date, '%Y-%m') as month,
                COUNT(b.booking_id) as bookings,
                COALESCE(SUM(p.amount_paid), 0) as revenue
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN DATE_SUB(:start_date, INTERVAL 6 MONTH) AND :end_date
            GROUP BY DATE_FORMAT(b.reservation_date, '%Y-%m')
            ORDER BY month
        ";

        $stmt = $this->pdo->prepare($trendSql);
        $stmt->execute($params);
        $revenueTrend = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Payment method breakdown
        $paymentSql = "
            SELECT 
                p.payment_method,
                COUNT(*) as transaction_count,
                SUM(p.amount_paid) as total_amount
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.reference_id = b.reference_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
            AND p.payment_status = 'paid'
            GROUP BY p.payment_method
            ORDER BY total_amount DESC
        ";

        $stmt = $this->pdo->prepare($paymentSql);
        $stmt->execute($params);
        $paymentMethods = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'revenue_by_type' => $revenueByType,
            'revenue_trend' => $revenueTrend,
            'payment_methods' => $paymentMethods,
            'summary' => $this->getRevenueSummary($startDate, $endDate, $eventType)
        ];
    }

    /**
     * Get revenue summary
     */
    private function getRevenueSummary($startDate, $endDate, $eventType = '')
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT p.payment_id) as total_transactions,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue,
                AVG(p.amount_paid) as avg_transaction_amount,
                SUM(CASE WHEN p.payment_status = 'paid' THEN p.amount_paid ELSE 0 END) as paid_amount,
                SUM(CASE WHEN p.payment_status = 'pending' THEN p.amount_paid ELSE 0 END) as pending_amount,
                SUM(CASE WHEN p.payment_status = 'partial' THEN p.amount_paid ELSE 0 END) as partial_amount
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.id = b.booking_id
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
     * Get payment report
     */
    public function getPaymentReport($startDate, $endDate, $paymentStatus = '')
    {
        $sql = "
            SELECT 
                p.payment_id,
                p.reference_id,
                b.event_type,
                b.reservation_date,
                p.amount_paid,
                p.payment_status,
                p.payment_method,
                p.payment_date,
                p.transaction_id,
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                u.email as client_email
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.booking_id = b.id
            JOIN users u ON b.user_id = u.user_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($paymentStatus)) {
            $sql .= " AND p.payment_status = :payment_status";
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
     * Get payment summary
     */
    private function getPaymentSummary($startDate, $endDate, $paymentStatus = '')
    {
        $sql = "
            SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN p.payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN p.payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN p.payment_status = 'partial' THEN 1 ELSE 0 END) as partial_count,
                SUM(CASE WHEN p.payment_status = 'refunded' THEN 1 ELSE 0 END) as refunded_count,
                COALESCE(SUM(p.amount_paid), 0) as total_amount,
                SUM(CASE WHEN p.payment_status = 'paid' THEN p.amount_paid ELSE 0 END) as paid_amount,
                SUM(CASE WHEN p.payment_status = 'pending' THEN p.amount_paid ELSE 0 END) as pending_amount
            FROM tbl_payments p
            JOIN tbl_bookings b ON p.booking_id = b.id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
        ";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if (!empty($paymentStatus)) {
            $sql .= " AND p.payment_status = :payment_status";
            $params['payment_status'] = $paymentStatus;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get event analysis report
     */
    public function getEventAnalysisReport($startDate, $endDate)
    {
        $sql = "
            SELECT 
                b.event_type,
                COUNT(b.booking_id) as total_bookings,
                SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                ROUND((SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) / COUNT(b.booking_id)) * 100, 2) as completion_rate,
                ROUND((SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) / COUNT(b.booking_id)) * 100, 2) as cancellation_rate,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue,
                COALESCE(AVG(p.amount_paid), 0) as avg_revenue_per_booking
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.reservation_date BETWEEN :start_date AND :end_date
            GROUP BY b.event_type
            ORDER BY total_bookings DESC
        ";

        $stmt = $this->pdo->prepare([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get available event types
     */
    public function getEventTypes()
    {
        $sql = "SELECT DISTINCT event_type FROM tbl_bookings ORDER BY event_type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}