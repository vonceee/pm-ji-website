<?php
// pages/admin/dashboard/dashboard/index.php - Dashboard only (reports removed)

// Add debugging at the start
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log all GET parameters
error_log("Dashboard DEBUG - GET parameters: " . print_r($_GET, true));

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;
$pdo = Database::getConnection();

if (isset($_GET['ajax']) && $_GET['ajax'] === '1' && isset($_GET['action']) && $_GET['action'] === 'update_dashboard') {

    // Set JSON content type header
    header('Content-Type: application/json');

    try {
        // Get and validate date parameters
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        // Validate dates
        if (!DateTime::createFromFormat('Y-m-d', $start)) {
            $start = date('Y-m-01');
        }
        if (!DateTime::createFromFormat('Y-m-d', $end)) {
            $end = date('Y-m-t');
        }

        // Ensure end date is not before start date
        if (strtotime($end) < strtotime($start)) {
            $end = $start;
        }

        // Create formatted date range for display
        $startFormatted = date('M d, Y', strtotime($start));
        $endFormatted = date('M d, Y', strtotime($end));
        $dateRangeDisplay = $startFormatted . ' - ' . $endFormatted;

        // Fetch dashboard stats
        $totalCount = $stats->totalAppointments($start, $end);
        $approvedCount = $stats->approvedAppointments($start, $end);
        $pendingCount = $stats->pendingApprovals($start, $end);
        $completedCount = $stats->completedBookings($start, $end);
        $revenue = $stats->revenueForRange($start, $end);

        // Calculate revenue growth
        $daysDiff = (strtotime($end) - strtotime($start)) / (60 * 60 * 24) + 1;
        $previousStart = date('Y-m-d', strtotime($start . ' -' . $daysDiff . ' days'));
        $previousEnd = date('Y-m-d', strtotime($start . ' -1 day'));
        $previousRevenue = $stats->revenueForRange($previousStart, $previousEnd);

        $revenueGrowth = 0;
        if ($previousRevenue > 0) {
            $revenueGrowth = (($revenue - $previousRevenue) / $previousRevenue) * 100;
        } elseif ($revenue > 0) {
            $revenueGrowth = 100;
        }

        // Get additional data
        $revenueByEventType = $stats->revenueByEventType($start, $end);
        $recentBookings = $stats->recentBookings(5);
        $upcomingBookings = $stats->upcomingBookings(7);

        // Prepare response data
        $responseData = [
            'success' => true,
            'data' => [
                'totalCount' => $totalCount,
                'approvedCount' => $approvedCount,
                'pendingCount' => $pendingCount,
                'completedCount' => $completedCount,
                'revenue' => $revenue,
                'revenueGrowth' => $revenueGrowth,
                'upcomingBookings' => $upcomingBookings,
                'dateRangeDisplay' => $dateRangeDisplay,
                'revenueByEventType' => $revenueByEventType,
                'recentBookings' => $recentBookings,
                'startDate' => $start,
                'endDate' => $end
            ],
            'timestamp' => time()
        ];

        // Log successful AJAX request
        error_log("Dashboard AJAX: Successfully processed request for range $start to $end");

        // Return JSON response
        echo json_encode($responseData);
        exit;

    } catch (Exception $e) {
        // Log the error
        error_log("Dashboard AJAX Error: " . $e->getMessage());
        error_log("Dashboard AJAX Error Stack Trace: " . $e->getTraceAsString());

        // Return error response
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update dashboard data',
            'error' => $e->getMessage(),
            'timestamp' => time()
        ]);
        exit;
    }
}

// dashboard service class model
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/DashboardStats.php';

// create instances
$stats = new \Models\DashboardStats($pdo);

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

// Debug: Final check before rendering
error_log("Dashboard DEBUG - About to render page with date range: $dateRangeDisplay");
?>

<head>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/dashboard.css" />

    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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
    <div class="dashboard-section">
        <!-- Date Range Picker Header -->
        <header>
            <div class="dashboard-header">
                <h4>Dashboard</h4>
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
                    <h4>Recent Bookings</h4>
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

    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/date-range-picker.js"></script>
</body>