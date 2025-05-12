<?php

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

// get date range from GET params or use default (this month)
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

// fetch stats
$totalCount = $stats->totalAppointments($start, $end);
$approvedCount = $stats->approvedAppointments($start, $end);
$pendingCount = $stats->pendingApprovals($start, $end);
$thisMonthRevenue = $stats->revenueForMonth(date('Y'), date('m'));
$lastMonthRevenue = $stats->revenueForMonth(date('Y', strtotime('-1 month')), date('m', strtotime('-1 month')));

?>

<head>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/index.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <header>
        <div class="dashboard-header">
            <h4>Dashboard</h4>
            <form id="dateRangeForm">
                <div class="date-range-input-wrapper">
                    <input type="text" id="dateRange" name="dateRange" class="form-control" autocomplete="off" />
                    <span class="calendar-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                </div>
            </form>
        </div>
    </header>

    <script>
        $(function () {
            // set default range: this month
            let start = moment("<?= $start ?>");
            let end = moment("<?= $end ?>");

            function cb(start, end) {
                // Format: May 05, 2025 - May 12, 2025
                $('#dateRange').val(start.format('MMM DD, YYYY') + ' - ' + end.format('MMM DD, YYYY'));
            }

            $('#dateRange').daterangepicker({
                startDate: start,
                endDate: end,
                locale: { format: 'MMM DD, YYYY' }
            }, cb);

            cb(start, end);

            $('#dateRangeForm').on('submit', function (e) {
                e.preventDefault();
                // reload dashboard with date range as GET params
                const range = $('#dateRange').val().split(' - ');
                if (range.length === 2) {
                    // Convert to YYYY-MM-DD for backend
                    const startDate = moment(range[0], 'MMM DD, YYYY').format('YYYY-MM-DD');
                    const endDate = moment(range[1], 'MMM DD, YYYY').format('YYYY-MM-DD');
                    window.location.search = `?start=${startDate}&end=${endDate}`;
                }
            });
        });
    </script>

    <!-- At-a-Glance Overview -->
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <h2><?= $totalCount ?></h2>
            <div class="desc">Total Appointments</div>
        </div>
        <div class="dashboard-card">
            <h2><?= $approvedCount ?></h2>
            <div class="desc">Approved Appointments</div>
        </div>
        <div class="dashboard-card">
            <h2><?= $pendingCount ?></h2>
            <div class="desc">Pending Appointments</div>
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
            <li><strong>Photographer Assignments Unconfirmed:</strong>
                <?php echo $assignmentsUnconfirmed; ?></li>
        </ul>
    </div>