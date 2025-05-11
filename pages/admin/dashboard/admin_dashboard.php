<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}
$admin_username = $_SESSION['admin_username'];

// database connection
require_once $_SERVER['DOCUMENT_ROOT']
    . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// fetch the shared PDO instance
$pdo = Database::getConnection();

// dashboard service class model
require_once $_SERVER['DOCUMENT_ROOT']
    . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/DashboardStats.php';

// create a DashboardStats class instance
$stats = new \Models\DashboardStats($pdo);

// fetch stats
$upcomingCount = $stats->upcomingBookings(7);
$pendingCount = $stats->pendingApprovals();
$thisMonthRevenue = $stats->revenueForMonth(date('Y'), date('m'));
$lastMonthRevenue = $stats->revenueForMonth(date('Y', strtotime('-1 month')), date('m', strtotime('-1 month')));

// header, navbar
require_once $_SERVER['DOCUMENT_ROOT']
    . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/components/admin_header.php';
require_once $_SERVER['DOCUMENT_ROOT']
    . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/components/admin_navbar.php';

/*
// 1. Total Upcoming Bookings (confirmed, next 7 days)
$upcomingSql = "SELECT COUNT(*) FROM tbl_bookings WHERE status = 'approved' AND reservation_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
$upcomingCount = $conn->query($upcomingSql)->fetch_row()[0];

// 2. Pending Approvals
$pendingSql = "SELECT COUNT(*) FROM tbl_bookings WHERE status = 'pending'";
$pendingCount = $conn->query($pendingSql)->fetch_row()[0];

// 3. Revenue Snapshot
// This Month
$thisMonthRevenueSql = "SELECT SUM(CASE WHEN payment_status='paid' THEN duration*1000 ELSE 0 END) FROM tbl_bookings WHERE MONTH(reservation_date) = MONTH(CURDATE()) AND YEAR(reservation_date) = YEAR(CURDATE())";
$thisMonthRevenue = $conn->query($thisMonthRevenueSql)->fetch_row()[0] ?? 0;

// Last Month
$lastMonthRevenueSql = "SELECT SUM(CASE WHEN payment_status='paid' THEN duration*1000 ELSE 0 END) FROM tbl_bookings WHERE MONTH(reservation_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(reservation_date) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
$lastMonthRevenue = $conn->query($lastMonthRevenueSql)->fetch_row()[0] ?? 0;

// Year-to-date
$ytdRevenueSql = "SELECT SUM(CASE WHEN payment_status='paid' THEN duration*1000 ELSE 0 END) FROM tbl_bookings WHERE YEAR(reservation_date) = YEAR(CURDATE())";
$ytdRevenue = $conn->query($ytdRevenueSql)->fetch_row()[0] ?? 0;

// Refunds/Cancellations this month
$refundSql = "SELECT COUNT(*) FROM tbl_bookings WHERE (status='cancelled_by_user' OR status='rejected') AND MONTH(reservation_date) = MONTH(CURDATE()) AND YEAR(reservation_date) = YEAR(CURDATE())";
$refundCount = $conn->query($refundSql)->fetch_row()[0];

// 4. Alerts & Tasks (simulate for now)
$latePaymentsSql = "SELECT COUNT(*) FROM tbl_bookings WHERE payment_status='pending' AND reservation_date < CURDATE()";
$latePayments = $conn->query($latePaymentsSql)->fetch_row()[0];

// Simulate contracts pending and assignments unconfirmed
$contractsPending = 2; // Placeholder
$assignmentsUnconfirmed = 1; // Placeholder

*/

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- End Bootstrap CSS -->

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/admin_dashboard.css">
    <!-- End Custom CSS -->

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <!-- End Font Awesome Icons -->
</head>

<body>
    <main class="content-container">
        <div class="main-content-wrapper">
            <div class="main-content">
                <?php require_once $_SERVER['DOCUMENT_ROOT']
                    . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/index.php'; ?>
            </div>
        </div>
    </main>
</body>

</html>