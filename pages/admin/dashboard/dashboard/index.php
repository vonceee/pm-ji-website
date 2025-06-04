<?php
// pages/admin/dashboard/dashboard/index.php - Improved version with debugging

// Add debugging at the start
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log all GET parameters
error_log("Dashboard DEBUG - GET parameters: " . print_r($_GET, true));

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

// get date range parameters with better validation
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

// Debug: Log received dates
error_log("Dashboard DEBUG - Received start: $start, end: $end");

// validate and sanitize dates
$originalStart = $start;
$originalEnd = $end;

if (!DateTime::createFromFormat('Y-m-d', $start)) {
    $start = date('Y-m-01'); // Default to first day of current month
    error_log("Dashboard DEBUG - Invalid start date '$originalStart', using default: $start");
}
if (!DateTime::createFromFormat('Y-m-d', $end)) {
    $end = date('Y-m-t'); // Default to last day of current month
    error_log("Dashboard DEBUG - Invalid end date '$originalEnd', using default: $end");
}

// ensure end date is not before start date
if (strtotime($end) < strtotime($start)) {
    $end = $start;
    error_log("Dashboard DEBUG - End date before start date, adjusted end to: $end");
}

// Final validation
error_log("Dashboard DEBUG - Final dates - start: $start, end: $end");

