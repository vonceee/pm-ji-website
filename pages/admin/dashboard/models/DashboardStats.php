<?php
// models/DashboardStats.php

namespace Models;

use PDO;

class DashboardStats
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get upcoming bookings within specified days
     */
    public function upcomingBookings(int $days = 7): int
    {
        $sql = "
            SELECT COUNT(*) 
            FROM tbl_bookings 
            WHERE status IN ('approved', 'confirmed')
              AND reservation_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['days' => $days]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get pending approvals count
     */
    public function pendingApprovals($start = null, $end = null): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_bookings WHERE status = 'pending'";
        $params = [];

        if ($start && $end) {
            $sql .= " AND reservation_date BETWEEN :start AND :end";
            $params = ['start' => $start, 'end' => $end];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get revenue for specific month
     */
    public function revenueForMonth(int $year, int $month): float
    {
        $sql = "
            SELECT COALESCE(SUM(p.amount_paid), 0) - COALESCE(SUM(p.refund_amount), 0) as net_revenue
            FROM tbl_bookings b
            JOIN tbl_payments p ON b.id = p.booking_id
            WHERE p.status IN ('paid', 'partial')
              AND YEAR(p.payment_date) = :year
              AND MONTH(p.payment_date) = :month
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'year' => $year,
            'month' => $month
        ]);
        $result = $stmt->fetchColumn();
        return $result !== null ? (float) $result : 0.0;
    }

    /**
     * Get total appointments count
     */
    public function totalAppointments($start = null, $end = null): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_bookings";
        $params = [];

        if ($start && $end) {
            $sql .= " WHERE reservation_date BETWEEN :start AND :end";
            $params = ['start' => $start, 'end' => $end];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get approved appointments count
     */
    public function approvedAppointments($start = null, $end = null): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_bookings WHERE status IN ('approved', 'confirmed')";
        $params = [];

        if ($start && $end) {
            $sql .= " AND reservation_date BETWEEN :start AND :end";
            $params = ['start' => $start, 'end' => $end];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get revenue for date range
     */
    public function revenueForRange($start = null, $end = null): float
    {
        $sql = "
            SELECT COALESCE(SUM(p.amount_paid), 0) - COALESCE(SUM(p.refund_amount), 0) as net_revenue
            FROM tbl_bookings b
            JOIN tbl_payments p ON b.id = p.booking_id
            WHERE p.status IN ('paid', 'partial')
        ";
        $params = [];

        if ($start && $end) {
            $sql .= " AND p.payment_date BETWEEN :start AND :end";
            $params = ['start' => $start, 'end' => $end];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchColumn();
        return $result !== null ? (float) $result : 0.0;
    }

    /**
     * Get completed bookings count (with paid status)
     */
    public function completedBookings($start = null, $end = null): int
    {
        $sql = "
            SELECT COUNT(DISTINCT b.id)
            FROM tbl_bookings b
            JOIN tbl_payments p ON b.id = p.booking_id
            WHERE p.status IN ('paid', 'partial')
        ";
        $params = [];

        if ($start && $end) {
            $sql .= " AND p.payment_date BETWEEN :start AND :end";
            $params = ['start' => $start, 'end' => $end];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get revenue by event type
     */
    public function revenueByEventType($start = null, $end = null): array
    {
        $sql = "
            SELECT 
                b.event_type,
                COUNT(DISTINCT b.id) as booking_count,
                COALESCE(SUM(p.amount_paid), 0) as total_revenue
            FROM tbl_bookings b
            JOIN tbl_payments p ON b.id = p.booking_id
            WHERE p.status IN ('paid', 'partial')
        ";
        $params = [];

        if ($start && $end) {
            $sql .= " AND p.payment_date BETWEEN :start AND :end";
            $params = ['start' => $start, 'end' => $end];
        }

        $sql .= " GROUP BY b.event_type ORDER BY total_revenue DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent bookings
     */
    public function recentBookings(int $limit = 10): array
    {
        $sql = "
            SELECT 
                b.reference_id,
                b.event_type,
                b.reservation_date,
                b.start_time,
                b.end_time,
                b.city,
                b.status,
                p.amount_paid,
                p.status as payment_status
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            ORDER BY b.created_at DESC
            LIMIT :limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get monthly revenue comparison
     */
    public function monthlyRevenueComparison(): array
    {
        $sql = "
            SELECT 
                YEAR(p.payment_date) as year,
                MONTH(p.payment_date) as month,
                MONTHNAME(p.payment_date) as month_name,
                COALESCE(SUM(p.amount_paid), 0) - COALESCE(SUM(p.refund_amount), 0) as net_revenue,
                COUNT(DISTINCT b.id) as booking_count
            FROM tbl_bookings b
            JOIN tbl_payments p ON b.id = p.booking_id
            WHERE p.status IN ('paid', 'partial')
              AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY YEAR(p.payment_date), MONTH(p.payment_date)
            ORDER BY year DESC, month DESC
            LIMIT 12
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get outstanding payments
     */
    public function outstandingPayments(): array
    {
        $sql = "
            SELECT 
                b.reference_id,
                b.event_type,
                b.reservation_date,
                p.amount_paid,
                p.balance,
                p.status,
                DATEDIFF(CURDATE(), b.reservation_date) as days_overdue
            FROM tbl_bookings b
            JOIN tbl_payments p ON b.id = p.booking_id
            WHERE p.status IN ('pending', 'partial')
              AND b.reservation_date < CURDATE()
            ORDER BY days_overdue DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}