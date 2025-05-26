<?php
// pages/admin/reports/report-templates/booking_summary.php
$data = $reportData;
$bookings = $data['bookings'];
$summary = $data['summary'];
?>

<div class="report-section">
    <!-- Summary Statistics -->
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-chart-pie me-2"></i>Summary Statistics
        </h3>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= number_format($summary['total_bookings']) ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-number"><?= number_format($summary['completed_count']) ?></div>
            <div class="stat-label">Completed</div>
            <div class="stat-change">
                <?= $summary['total_bookings'] > 0 ? round(($summary['completed_count'] / $summary['total_bookings']) * 100, 1) : 0 ?>% of total
            </div>
        </div>
        <div class="stat-card stat-warning">
            <div class="stat-number"><?= number_format($summary['pending_count']) ?></div>
            <div class="stat-label">Pending</div>
            <div class="stat-change">
                <?= $summary['total_bookings'] > 0 ? round(($summary['pending_count'] / $summary['total_bookings']) * 100, 1) : 0 ?>% of total
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-number"><?= number_format($summary['confirmed_count']) ?></div>
            <div class="stat-label">Confirmed</div>
            <div class="stat-change">
                <?= $summary['total_bookings'] > 0 ? round(($summary['confirmed_count'] / $summary['total_bookings']) * 100, 1) : 0 ?>% of total
            </div>
        </div>
        <div class="stat-card stat-danger">
            <div class="stat-number"><?= number_format($summary['cancelled_count']) ?></div>
            <div class="stat-label">Cancelled</div>
            <div class="stat-change">
                <?= $summary['total_bookings'] > 0 ? round(($summary['cancelled_count'] / $summary['total_bookings']) * 100, 1) : 0 ?>% of total
            </div>
        </div>
        <div class="stat-card stat-purple">
            <div class="stat-number text-currency">₱<?= number_format($summary['total_revenue'], 2) ?></div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-change">
                Average: ₱<?= $summary['total_bookings'] > 0 ? number_format($summary['total_revenue'] / $summary['total_bookings'], 2) : '0.00' ?> per booking
            </div>
        </div>
    </div>
</div>

<!-- Status Breakdown Chart -->
<div class="report-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-chart-bar me-2"></i>Status Distribution
        </h3>
    </div>
    
    <div class="chart-container">
        <div class="row">
            <div class="col-md-6">
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Completed Bookings</span>
                        <span><?= number_format($summary['completed_count']) ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?= $summary['total_bookings'] > 0 ? ($summary['completed_count'] / $summary['total_bookings']) * 100 : 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Confirmed Bookings</span>
                        <span><?= number_format($summary['confirmed_count']) ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: <?= $summary['total_bookings'] > 0 ? ($summary['confirmed_count'] / $summary['total_bookings']) * 100 : 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Approved Bookings</span>
                        <span><?= number_format($summary['approved_count']) ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: <?= $summary['total_bookings'] > 0 ? ($summary['approved_count'] / $summary['total_bookings']) * 100 : 0 ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Pending Bookings</span>
                        <span><?= number_format($summary['pending_count']) ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: <?= $summary['total_bookings'] > 0 ? ($summary['pending_count'] / $summary['total_bookings']) * 100 : 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Cancelled Bookings</span>
                        <span><?= number_format($summary['cancelled_count']) ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-danger" style="width: <?= $summary['total_bookings'] > 0 ? ($summary['cancelled_count'] / $summary['total_bookings']) * 100 : 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Success Rate</span>
                        <span><?= $summary['total_bookings'] > 0 ? round((($summary['completed_count'] + $summary['confirmed_count']) / $summary['total_bookings']) * 100, 1) : 0 ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-gradient-success" style="width: <?= $summary['total_bookings'] > 0 ? (($summary['completed_count'] + $summary['confirmed_count']) / $summary['total_bookings']) * 100 : 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Booking List -->
