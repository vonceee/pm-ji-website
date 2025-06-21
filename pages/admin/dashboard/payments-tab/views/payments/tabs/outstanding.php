<?php
// views/payments/tabs/outstanding.php
?>
<section class="outstanding-payments-section">
    <div class="section-header">
        <h5>Outstanding Payments</h5>
    </div>

    <?php if (empty($outstandingPayments)): ?>
        <div class="empty-state">
            <i class="fas fa-money-check-alt"></i>
            <h4><?= $hasActiveFilters ? 'No Outstanding Payments Found' : 'No Outstanding Payments' ?></h4>
            <?php if ($hasActiveFilters): ?>
                <p>Try adjusting your filter criteria or <a href="?view=payments">clear all filters</a>.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php include ROOT_PATH . '/views/payments/components/outstanding-table.php'; ?>
    <?php endif; ?>
</section>

<?php
// views/payments/tabs/history.php
?>
<section class="payments-history-section">
    <div class="section-header">
        <h5>Payments History</h5>
    </div>

    <div id="history-loading" class="text-center py-4" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i> Loading Payment History...
    </div>

    <div id="payment-history-container">
        <?php if (!empty($historyPayments)): ?>
            <?php include ROOT_PATH . '/views/payments/components/history-table.php'; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h4><?= $hasActiveFilters ? 'No Payment History Found' : 'No Payment History' ?></h4>
                <?php if ($hasActiveFilters): ?>
                    <p>Try adjusting your filter criteria or <a href="?view=payments">clear all filters</a>.</p>
                <?php else: ?>
                    <p>No payments have been recorded yet.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// views/payments/tabs/refunds.php
?>
<section class="refunds-section">
    <div class="section-header">
        <h5>Pending Refunds</h5>
    </div>

    <div id="refunds-loading" class="text-center py-4" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i> Loading Refunds...
    </div>

    <div id="refunds-container">
        <?php if (!empty($pendingRefunds)): ?>
            <?php include ROOT_PATH . '/views/payments/components/refunds-table.php'; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-undo"></i>
                <h4><?= $hasActiveFilters ? 'No Pending Refunds Found' : 'No Pending Refunds' ?></h4>
                <?php if ($hasActiveFilters): ?>
                    <p>Try adjusting your filter criteria or <a href="?view=payments">clear all filters</a>.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// views/payments/modals/filter-modal.php
?>
<div class="modal fade" id="paymentFilterModal" tabindex="-1" aria-labelledby="paymentFilterModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentFilterModalLabel">
                    <i class="fas fa-filter me-2"></i>Filter Payments by Date
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" id="paymentFilterForm">
                <input type="hidden" name="view" value="payments">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="payment_date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="payment_date_from"
                                name="payment_date_from" value="<?= htmlspecialchars($filters['dateFrom']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="payment_date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="payment_date_to" name="payment_date_to"
                                value="<?= htmlspecialchars($filters['dateTo']) ?>">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <label class="form-label">Quick Date Ranges</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="setPaymentDateRange('today')">Today</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="setPaymentDateRange('this_week')">This Week</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="setPaymentDateRange('this_month')">This Month</button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="setPaymentDateRange('last_30_days')">Last 30 Days</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="setPaymentDateRange('last_3_months')">Last 3 Months</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="setPaymentDateRange('this_year')">This Year</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?view=payments" class="btn btn-outline-secondary">Clear All</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>