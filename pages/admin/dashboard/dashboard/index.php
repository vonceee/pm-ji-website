<?php
// pages/admin/dashboard/dashboard/index.php

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;
$pdo = Database::getConnection();

// dashboard service class model
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/DashboardStats.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/ReportGenerator.php';

// create instances
$stats = new \Models\DashboardStats($pdo);
$reportGenerator = new \Models\ReportGenerator($pdo);

// get date range and other parameters
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');
$reportType = $_GET['report_type'] ?? '';
$status = $_GET['status'] ?? '';
$eventType = $_GET['event_type'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';

// fetch dashboard stats
$totalCount = $stats->totalAppointments($start, $end);
$approvedCount = $stats->approvedAppointments($start, $end);
$pendingCount = $stats->pendingApprovals($start, $end);
$completedCount = $stats->completedBookings($start, $end);
$Revenue = $stats->revenueForRange($start, $end);
$thisMonthRevenue = $stats->revenueForMonth(date('Y'), date('m'));
$lastMonthRevenue = $stats->revenueForMonth(date('Y', strtotime('-1 month')), date('m', strtotime('-1 month')));

// calculate revenue growth percentage
$revenueGrowth = 0;
if ($lastMonthRevenue > 0) {
    $revenueGrowth = (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
}

// get additional dashboard data
$revenueByEventType = $stats->revenueByEventType($start, $end);
$recentBookings = $stats->recentBookings(5);
$outstandingPayments = $stats->outstandingPayments();
$upcomingBookings = $stats->upcomingBookings(7);

// report data (only if report type is selected)
$reportData = [];
$reportTitle = '';
$reportDescription = '';

if (!empty($reportType)) {
    switch ($reportType) {
        case 'booking_summary':
            $reportData = $reportGenerator->getBookingSummaryReport($start, $end, $status, $eventType);
            $reportTitle = 'Booking Summary Report';
            $reportDescription = 'Overview of all bookings for the selected period';
            break;
        case 'revenue_report':
            $reportData = $reportGenerator->getRevenueReport($start, $end, $eventType);
            $reportTitle = 'Revenue Report';
            $reportDescription = 'Financial overview and revenue breakdown';
            break;
        case 'payment_report':
            $reportData = $reportGenerator->getPaymentReport($start, $end, $paymentStatus);
            $reportTitle = 'Payment Status Report';
            $reportDescription = 'Detailed payment tracking and outstanding amounts';
            break;
        case 'event_analysis':
            $reportData = $reportGenerator->getEventAnalysisReport($start, $end);
            $reportTitle = 'Event Type Analysis';
            $reportDescription = 'Performance analysis by event type';
            break;
    }
}

// get filter options for reports
$eventTypes = $reportGenerator->getEventTypes();
$statuses = ['pending', 'approved', 'confirmed', 'cancelled', 'completed'];
$paymentStatuses = ['pending', 'paid', 'partial', 'refunded'];
?>

<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/dashboard.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/reports.css">

    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

</head>

<body>
    <!-- Dashboard Section -->
    <div class="dashboard-section no-print">
        <!-- Date Range Picker Header -->
        <header>
            <div class="dashboard-header">
                <h4>Dashboard</h4>
                <form id="mainForm" method="GET">
                    <div class="date-range-input-wrapper">
                        <input type="text" id="dateRange" name="dateRange" class="form-control" autocomplete="off"
                            data-start="<?= $start ?>" data-end="<?= $end ?>" />
                        <span class="calendar-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input type="hidden" name="start" value="<?= $start ?>">
                    <input type="hidden" name="end" value="<?= $end ?>">
                    <input type="hidden" name="report_type" value="<?= $reportType ?>">
                    <input type="hidden" name="status" value="<?= $status ?>">
                    <input type="hidden" name="event_type" value="<?= $eventType ?>">
                    <input type="hidden" name="payment_status" value="<?= $paymentStatus ?>">
                </form>
            </div>
        </header>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <h2><?= $totalCount ?></h2>
                <div class="desc">Total Bookings</div>
                <small class="text-muted">for selected period</small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-success"><?= $approvedCount ?></h2>
                <div class="desc">Approved Bookings</div>
                <small class="text-muted">ready for service</small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-warning"><?= $pendingCount ?></h2>
                <div class="desc">Pending Approvals</div>
                <small class="text-muted">requires attention</small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-info"><?= $completedCount ?></h2>
                <div class="desc">Completed Bookings</div>
                <small class="text-muted">with payments</small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-primary">₱<?= number_format($Revenue, 2) ?></h2>
                <div class="desc">Total Revenue</div>
                <small class="text-muted">
                    <?php if ($revenueGrowth >= 0): ?>
                        <span class="text-success">↗ <?= number_format($revenueGrowth, 1) ?>%</span>
                    <?php else: ?>
                        <span class="text-danger">↘ <?= number_format(abs($revenueGrowth), 1) ?>%</span>
                    <?php endif; ?>
                    vs last month
                </small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-purple"><?= $upcomingBookings ?></h2>
                <div class="desc">Upcoming Bookings</div>
                <small class="text-muted">next 7 days</small>
            </div>
        </div>

        <!-- Revenue by Event Type Section -->
        <?php if (!empty($revenueByEventType)): ?>
            <section class="revenue-breakdown">
                <header>
                    <div class="dashboard-header">
                        <h4>Revenue by Event Type</h4>
                    </div>
                </header>
                <div class="revenue-cards">
                    <?php foreach ($revenueByEventType as $eventData): ?>
                        <div class="revenue-card">
                            <h3><?= htmlspecialchars($eventData['event_type']) ?></h3>
                            <div class="revenue-amount">₱<?= number_format($eventData['total_revenue'], 2) ?></div>
                            <div class="booking-count"><?= $eventData['booking_count'] ?> bookings</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Recent Bookings Section -->
        <section class="recent-bookings">
            <header>
                <div class="dashboard-header mb-2">
                    <h4>Recent Bookings</h4>
                </div>
            </header>
            <div class="bookings-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference ID</th>
                            <th>Event Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['reference_id']) ?></td>
                                <td><?= htmlspecialchars($booking['event_type']) ?></td>
                                <td><?= date('M d, Y', strtotime($booking['reservation_date'])) ?></td>
                                <td><?= htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']) ?></td>
                                <td><?= htmlspecialchars($booking['city']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['amount_paid']): ?>
                                        ₱<?= number_format($booking['amount_paid'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($booking['payment_status']): ?>
                                        <span class="payment-badge payment-<?= strtolower($booking['payment_status']) ?>">
                                            <?= ucfirst($booking['payment_status']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No payment</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <!-- Section Divider -->
    <div class="section-divider no-print"></div>

    <!-- Reports Section -->
    <div class="reports-section">
        <!-- Report Controls -->
        <div class="reports-toggle no-print">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Generate Reports</h4>
                </div>
            </div>

            <form id="reportForm" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select" onchange="updateReportType()">
                            <option value="">Select Report Type</option>
                            <option value="booking_summary" <?= $reportType === 'booking_summary' ? 'selected' : '' ?>>Booking Summary</option>
                            <option value="revenue_report" <?= $reportType === 'revenue_report' ? 'selected' : '' ?>>Revenue Report</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $statusOption): ?>
                                <option value="<?= $statusOption ?>" <?= $status === $statusOption ? 'selected' : '' ?>>
                                    <?= ucfirst($statusOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select">
                            <option value="">All Events</option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?= $type['event_type'] ?>" <?= $eventType === $type['event_type'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['event_type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All Payments</option>
                            <?php foreach ($paymentStatuses as $paymentOption): ?>
                                <option value="<?= $paymentOption ?>" <?= $paymentStatus === $paymentOption ? 'selected' : '' ?>>
                                    <?= ucfirst($paymentOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-chart-line me-2"></i>Generate
                        </button>
                    </div>
                </div>
                <input type="hidden" name="start" value="<?= $start ?>">
                <input type="hidden" name="end" value="<?= $end ?>">
            </form>
        </div>

        <!-- Report Content -->
        <?php if (!empty($reportType) && !empty($reportData)): ?>
            <div class="report-content">
                <!-- Report Header -->
                <div class="report-header text-center mb-4">
                    <h1 class="report-title"><?= $reportTitle ?></h1>
                    <p class="report-description"><?= $reportDescription ?></p>
                    <div class="report-meta">
                        <strong>Period:</strong> <?= date('M d, Y', strtotime($start)) ?> - <?= date('M d, Y', strtotime($end)) ?> | 
                        <strong>Generated:</strong> <?= date('M d, Y g:i A') ?>
                    </div>
                </div>

                <!-- Include Report Template -->
                <?php 
                $reportTemplatePath = $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/report-templates/' . $reportType . '.php';
                if (file_exists($reportTemplatePath)) {
                    include $reportTemplatePath;
                } else {
                    echo '<div class="alert alert-warning">Report template not found.</div>';
                }
                ?>

                <!-- Report Footer -->
                <div class="report-footer mt-5 pt-3 border-top">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">
                                Generated by: Admin Panel<br>
                                Report ID: RPT-<?= date('Ymd-His') ?>
                            </small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">
                                Page 1 of 1<br>
                                Confidential - Internal Use Only
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/reports.js"></script>
</body>