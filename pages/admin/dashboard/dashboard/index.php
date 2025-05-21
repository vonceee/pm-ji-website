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
$Revenue = $stats->revenueForRange($start, $end);
$thisMonthRevenue = $stats->revenueForMonth(date('Y'), date('m'));
$lastMonthRevenue = $stats->revenueForMonth(date('Y', strtotime('-1 month')), date('m', strtotime('-1 month')));

?>

<head>
    <!-- Custome CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/index.css" />
    <!-- End Custom CSS -->

    <!-- Date Time Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <!-- End Date Time Picker -->

    <!-- Date Range Picker -->
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
    <!-- End Date Range Picker -->

    <!-- Date Range Picker Script -->
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
                    // convert to YYYY-MM-DD for backend
                    const startDate = moment(range[0], 'MMM DD, YYYY').format('YYYY-MM-DD');
                    const endDate = moment(range[1], 'MMM DD, YYYY').format('YYYY-MM-DD');
                    window.location.search = `?start=${startDate}&end=${endDate}`;
                }
            });
        });
    </script>
    <!-- End Date Range Picker Script -->

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <h2><?= $totalCount ?></h2>
            <div class="desc">Total Appointments</div>
        </div>
        <div class="dashboard-card">
            <h2 class="text-success"><?= $approvedCount ?></h2>
            <div class="desc">Approved Appointments</div>
        </div>
        <div class="dashboard-card">
            <h2 class="text-warning"><?= $pendingCount ?></h2>
            <div class="desc">Pending Appointments</div>
        </div>
        <div class="dashboard-card">
            <h2 class="text-primary">₱<?= number_format($Revenue, 2) ?></h2>
            <div class="desc">Revenue</div>
        </div>
    </div>
    <!-- End Dashboard Cards -->

    <!-- Date Range Picker -->
    <header>
        <div class="dashboard-header">
            <h4>Upcoming Appointments</h4>
        </div>
    </header>
    <!-- End Date Range Picker -->