<div class="report-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-list me-2"></i>Detailed Booking List
        </h3>
    </div>
    
    <?php if (!empty($bookings)): ?>
        <div class="report-table">
            <table class="table table-striped" id="bookingsTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'bookingsTable')" style="cursor: pointer;">
                            Reference ID <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(1, 'bookingsTable')" style="cursor: pointer;">
                            Client Name <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(2, 'bookingsTable')" style="cursor: pointer;">
                            Event Type <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(3, 'bookingsTable')" style="cursor: pointer;">
                            Date <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(4, 'bookingsTable')" style="cursor: pointer;">
                            Time <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(5, 'bookingsTable')" style="cursor: pointer;">
                            Location <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(6, 'bookingsTable')" style="cursor: pointer;">
                            Status <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(7, 'bookingsTable')" style="cursor: pointer;">
                            Payment <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(8, 'bookingsTable')" style="cursor: pointer;">
                            Amount <i class="fas fa-sort"></i>
                        </th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($booking['reference_id']) ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($booking['client_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($booking['client_email']) ?></small>
                                </div>
                            </td>
                            <td>
                                <span>
                                    <?= htmlspecialchars($booking['event_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($booking['reservation_date'])) ?>
                            </td>
                            <td>
                                <div>
                                    <small>
                                        <?= date('g:i A', strtotime($booking['start_time'])) ?> - 
                                        <?= date('g:i A', strtotime($booking['end_time'])) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <small>
                                        <?= htmlspecialchars($booking['street_address']) ?><br>
                                        <?= htmlspecialchars($booking['barangay']) ?>, <?= htmlspecialchars($booking['city']) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch ($booking['status']) {
                                    case 'pending':
                                        $statusClass = 'status-pending';
                                        break;
                                    case 'approved':
                                        $statusClass = 'status-approved';
                                        break;
                                    case 'confirmed':
                                        $statusClass = 'status-confirmed';
                                        break;
                                    case 'completed':
                                        $statusClass = 'status-completed';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'status-cancelled';
                                        break;
                                    default:
                                        $statusClass = 'status-pending';
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($booking['payment_method'])): ?>
                                    <?php
                                    $paymentClass = '';
                                    switch ($booking['status']) {
                                        case 'paid':
                                            $paymentClass = 'payment-paid';
                                            break;
                                        case 'pending':
                                            $paymentClass = 'payment-pending';
                                            break;
                                        case 'partial':
                                            $paymentClass = 'payment-partial';
                                            break;
                                        case 'refunded':
                                            $paymentClass = 'payment-refunded';
                                            break;
                                        default:
                                            $paymentClass = 'payment-pending';
                                    }
                                    ?>
                                    <span class="payment-badge <?= $paymentClass ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($booking['payment_method']) ?>
                                    </small>
                                    <?php if (!empty($booking['payment_date'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($booking['payment_date'])) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="payment-badge payment-pending">No Payment</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-currency">
                                    ₱<?= number_format($booking['amount_paid'] ?? 0, 2) ?>
                                </strong>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <?php if (!empty($booking['client_phone'])): ?>
                                        <small>
                                            <i class="fas fa-phone fa-xs"></i>
                                            <?= htmlspecialchars($booking['client_phone']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Table Summary -->
            <div class="table-summary mt-3 p-3 bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total Records: <?= count($bookings) ?></strong>
                    </div>
                    <div class="col-md-6 text-end">
                        <strong>Total Revenue: ₱<?= number_format($summary['total_revenue'], 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Export Options (No Print) -->
        <div class="no-print mt-3">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" onclick="exportTableToCSV('bookingsTable', 'booking-summary.csv')">
                    <i class="fas fa-file-csv me-2"></i>Export to CSV
                </button>
                <button type="button" class="btn btn-info" onclick="filterTable('', 'bookingsTable')">
                    <i class="fas fa-filter me-2"></i>Clear Filters
                </button>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h5>No Bookings Found</h5>
            <p>No bookings match the selected criteria for the specified date range.</p>
            <div class="mt-3">
                <button type="button" class="btn btn-primary" onclick="resetFilters()">
                    <i class="fas fa-undo me-2"></i>Reset Filters
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Event Type Breakdown -->
<?php if (!empty($bookings)): ?>
    <div class="report-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-chart-donut me-2"></i>Event Type Breakdown
            </h3>
        </div>
        
        <?php
        // Calculate event type breakdown
        $eventTypeStats = [];
        foreach ($bookings as $booking) {
            $type = $booking['event_type'];
            if (!isset($eventTypeStats[$type])) {
                $eventTypeStats[$type] = [
                    'count' => 0,
                    'revenue' => 0
                ];
            }
            $eventTypeStats[$type]['count']++;
            $eventTypeStats[$type]['revenue'] += $booking['amount_paid'] ?? 0;
        }
        
        // Sort by count descending
        arsort($eventTypeStats);
        ?>
        
        <div class="revenue-breakdown">
            <?php foreach ($eventTypeStats as $eventType => $stats): ?>
                <div class="revenue-item">
                    <div class="revenue-type"><?= htmlspecialchars($eventType) ?></div>
                    <div class="revenue-count"><?= number_format($stats['count']) ?> bookings</div>
                    <div class="revenue-amount">₱<?= number_format($stats['revenue'], 2) ?></div>
                    <small class="text-muted">
                        <?= $summary['total_bookings'] > 0 ? round(($stats['count'] / $summary['total_bookings']) * 100, 1) : 0 ?>% of total
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Monthly Trend (if data spans multiple months) -->
<?php 
if (!empty($bookings)) {
    // Group by month
    $monthlyStats = [];
    foreach ($bookings as $booking) {
        $month = date('Y-m', strtotime($booking['reservation_date']));
        if (!isset($monthlyStats[$month])) {
            $monthlyStats[$month] = [
                'count' => 0,
                'revenue' => 0
            ];
        }
        $monthlyStats[$month]['count']++;
        $monthlyStats[$month]['revenue'] += $booking['amount_paid'] ?? 0;
    }
    
    if (count($monthlyStats) > 1):
?>
    <div class="report-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-chart-line me-2"></i>Monthly Trend
            </h3>
        </div>
        
        <div class="chart-container">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Avg per Booking</th>
                            <th>Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $previousCount = 0;
                        foreach ($monthlyStats as $month => $stats): 
                            $growth = $previousCount > 0 ? (($stats['count'] - $previousCount) / $previousCount) * 100 : 0;
                            $previousCount = $stats['count'];
                        ?>
                            <tr>
                                <td><?= date('M Y', strtotime($month . '-01')) ?></td>
                                <td><?= number_format($stats['count']) ?></td>
                                <td>₱<?= number_format($stats['revenue'], 2) ?></td>
                                <td>₱<?= $stats['count'] > 0 ? number_format($stats['revenue'] / $stats['count'], 2) : '0.00' ?></td>
                                <td>
                                    <?php if ($growth != 0): ?>
                                        <span class="<?= $growth > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $growth > 0 ? '+' : '' ?><?= number_format($growth, 1) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php 
    endif;
} 
?>