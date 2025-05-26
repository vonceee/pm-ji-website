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
        <div class="stat-card stat-primary">
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