// get other parameters
$reportType = $_GET['report_type'] ?? '';
$status = $_GET['status'] ?? '';
$eventType = $_GET['event_type'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';

// create formatted date range for display
$startFormatted = date('M d, Y', strtotime($start));
$endFormatted = date('M d, Y', strtotime($end));
$dateRangeDisplay = $startFormatted . ' - ' . $endFormatted;

// Debug: Log the formatted display
error_log("Dashboard DEBUG - Date range display: $dateRangeDisplay");

try {
    // fetch dashboard stats for the selected date range
    error_log("Dashboard DEBUG - Fetching stats for range: $start to $end");

    $totalCount = $stats->totalAppointments($start, $end);
    $approvedCount = $stats->approvedAppointments($start, $end);
    $pendingCount = $stats->pendingApprovals($start, $end);
    $completedCount = $stats->completedBookings($start, $end);
    $Revenue = $stats->revenueForRange($start, $end);

    // Debug: Log the fetched stats
    error_log("Dashboard DEBUG - Stats retrieved: Total=$totalCount, Approved=$approvedCount, Pending=$pendingCount, Completed=$completedCount, Revenue=$Revenue");

    // calculate revenue growth (compare with previous period of same length)
    $daysDiff = (strtotime($end) - strtotime($start)) / (60 * 60 * 24) + 1;
    $previousStart = date('Y-m-d', strtotime($start . ' -' . $daysDiff . ' days'));
    $previousEnd = date('Y-m-d', strtotime($start . ' -1 day'));

    $previousRevenue = $stats->revenueForRange($previousStart, $previousEnd);

    // calculate revenue growth percentage
    $revenueGrowth = 0;
    if ($previousRevenue > 0) {
        $revenueGrowth = (($Revenue - $previousRevenue) / $previousRevenue) * 100;
    } elseif ($Revenue > 0) {
        $revenueGrowth = 100; // 100% growth from 0
    }

    // get additional dashboard data
    $revenueByEventType = $stats->revenueByEventType($start, $end);
    $recentBookings = $stats->recentBookings(5);
    $outstandingPayments = $stats->outstandingPayments();
    $upcomingBookings = $stats->upcomingBookings(7);

    error_log("Dashboard DEBUG - Additional data loaded successfully");

} catch (Exception $e) {
    // handle database errors gracefully
    error_log("Dashboard Error: " . $e->getMessage());
    error_log("Dashboard Error Stack Trace: " . $e->getTraceAsString());

    $totalCount = $approvedCount = $pendingCount = $completedCount = 0;
    $Revenue = 0;
    $revenueGrowth = 0;
    $revenueByEventType = [];
    $recentBookings = [];
    $outstandingPayments = [];
    $upcomingBookings = 0;
}

// report data (only if report type is selected)
$reportData = [];
$reportTitle = '';
$reportDescription = '';

if (!empty($reportType)) {
    try {
        error_log("Dashboard DEBUG - Generating report: $reportType");

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

        error_log("Dashboard DEBUG - Report generated with " . count($reportData) . " records");

    } catch (Exception $e) {
        error_log("Report Generation Error: " . $e->getMessage());
        error_log("Report Generation Error Stack Trace: " . $e->getTraceAsString());
        $reportData = [];
    }
}

// get filter options for reports
try {
    $eventTypes = $reportGenerator->getEventTypes();
} catch (Exception $e) {
    error_log("Error getting event types: " . $e->getMessage());
    $eventTypes = [];
}

$statuses = ['pending', 'approved', 'confirmed', 'cancelled', 'completed'];
$paymentStatuses = ['pending', 'paid', 'partial', 'refunded'];

// Debug: Final check before rendering
error_log("Dashboard DEBUG - About to render page with date range: $dateRangeDisplay");
?>

<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/dashboard.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/reports.css">
    <!-- End Custom CSS -->

    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <!-- End Date Time Picker -->

    <style>
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .dashboard-cards {
            position: relative;
        }

        .date-range-display {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 1rem;
        }

        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <!-- Debug Information (remove in production) -->
    <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
        <div class="debug-info">
            <strong>DEBUG INFO:</strong><br>
            Start Date: <?= htmlspecialchars($start) ?><br>
            End Date: <?= htmlspecialchars($end) ?><br>
            Display Range: <?= htmlspecialchars($dateRangeDisplay) ?><br>
            Total Count: <?= $totalCount ?><br>
            Approved: <?= $approvedCount ?><br>
            Revenue: ₱<?= number_format($Revenue, 2) ?><br>
            URL: <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?><br>
            Timestamp: <?= date('Y-m-d H:i:s') ?>
        </div>
    <?php endif; ?>

    <!-- Dashboard Section -->
    <div class="dashboard-section no-print">
        <!-- Date Range Picker Header -->
        <header>
            <div class="dashboard-header">
                <h4>Dashboard & Reports</h4>
                <div class="date-range-display">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Showing data for: <strong><?= $dateRangeDisplay ?></strong>
                </div>
                <form id="mainForm" method="GET">
                    <div class="date-range-input-wrapper">
                        <input type="text" id="dateRange" name="dateRange" class="form-control" autocomplete="off"
                            data-start="<?= $start ?>" data-end="<?= $end ?>"
                            value="<?= $startFormatted . ' - ' . $endFormatted ?>" />
                        <span class="calendar-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                    <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
                    <input type="hidden" name="report_type" value="<?= htmlspecialchars($reportType) ?>">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                    <input type="hidden" name="event_type" value="<?= htmlspecialchars($eventType) ?>">
                    <input type="hidden" name="payment_status" value="<?= htmlspecialchars($paymentStatus) ?>">
                </form>
            </div>
        </header>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <h2><?= number_format($totalCount) ?></h2>
                <div class="desc">Total Bookings</div>
                <small class="text-muted">for selected period</small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-success"><?= number_format($approvedCount) ?></h2>
                <div class="desc">Approved Bookings</div>
                <small class="text-muted">ready for service</small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-warning"><?= number_format($pendingCount) ?></h2>
                <div class="desc">Pending Approvals</div>
                <small class="text-muted">requires attention</small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-info"><?= number_format($completedCount) ?></h2>
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
                    vs previous period
                </small>
            </div>
            <div class="dashboard-card">
                <h2 class="text-purple"><?= number_format($upcomingBookings) ?></h2>
                <div class="desc">Upcoming Bookings</div>
                <small class="text-muted">next 7 days</small>
            </div>
        </div>

        <!-- Rest of the content remains the same as your original file -->
        <!-- Revenue by Event Type Section -->
        <?php if (!empty($revenueByEventType)): ?>
            <section class="revenue-breakdown">
                <header>
                    <div class="dashboard-header">
                        <h4>Revenue by Event Type</h4>
                        <small class="text-muted">For period: <?= $dateRangeDisplay ?></small>
                    </div>
                </header>
                <div class="revenue-cards">
                    <?php foreach ($revenueByEventType as $eventData): ?>
                        <div class="revenue-card">
                            <h3><?= htmlspecialchars($eventData['event_type']) ?></h3>
                            <div class="revenue-amount">₱<?= number_format($eventData['total_revenue'], 2) ?></div>
                            <div class="booking-count"><?= number_format($eventData['booking_count']) ?> bookings</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <section class="revenue-breakdown">
                <header>
                    <div class="dashboard-header">
                        <h4>Revenue by Event Type</h4>
                        <small class="text-muted">For period: <?= $dateRangeDisplay ?></small>
                    </div>
                </header>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No revenue data available for the selected period.
                </div>
            </section>
        <?php endif; ?>

        <!-- Recent Bookings Section -->
        <section class="recent-bookings">
            <header>
                <div class="dashboard-header mb-2">
                    <h4>Upcoming Bookings</h4>
                    <small class="text-muted">Latest bookings from the system</small>
                </div>
            </header>
            <div class="bookings-table">
                <?php if (!empty($recentBookings)): ?>
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
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No recent bookings found.
                    </div>
                <?php endif; ?>
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
                    <small class="text-muted">Reports will be generated for: <?= $dateRangeDisplay ?></small>
                </div>
            </div>

            <form id="reportForm" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select" onchange="updateReportType()">
                            <option value="">Select Report Type</option>
                            <option value="booking_summary" <?= $reportType === 'booking_summary' ? 'selected' : '' ?>>
                                Booking Summary</option>
                            <option value="revenue_report" <?= $reportType === 'revenue_report' ? 'selected' : '' ?>>
                                Revenue Report</option>
                            <option value="payment_report" <?= $reportType === 'payment_report' ? 'selected' : '' ?>>
                                Payment Report</option>
                            <option value="event_analysis" <?= $reportType === 'event_analysis' ? 'selected' : '' ?>>Event
                                Analysis</option>
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
                                <option value="<?= htmlspecialchars($type['event_type']) ?>"
                                    <?= $eventType === $type['event_type'] ? 'selected' : '' ?>>
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
                <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
            </form>
        </div>

        <!-- Report Content -->
        <?php if (!empty($reportType) && !empty($reportData)): ?>
            <div class="report-content">
                <!-- Report Header -->
                <div class="report-header text-center mb-4">
                    <h1 class="report-title"><?= htmlspecialchars($reportTitle) ?></h1>
                    <p class="report-description"><?= htmlspecialchars($reportDescription) ?></p>
                    <div class="report-meta">
                        <strong>Period:</strong> <?= $dateRangeDisplay ?> |
                        <strong>Generated:</strong> <?= date('M d, Y g:i A') ?>
                    </div>
                </div>

                <!-- Include Report Template -->
                <?php
                $reportTemplatePath = $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/report-templates/' . $reportType . '.php';
                if (file_exists($reportTemplatePath)) {
                    include $reportTemplatePath;
                } else {
                    echo '<div class="alert alert-warning">Report template not found for: ' . htmlspecialchars($reportType) . '</div>';
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
        <?php elseif (!empty($reportType)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No data available for the selected report type and date range.
            </div>
        <?php endif; ?>
    </div>

    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/date-range-picker.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/reports.js"></script>
</body>