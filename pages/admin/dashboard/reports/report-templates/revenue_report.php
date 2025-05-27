<?php
// pages/admin/reports/report-templates/revenue_report.php
$data = $reportData;
$revenue_data = $data['revenue_data'] ?? [];
$summary = $data['summary'] ?? [];
$monthly_breakdown = $data['monthly_breakdown'] ?? [];
$event_type_revenue = $data['event_type_revenue'] ?? [];
$payment_method_stats = $data['payment_method_stats'] ?? [];
?>

<!-- Revenue Summary Cards -->
<div class="report-section">
    <div class="section-header">
        <h3 class="section-title ml-2">
            <i class="fas fa-chart-line me-2"></i>Revenue Overview
        </h3>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center" style="margin-bottom: 28px;">
                    <i class="fas fa-peso-sign text-primary fa-2x mb-2"></i>
                    <h4 class="text-primary mb-1">₱<?= number_format($summary['total_revenue'] ?? 0, 2) ?></h4>
                    <small class="text-muted">Total Revenue</small>
                    <?php if (isset($summary['revenue_growth'])): ?>
                        <div class="mt-2">
                            <span class="badge <?= $summary['revenue_growth'] >= 0 ? 'bg-success' : 'bg-danger' ?>">
                                <i class="fas <?= $summary['revenue_growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                                <?= abs($summary['revenue_growth']) ?>%
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card text-success fa-2x mb-2"></i>
                    <h4 class="text-success mb-1">₱<?= number_format($summary['paid_amount'] ?? 0, 2) ?></h4>
                    <small class="text-muted">Amount Collected</small>
                    <div class="mt-2">
                        <small class="text-success">
                            <?= round(($summary['paid_amount'] ?? 0) / max($summary['total_revenue'] ?? 1, 1) * 100, 1) ?>%
                            Collection Rate
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                    <h4 class="text-warning mb-1">₱<?= number_format($summary['partial_amount'] ?? 0, 2) ?></h4>
                    <small class="text-muted">Pending Payments</small>
                    <div class="mt-2">
                        <small class="text-muted">
                            <?= $summary['pending_bookings'] ?? 0 ?> bookings
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calculator text-info fa-2x mb-2"></i>
                    <h4 class="text-info mb-1">₱<?= number_format($summary['average_booking_value'] ?? 0, 2) ?></h4>
                    <small class="text-muted">Avg. Booking Value</small>
                    <div class="mt-2">
                        <small class="text-muted">
                            <?= $summary['total_bookings'] ?? 0 ?> total bookings
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Breakdown -->
<?php if (!empty($monthly_breakdown)): ?>
    <div class="report-section">
        <div class="section-header">
            <h3 class="section-title ml-2">
                <i class="fas fa-calendar-alt me-2"></i>Monthly Revenue Breakdown
            </h3>
        </div>

        <div class="report-table">
            <table class="table table-striped" id="monthlyRevenueTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'monthlyRevenueTable')" style="cursor: pointer;">
                            Month <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(1, 'monthlyRevenueTable')" style="cursor: pointer;">
                            Bookings <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(2, 'monthlyRevenueTable')" style="cursor: pointer;">
                            Total Revenue <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(3, 'monthlyRevenueTable')" style="cursor: pointer;">
                            Collected <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(4, 'monthlyRevenueTable')" style="cursor: pointer;">
                            Pending <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(5, 'monthlyRevenueTable')" style="cursor: pointer;">
                            Collection Rate <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(6, 'monthlyRevenueTable')" style="cursor: pointer;">
                            Avg. Value <i class="fas fa-sort"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_breakdown as $month_data): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($month_data['month_year']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= $month_data['booking_count'] ?></span>
                            </td>
                            <td>
                                <strong class="text-currency">₱<?= number_format($month_data['total_revenue'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="text-success">₱<?= number_format($month_data['collected_amount'], 2) ?></span>
                            </td>
                            <td>
                                <span class="text-warning">₱<?= number_format($month_data['pending_amount'], 2) ?></span>
                            </td>
                            <td>
                                <?php
                                $collection_rate = $month_data['total_revenue'] > 0
                                    ? ($month_data['collected_amount'] / $month_data['total_revenue'] * 100)
                                    : 0;
                                $rate_class = $collection_rate >= 80 ? 'text-success' : ($collection_rate >= 60 ? 'text-warning' : 'text-danger');
                                ?>
                                <span class="<?= $rate_class ?>"><?= round($collection_rate, 1) ?>%</span>
                            </td>
                            <td>
                                <span class="text-info">₱<?= number_format($month_data['avg_booking_value'], 2) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Event Type Revenue Analysis -->
<?php if (!empty($event_type_revenue)): ?>
    <div class="report-section">
        <div class="section-header">
            <h3 class="section-title ml-2">
                <i class="fas fa-chart-pie me-2"></i>Revenue by Event Type
            </h3>
        </div>

        <div class="report-table">
            <table class="table table-striped" id="eventTypeRevenueTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'eventTypeRevenueTable')" style="cursor: pointer;">
                            Event Type <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(1, 'eventTypeRevenueTable')" style="cursor: pointer;">
                            Bookings <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(2, 'eventTypeRevenueTable')" style="cursor: pointer;">
                            Total Revenue <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(3, 'eventTypeRevenueTable')" style="cursor: pointer;">
                            Avg. Price <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(4, 'eventTypeRevenueTable')" style="cursor: pointer;">
                            Revenue Share <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(5, 'eventTypeRevenueTable')" style="cursor: pointer;">
                            Collection Rate <i class="fas fa-sort"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($event_type_revenue as $event_data): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($event_data['event_type']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($event_data['description'] ?? '') ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $event_data['booking_count'] ?></span>
                            </td>
                            <td>
                                <strong class="text-currency">₱<?= number_format($event_data['total_revenue'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="text-primary">₱<?= number_format($event_data['avg_price'], 2) ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar" style="width: <?= $event_data['revenue_percentage'] ?>%">
                                        </div>
                                    </div>
                                    <small><?= round($event_data['revenue_percentage'], 1) ?>%</small>
                                </div>
                            </td>
                            <td>
                                <?php
                                $collection_rate = $event_data['collection_rate'] ?? 0;
                                $rate_class = $collection_rate >= 80 ? 'text-success' : ($collection_rate >= 60 ? 'text-warning' : 'text-danger');
                                ?>
                                <span class="<?= $rate_class ?>"><?= round($collection_rate, 1) ?>%</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Payment Method Statistics -->
<?php if (!empty($payment_method_stats)): ?>
    <div class="report-section">
        <div class="section-header">
            <h3 class="section-title ml-2">
                <i class="fas fa-credit-card me-2"></i>Payment Method Analysis
            </h3>
        </div>

        <div class="row">
            <?php foreach ($payment_method_stats as $payment_data): ?>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="card-title mb-1" style="text-transform: uppercase;">
                                        <?= htmlspecialchars($payment_data['payment_method']) ?>
                                    </h6>
                                    <small class="text-muted"><?= $payment_data['transaction_count'] ?> Transactions</small>
                                </div>
                                <i class="fas <?= $payment_data['icon'] ?? 'fa-money-bill' ?> text-primary"></i>
                            </div>
                            <div class="mb-2">
                                <strong class="text-primary">₱<?= number_format($payment_data['total_amount'], 2) ?></strong>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar" style="width: <?= $payment_data['percentage'] ?>%"></div>
                            </div>
                            <small class="text-muted"><?= round($payment_data['percentage'], 1) ?>% of Total Revenue</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Detailed Revenue Transactions -->
<?php if (!empty($revenue_data)): ?>
    <div class="report-section">
        <div class="section-header">
            <h3 class="section-title ml-2">
                <i class="fas fa-list-alt me-2"></i>Revenue Transactions
            </h3>
        </div>

        <div class="report-table">
            <table class="table table-striped" id="revenueTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'revenueTable')" style="cursor: pointer;">
                            Transaction Date <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(1, 'revenueTable')" style="cursor: pointer;">
                            Reference ID <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(2, 'revenueTable')" style="cursor: pointer;">
                            Client <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(3, 'revenueTable')" style="cursor: pointer;">
                            Event Type <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(4, 'revenueTable')" style="cursor: pointer;">
                            Event Date <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(5, 'revenueTable')" style="cursor: pointer;">
                            Payment Method <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(6, 'revenueTable')" style="cursor: pointer;">
                            Amount <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(7, 'revenueTable')" style="cursor: pointer;">
                            Status <i class="fas fa-sort"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenue_data as $transaction): ?>
                        <tr>
                            <td>
                                <strong><?= date('M d, Y', strtotime($transaction['payment_date'])) ?></strong>
                                <br>
                                <small class="text-muted"><?= date('g:i A', strtotime($transaction['payment_date'])) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($transaction['reference_id']) ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($transaction['client_name']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($transaction['client_email']) ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($transaction['event_type']) ?></span>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($transaction['event_date'])) ?>
                            </td>
                            <td>
                                <div>
                                    <span><?= htmlspecialchars($transaction['payment_method']) ?></span>
                                    <?php if (!empty($transaction['transaction_id'])): ?>
                                        <br>
                                        <small class="text-muted">ID:
                                            <?= htmlspecialchars($transaction['transaction_id']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <strong class="text-success">₱<?= number_format($transaction['amount'], 2) ?></strong>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch ($transaction['payment_status']) {
                                    case 'paid':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'partial':
                                        $statusClass = 'bg-warning';
                                        break;
                                    case 'refunded':
                                        $statusClass = 'bg-info';
                                        break;
                                    case 'failed':
                                        $statusClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>">
                                    <?= ucfirst($transaction['payment_status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Table Summary -->
            <div class="table-summary mt-3 p-3 bg-light">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Total Transactions: <?= count($revenue_data) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <strong>Total Revenue: ₱<?= number_format($summary['total_revenue'], 2) ?></strong>
                    </div>
                    <div class="col-md-4 text-end">
                        <strong>Amount Collected: ₱<?= number_format($summary['paid_amount'], 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="mt-3">
            <button type="button" class="btn btn-primary ml-2" onclick="printTableOnly()">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
            <button type="button" class="btn btn-success ml-2"
                onclick="exportTableToCSV('revenueTable', 'revenue-report.csv')">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </button>
        </div>

    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <h5>No Revenue Data Found</h5>
            <p>No revenue transactions match the selected criteria for the specified date range.</p>
            <div class="mt-3">
                <button type="button" class="btn btn-primary" onclick="resetFilters()">
                    <i class="fas fa-redo me-2"></i>Reset Filters
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>