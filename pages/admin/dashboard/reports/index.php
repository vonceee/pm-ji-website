<?php
// \NEW-PM-JI-RESERVIFY\pages\admin\dashboard\reports\index.php

// database connection
require_once $_SERVER['DOCUMENT_ROOT']
    . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// fetch the shared PDO instance
$pdo = Database::getConnection();

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$statuses = ['pending', 'approved', 'cancelled', 'rejected', 'completed', 'no-show'];
$quickStats = [];
foreach ($statuses as $status) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_bookings WHERE status = :status AND reservation_date BETWEEN :start AND :end");
    $stmt->execute(['status' => $status, 'start' => $start, 'end' => $end]);
    $quickStats[$status] = $stmt->fetchColumn();
}

// Revenue statuses and colors
$revenueStatuses = ['paid', 'pending', 'refunded', 'partial'];
$revenueStatusColors = [
    'paid' => '#28a745',        // green
    'pending' => '#ffc107',     // yellow
    'refunded' => '#dc3545',    // red
    'partial' => '#0d6efd',     // blue
];

// Revenue Quick Stats
$revenueQuickStats = [];
foreach ($revenueStatuses as $status) {
    $stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM tbl_payments WHERE status = :status AND payment_date BETWEEN :start AND :end");
    $stmt->execute(['status' => $status, 'start' => $start, 'end' => $end]);
    $revenueQuickStats[$status] = $stmt->fetchColumn() ?: 0;
}

// Prepare chart data for each status
$statusColors = [
    'pending' => '#ffc107',    // yellow
    'approved' => '#28a745',   // green
    'cancelled' => '#6c757d',  // gray
    'rejected' => '#dc3545',   // red
    'completed' => '#0d6efd',  // blue
    'no-show' => '#212529',    // dark
];

// Get all dates in range
$period = new DatePeriod(
    new DateTime($start),
    new DateInterval('P1D'),
    (new DateTime($end))->modify('+1 day')
);
$allDates = [];
$allDatesFormatted = [];
foreach ($period as $date) {
    $allDates[] = $date->format('Y-m-d');
    $allDatesFormatted[] = $date->format('M d');
}

// Initialize chart data
$chartStatusData = [];
foreach ($statuses as $status) {
    $chartStatusData[$status] = array_fill_keys($allDates, 0);
}

