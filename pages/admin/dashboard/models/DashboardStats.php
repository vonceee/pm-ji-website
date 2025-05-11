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

    public function upcomingBookings(int $days = 7): int
    {
        $sql = "
          SELECT COUNT(*) 
          FROM tbl_bookings 
          WHERE status = 'approved'
            AND reservation_date
              BETWEEN CURDATE()
                  AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['days' => $days]);
        return (int) $stmt->fetchColumn();
    }

    public function pendingApprovals(): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_bookings WHERE status = 'pending'";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    public function revenueForMonth(int $year, int $month): float
    {
        $sql = "
          SELECT SUM(duration * 1000)
          FROM tbl_bookings
          WHERE payment_status = 'paid'
            AND YEAR(reservation_date)  = :year
            AND MONTH(reservation_date) = :month
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'year' => $year,
            'month' => $month
        ]);
        $sum = $stmt->fetchColumn();
        return $sum !== null ? (float) $sum : 0.0;
    }

    // …other methods…
}
