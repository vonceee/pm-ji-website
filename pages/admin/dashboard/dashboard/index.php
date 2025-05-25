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
$completedCount = $stats->completedBookings($start, $end);
$Revenue = $stats->revenueForRange($start, $end);
$thisMonthRevenue = $stats->revenueForMonth(date('Y'), date('m'));
$lastMonthRevenue = $stats->revenueForMonth(date('Y', strtotime('-1 month')), date('m', strtotime('-1 month')));

// calculate revenue growth percentage
$revenueGrowth = 0;
if ($lastMonthRevenue > 0) {
    $revenueGrowth = (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
}

// get additional data for dashboard
$revenueByEventType = $stats->revenueByEventType($start, $end);
$recentBookings = $stats->recentBookings(5);
$outstandingPayments = $stats->outstandingPayments();
$upcomingBookings = $stats->upcomingBookings(7);

?>

<head>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/index.css" />
    <!-- End Custom CSS -->

    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/date-range-picker.js"></script>
    <!-- End Date Time Picker -->

    <!-- Chart.js for revenue charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <!-- Date Range Picker Header -->
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
    <!-- End Date Range Picker Header -->

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <h2><?= $totalCount ?></h2>
            <div class="desc">Total Bookings</div>
            <small class="text-muted">For selected period</small>
        </div>
        <div class="dashboard-card">
            <h2 class="text-success"><?= $approvedCount ?></h2>
            <div class="desc">Approved Bookings</div>
            <small class="text-muted">Ready for service</small>
        </div>
        <div class="dashboard-card">
            <h2 class="text-warning"><?= $pendingCount ?></h2>
            <div class="desc">Pending Approvals</div>
            <small class="text-muted">Requires attention</small>
        </div>
        <div class="dashboard-card">
            <h2 class="text-info"><?= $completedCount ?></h2>
            <div class="desc">Completed Bookings</div>
            <small class="text-muted">With payments</small>
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
            <small class="text-muted">Next 7 days</small>
        </div>
    </div>
    <!-- End Dashboard Cards -->

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
            <div class="dashboard-header">
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

    <!-- Outstanding Payments Alert -->
    <?php if (!empty($outstandingPayments)): ?>
        <section class="outstanding-payments">
            <header>
                <div class="dashboard-header">
                    <h4 class="text-warning">Outstanding Payments (<?= count($outstandingPayments) ?>)</h4>
                </div>
            </header>
            <div class="alert alert-warning">
                <strong>Attention:</strong> You have <?= count($outstandingPayments) ?> booking(s) with outstanding
                payments.
                <a href="/NEW-PM-JI-RESERVIFY/pages/admin/payments/outstanding.php" class="btn btn-sm btn-warning">
                    View Details
                </a>
            </div>
        </section>
    <?php endif; ?>

</body>