// Fetch counts per day per status
$stmt = $pdo->prepare("
    SELECT reservation_date, status, COUNT(*) as total
    FROM tbl_bookings
    WHERE reservation_date BETWEEN :start AND :end
    GROUP BY reservation_date, status
    ORDER BY reservation_date
");
$stmt->execute(['start' => $start, 'end' => $end]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $chartStatusData[$row['status']][$row['reservation_date']] = (int) $row['total'];
}
    
// For "This Year" filter, show total bookings per month
if (
    (isset($_GET['filter']) && $_GET['filter'] === 'year')
    || (isset($_POST['filter']) && $_POST['filter'] === 'year')
) {
    // Overwrite $allDatesFormatted and $chartStatusData for monthly aggregation
    $allMonths = [];
    $allMonthsFormatted = [];
    for ($m = 1; $m <= 12; $m++) {
        $monthNum = str_pad($m, 2, '0', STR_PAD_LEFT);
        $allMonths[] = date('Y') . '-' . $monthNum;
        $allMonthsFormatted[] = date('M', mktime(0, 0, 0, $m, 1));
    }
    $chartStatusData = [];
    foreach ($statuses as $status) {
        $chartStatusData[$status] = array_fill_keys($allMonths, 0);
    }
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(reservation_date, '%Y-%m') as month, status, COUNT(*) as total
        FROM tbl_bookings
        WHERE reservation_date BETWEEN :start AND :end
        GROUP BY month, status
        ORDER BY month
    ");
    $stmt->execute(['start' => $start, 'end' => $end]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $chartStatusData[$row['status']][$row['month']] = (int)$row['total'];
    }
    $allDatesFormatted = $allMonthsFormatted;
}

// Fetch data for bar chart (appointments per day)
$stmt = $pdo->prepare("
    SELECT reservation_date, COUNT(*) as total
    FROM tbl_bookings
    WHERE reservation_date BETWEEN :start AND :end
    GROUP BY reservation_date
    ORDER BY reservation_date
");
$stmt->execute(['start' => $start, 'end' => $end]);
$chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$chartLabels = array_column($chartData, 'reservation_date');
$chartTotals = array_column($chartData, 'total');

// Prepare revenue data per day
$revenuePerDay = array_fill_keys($allDates, 0);
$stmt = $pdo->prepare("
    SELECT payment_date, SUM(amount_paid) as total
    FROM tbl_payments
    WHERE payment_date BETWEEN :start AND :end
    GROUP BY payment_date
    ORDER BY payment_date
");
$stmt->execute(['start' => $start, 'end' => $end]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $revenuePerDay[$row['payment_date']] = (float)$row['total'];
}
$revenueChartData = array_values($revenuePerDay);
?>

<head>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/index.css" />
</head>

<div>
    <h2><i class="fas fa-chart-bar"></i> Reports</h2>
    <div class="row mt-4">
        <!-- Side Menu -->
        <div class="col-md-3 mb-3">
            <div class="list-group" id="reportsMenu">
                <button type="button" class="list-group-item list-group-item-action active" id="appointmentsReportBtn">
                    <i class="fas fa-calendar-check"></i> Appointment Reports
                </button>
                <button type="button" class="list-group-item list-group-item-action" id="revenueReportBtn">
                    <i class="fas fa-coins"></i> Revenue Reports
                </button>
            </div>
        </div>
        <!-- Report Panels -->
        <div class="col-md-6">
            <!-- Appointment Reports Panel -->
            <div id="appointmentsReportPanel">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Appointment Reports</h5>
                        <form id="appointmentReportForm" class="row g-2 mb-2">
                            <div class="col">
                                <input type="date" name="start" id="startDate" class="form-control"
                                    value="<?= htmlspecialchars($start) ?>" required>
                            </div>
                            <div class="col">
                                <input type="date" name="end" id="endDate" class="form-control"
                                    value="<?= htmlspecialchars($end) ?>" required>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </form>
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="filterThisMonth">This Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="filterThisYear">This Year</button>
                        </div>
                        <canvas id="appointmentsBarChart" height="100"></canvas>
                        <form action="download_appointments_report.php" method="get" class="row g-2 mt-4">
                            <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                            <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-pdf"></i> Download PDF
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Revenue Reports Panel -->
            <div id="revenueReportPanel" style="display:none;">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Revenue Reports</h5>
                        <form id="revenueReportForm" class="row g-2 mb-2">
                            <div class="col">
                                <input type="date" name="start" id="revenueStartDate" class="form-control" value="<?= htmlspecialchars($start) ?>" required>
                            </div>
                            <div class="col">
                                <input type="date" name="end" id="revenueEndDate" class="form-control" value="<?= htmlspecialchars($end) ?>" required>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </form>
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="revenueFilterThisMonth">This Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="revenueFilterThisYear">This Year</button>
                        </div>
                        <canvas id="revenueLineChart" height="100"></canvas>
                        <form action="download_revenue_report.php" method="get" class="row g-2 mt-4">
                            <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                            <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-pdf"></i> Download PDF
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Stats Panel on the right -->
        <div class="col-md-3" id="quickStatsPanelCol">
            <!-- Appointment Quick Stats -->
            <div class="card" id="quickStatsPanel">
                <div class="card-header">
                    <strong>Quick Stats</strong>
                </div>
                <ul class="quick-stats-list">
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle legend-pending"></span> Pending
                        </span>
                        <span><?= $quickStats['pending'] ?? 0 ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle legend-approved"></span> Approved
                        </span>
                        <span><?= $quickStats['approved'] ?? 0 ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle legend-cancelled"></span> Cancelled
                        </span>
                        <span><?= $quickStats['cancelled'] ?? 0 ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle legend-rejected"></span> Rejected
                        </span>
                        <span><?= $quickStats['rejected'] ?? 0 ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle legend-completed"></span> Completed
                        </span>
                        <span><?= $quickStats['completed'] ?? 0 ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle legend-noshows"></span> No-Show
                        </span>
                        <span><?= $quickStats['no-show'] ?? 0 ?></span>
                    </li>
                </ul>
            </div>
            <!-- Revenue Quick Stats -->
            <div class="card" id="revenueQuickStatsPanel" style="display:none;">
                <div class="card-header">
                    <strong>Quick Stats</strong>
                </div>
                <ul class="quick-stats-list">
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle" style="background:#28a745"></span> Paid
                        </span>
                        <span>₱<?= number_format($revenueQuickStats['paid'], 2) ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle" style="background:#ffc107"></span> Pending
                        </span>
                        <span>₱<?= number_format($revenueQuickStats['pending'], 2) ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle" style="background:#dc3545"></span> Refunded
                        </span>
                        <span>₱<?= number_format($revenueQuickStats['refunded'], 2) ?></span>
                    </li>
                    <li>
                        <span class="quick-stats-label">
                            <span class="legend-circle" style="background:#0d6efd"></span> Partially Paid
                        </span>
                        <span>₱<?= number_format($revenueQuickStats['partial'], 2) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tab switching
        const appointmentsBtn = document.getElementById('appointmentsReportBtn');
        const revenueBtn = document.getElementById('revenueReportBtn');
        const appointmentsPanel = document.getElementById('appointmentsReportPanel');
        const revenuePanel = document.getElementById('revenueReportPanel');
        const quickStatsPanel = document.getElementById('quickStatsPanel');
        const revenueQuickStatsPanel = document.getElementById('revenueQuickStatsPanel');

        appointmentsBtn.addEventListener('click', function () {
            appointmentsBtn.classList.add('active');
            revenueBtn.classList.remove('active');
            appointmentsPanel.style.display = '';
            revenuePanel.style.display = 'none';
            quickStatsPanel.style.display = '';
            revenueQuickStatsPanel.style.display = 'none';
        });

        revenueBtn.addEventListener('click', function () {
            revenueBtn.classList.add('active');
            appointmentsBtn.classList.remove('active');
            revenuePanel.style.display = '';
            appointmentsPanel.style.display = 'none';
            quickStatsPanel.style.display = 'none';
            revenueQuickStatsPanel.style.display = '';
        });

        // Chart.js Bar Graph (per status)
        const ctx = document.getElementById('appointmentsBarChart').getContext('2d');
        const chartLabels = <?= json_encode($allDatesFormatted) ?>;
        const chartStatusData = <?= json_encode($chartStatusData) ?>;
        const statusColors = <?= json_encode($statusColors) ?>;

        const datasets = Object.keys(chartStatusData).map(function (status) {
            return {
                label: status.charAt(0).toUpperCase() + status.slice(1),
                data: Object.values(chartStatusData[status]),
                backgroundColor: statusColors[status],
                stack: 'Status'
            };
        });

        const appointmentsBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Date' }, stacked: true },
                    y: { title: { display: true, text: 'Appointments' }, beginAtZero: true, precision: 0, stacked: true }
                }
            }
        });

        // Revenue Line Chart
        const revenueCtx = document.getElementById('revenueLineChart').getContext('2d');
        const revenueLabels = <?= json_encode($allDatesFormatted) ?>;
        const revenueData = <?= json_encode($revenueChartData) ?>;

        const revenueLineChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Revenue (₱)' }, beginAtZero: true }
                }
            }
        });

        // Quick Filter Buttons for Appointments
        document.getElementById('filterThisMonth').addEventListener('click', function () {
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            document.getElementById('startDate').value = start.toISOString().slice(0, 10);
            document.getElementById('endDate').value = end.toISOString().slice(0, 10);
            document.getElementById('appointmentReportForm').submit();
        });
        document.getElementById('filterThisYear').addEventListener('click', function () {
            const now = new Date();
            const start = new Date(now.getFullYear(), 0, 1);
            const end = new Date(now.getFullYear(), 11, 31);
            document.getElementById('startDate').value = start.toISOString().slice(0, 10);
            document.getElementById('endDate').value = end.toISOString().slice(0, 10);
            // Add filter param to URL for yearly aggregation
            window.location.search = `?view=reports&start=${start.toISOString().slice(0,10)}&end=${end.toISOString().slice(0,10)}&filter=year`;
        });

        // Quick Filter Buttons for Revenue
        document.getElementById('revenueFilterThisMonth').addEventListener('click', function () {
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            document.getElementById('revenueStartDate').value = start.toISOString().slice(0, 10);
            document.getElementById('revenueEndDate').value = end.toISOString().slice(0, 10);
            document.getElementById('revenueReportForm').submit();
        });
        document.getElementById('revenueFilterThisYear').addEventListener('click', function () {
            const now = new Date();
            const start = new Date(now.getFullYear(), 0, 1);
            const end = new Date(now.getFullYear(), 11, 31);
            document.getElementById('revenueStartDate').value = start.toISOString().slice(0, 10);
            document.getElementById('revenueEndDate').value = end.toISOString().slice(0, 10);
            // Add filter param to URL for yearly aggregation
            window.location.search = `?view=reports&start=${start.toISOString().slice(0,10)}&end=${end.toISOString().slice(0,10)}&filter=year`;
        });

        // Filter form reloads page with new date range
        document.getElementById('appointmentReportForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            window.location.search = `?view=reports&start=${start}&end=${end}`;
        });

        // Revenue filter form
        document.getElementById('revenueReportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const start = document.getElementById('revenueStartDate').value;
            const end = document.getElementById('revenueEndDate').value;
            window.location.search = `?view=reports&start=${start}&end=${end}`;
        });
    });
</script>