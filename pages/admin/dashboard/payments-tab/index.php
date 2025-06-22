<?php

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// fetch the shared PDO instance
$pdo = Database::getConnection();

// payment model
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentOutstandingsModel.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentRefundsModel.php';

$paymentModel = new \Models\PaymentOutstandingsModel($pdo);
$refundModel = new \Models\PaymentRefundsModel($pdo);

// Get filter parameters - only date filters
$dateFrom = isset($_GET['payment_date_from']) ? $_GET['payment_date_from'] : '';
$dateTo = isset($_GET['payment_date_to']) ? $_GET['payment_date_to'] : '';

// Check if filters are active - only date filters
$hasActiveFilters = !empty($dateFrom) || !empty($dateTo);


// Apply filters to data fetching - only date filters, pass empty strings for removed filters
$outstandingPayments = $paymentModel->getOutstandingPayments($dateFrom, $dateTo, '', '', '');
$historyPayments = $paymentModel->getAllPaymentsHistory(20, 0, $dateFrom, $dateTo, '', '');

// Fix: Assign refund data to the correct variable name used in the template
$refundPayments = $refundModel->getAllRefundPayments($dateFrom, $dateTo);
$pendingRefunds = $refundPayments; // This variable name is used in the template

echo "<script>console.log('Refund Payments Data:', " . json_encode($refundPayments) . ");</script>";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Payment Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-tab.css">

    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <div class="container-fluid">
        <!-- Alert Messages -->
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/alerts/index.php'; ?>

        <!-- Page Header -->
        <div class="payment-header">
            <h4>Payment Management</h4>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="showPaymentPrintReportModal()">
                            <i class="fas fa-calendar-alt me-2"></i>Custom Date Range Report
                        </a></li>
                    <li><a class="dropdown-item" href="#" onclick="printCurrentPaymentPage()">
                            <i class="fas fa-eye me-2"></i>Print Current View
                        </a></li>
                </ul>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentFilterModal">
                <i class="fas fa-filter me-1"></i> Filter
                <?php if ($hasActiveFilters): ?>
                    <span class="badge bg-warning text-dark ms-1">Active</span>
                <?php endif; ?>
            </button>
            <?php if ($hasActiveFilters): ?>
                <a href="?view=payments" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear Filters
                </a>
            <?php endif; ?>
        </div>

        <!-- Active Filters Display -->
        <?php if ($hasActiveFilters): ?>
            <div class="active-filters mb-3">
                <h6 class="mb-2">Active Filters:</h6>
                <div class="filter-tags">
                    <?php if (!empty($dateFrom)): ?>
                        <span class="badge bg-info me-2">
                            From: <?= date('M d, Y', strtotime($dateFrom)) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($dateTo)): ?>
                        <span class="badge bg-info me-2">
                            To: <?= date('M d, Y', strtotime($dateTo)) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" onclick="switchTab('outstanding')">
                    <i class="fas fa-exclamation-triangle"></i> Outstanding Payments
                    <?php if (!empty($outstandingPayments)): ?>
                        <span class="badge bg-warning text-dark"><?= count($outstandingPayments) ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-button" onclick="switchTab('history')">
                    <i class="fas fa-history"></i> Payments History
                    <?php if (!empty($historyPayments)): ?>
                        <span class="badge bg-secondary" style="color: white"><?= count($historyPayments) ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-button" onclick="switchTab('refunds')">
                    <i class="fas fa-undo"></i> Payment Refunds
                    <?php if (!empty($pendingRefunds)): ?>
                        <span class="badge bg-danger" style="color: white;"><?= count($pendingRefunds) ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Outstanding Payments Tab -->
            <div id="outstanding-tab" class="tab-content active">
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
                        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/outstanding-payments.php'; ?>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Payments History Tab -->
            <div id="history-tab" class="tab-content">
                <section class="payments-history-section">
                    <div class="section-header">
                        <h5>Payments History</h5>
                    </div>

                    <div id="history-loading" class="text-center py-4" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Loading Payment History...
                    </div>

                    <div id="payment-history-container">
                        <?php if (!empty($historyPayments)): ?>
                            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/history-payments.php'; ?>
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
            </div>

            <!-- Refunds Tab -->
            <div id="refunds-tab" class="tab-content">
                <section class="refunds-section">
                    <div class="section-header">
                        <h5>Pending Refunds</h5>
                    </div>

                    <div id="refunds-loading" class="text-center py-4" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Loading Refunds...
                    </div>

                    <div id="refunds-container">
                        <?php if (!empty($pendingRefunds)): ?>
                            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/refund-payments.php'; ?>
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
            </div>
        </div>

        <!-- Payment Filter Modal - Simplified to only show date filters -->
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
                        <!-- Maintain the view parameter to stay on payments page -->
                        <input type="hidden" name="view" value="payments">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="payment_date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="payment_date_from"
                                        name="payment_date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="payment_date_to" name="payment_date_to"
                                        value="<?= htmlspecialchars($dateTo) ?>">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <label class="form-label">Quick Date Ranges</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="setPaymentDateRange('today')">
                                                    Today
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="setPaymentDateRange('this_week')">
                                                    This Week
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="setPaymentDateRange('this_month')">
                                                    This Month
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="setPaymentDateRange('last_30_days')">
                                                    Last 30 Days
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="setPaymentDateRange('last_3_months')">
                                                    Last 3 Months
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="setPaymentDateRange('this_year')">
                                                    This Year
                                                </button>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script
            src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-outstanding-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-history-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-refund-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payment-date-range-helper.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payment-print-report.js"></script>


    </div>
</body>

</html>