<?php
session_start();
// Check if admin is logged in; if not, redirect to the login page.
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}
$admin_username = $_SESSION['admin_username'];

// Database connection (adjust as needed)
$host = "localhost";
$user = "root";
$password = "";
$database = "db_pmji";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/admin_dashboard.css">
</head>

<body>
    <?php include 'components/admin_header.php'; ?>

    <div class="dashboard-container">
        <?php include 'components/admin_sidebar.php'; ?>

        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo htmlspecialchars($admin_username); ?>!</h1>
            </header>

            <!-- At-a-Glance Overview -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h2><?php echo $upcomingCount; ?></h2>
                    <div class="desc">Upcoming Bookings <br>(7 days)</div>
                    <a href="/NEW-PM-JI-RESERVIFY/pages/admin/calendar.php">View Calendar</a>
                </div>
                <div class="dashboard-card">
                    <h2><?php echo $pendingCount; ?></h2>
                    <div class="desc">Pending Approvals</div>
                    <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/">Go to Bookings</a>
                </div>
                <div class="dashboard-card">
                    <h2>₱<?php echo number_format($thisMonthRevenue, 2); ?></h2>
                    <div class="desc">Revenue (This Month)</div>
                </div>
                <div class="dashboard-card">
                    <h2>₱<?php echo number_format($lastMonthRevenue, 2); ?></h2>
                    <div class="desc">Revenue (Last Month)</div>
                </div>
            </div>

            <!-- Revenue Snapshot -->
            <div class="dashboard-section">
                <h4>Revenue Snapshot</h4>
                <ul>
                    <li><strong>This Month:</strong> ₱<?php echo number_format($thisMonthRevenue, 2); ?></li>
                    <li><strong>Last Month:</strong> ₱<?php echo number_format($lastMonthRevenue, 2); ?></li>
                    <li><strong>Year-to-date:</strong> ₱<?php echo number_format($ytdRevenue, 2); ?></li>
                    <li><strong>Refunds/Cancellations (This Month):</strong> <?php echo $refundCount; ?></li>
                </ul>
            </div>

            <!-- Alerts & Tasks -->
            <div class="dashboard-section dashboard-alerts">
                <h4>Alerts & Tasks</h4>
                <ul>
                    <li><strong>Late Payments:</strong> <?php echo $latePayments; ?></li>
                    <li><strong>Contracts Pending Signature:</strong> <?php echo $contractsPending; ?></li>
                    <li><strong>Photographer Assignments Unconfirmed:</strong> <?php echo $assignmentsUnconfirmed; ?></li>
                </ul>
            </div>
        </main>
    </div><!-- End Dashboard Container -->

    <!-- Bootstrap JS (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>