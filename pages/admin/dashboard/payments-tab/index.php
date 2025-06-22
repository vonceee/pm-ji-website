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

// Get filter parameters
$dateFrom = isset($_GET['payment_date_from']) ? $_GET['payment_date_from'] : '';
$dateTo = isset($_GET['payment_date_to']) ? $_GET['payment_date_to'] : '';

// Get active tab
$activeTab = isset($_GET['active_tab']) ? $_GET['active_tab'] : 'outstanding';

// Separate pagination parameters for each tab
$outstandingPage = isset($_GET['outstanding_page']) ? max(1, intval($_GET['outstanding_page'])) : 1;
$historyPage = isset($_GET['history_page']) ? max(1, intval($_GET['history_page'])) : 1;
$refundsPage = isset($_GET['refunds_page']) ? max(1, intval($_GET['refunds_page'])) : 1;

$itemsPerPage = 5;

// Calculate offsets for each tab
$outstandingOffset = ($outstandingPage - 1) * $itemsPerPage;
$historyOffset = ($historyPage - 1) * $itemsPerPage;
$refundsOffset = ($refundsPage - 1) * $itemsPerPage;

// Check if filters are active
$hasActiveFilters = !empty($dateFrom) || !empty($dateTo);

// Fetch Outstanding Payments data with pagination
$outstandingPayments = $paymentModel->getOutstandingPayments($dateFrom, $dateTo, '', '', '', $itemsPerPage, $outstandingOffset);
$totalOutstanding = $paymentModel->getTotalOutstandingPaymentsCount($dateFrom, $dateTo, '', '', '');

// Prepare pagination data for outstanding payments
$outstandingPaginationData = [
    'current_page' => $outstandingPage,
    'total_pages' => ceil($totalOutstanding / $itemsPerPage),
    'total_items' => $totalOutstanding,
    'has_previous' => $outstandingPage > 1,
    'has_next' => $outstandingPage < ceil($totalOutstanding / $itemsPerPage),
    'previous_page' => $outstandingPage - 1,
    'next_page' => $outstandingPage + 1,
    'page_param' => 'outstanding_page',
    'tab_name' => 'outstanding'
];

// Fetch History Payments data with pagination
$historyPayments = $paymentModel->getAllPaymentsHistory($itemsPerPage, $historyOffset, [
    'date_from' => $dateFrom,
    'date_to' => $dateTo
]);
$totalHistory = $paymentModel->getTotalPaymentsCount([
    'date_from' => $dateFrom,
    'date_to' => $dateTo
]);

// Prepare pagination data for history payments
$historyPaginationData = [
    'current_page' => $historyPage,
    'total_pages' => ceil($totalHistory / $itemsPerPage),
    'total_items' => $totalHistory,
    'has_previous' => $historyPage > 1,
    'has_next' => $historyPage < ceil($totalHistory / $itemsPerPage),
    'previous_page' => $historyPage - 1,
    'next_page' => $historyPage + 1,
    'page_param' => 'history_page',
    'tab_name' => 'history'
];

// Fetch Refund Payments data with pagination
$refundPayments = $refundModel->getAllRefundPayments($dateFrom, $dateTo, $itemsPerPage, $refundsOffset);
$totalRefunds = $refundModel->getTotalRefundPaymentsCount($dateFrom, $dateTo);

// Prepare pagination data for refund payments
$refundsPaginationData = [
    'current_page' => $refundsPage,
    'total_pages' => ceil($totalRefunds / $itemsPerPage),
    'total_items' => $totalRefunds,
    'has_previous' => $refundsPage > 1,
    'has_next' => $refundsPage < ceil($totalRefunds / $itemsPerPage),
    'previous_page' => $refundsPage - 1,
    'next_page' => $refundsPage + 1,
    'page_param' => 'refunds_page',
    'tab_name' => 'refunds'
];

$pendingRefunds = $refundPayments;

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
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/pagination/pagination.css">

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
                <a href="?view=payments&active_tab=<?= htmlspecialchars($activeTab) ?>" class="btn btn-outline-secondary">
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
                <button class="tab-button <?= $activeTab === 'outstanding' ? 'active' : '' ?>" 
                        onclick="switchTab('outstanding')" data-tab="outstanding">
                    <i class="fas fa-exclamation-triangle"></i> Outstanding Payments
                    <?php if ($totalOutstanding > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $totalOutstanding ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-button <?= $activeTab === 'history' ? 'active' : '' ?>" 
                        onclick="switchTab('history')" data-tab="history">
                    <i class="fas fa-history"></i> Payments History
                    <?php if ($totalHistory > 0): ?>
                        <span class="badge bg-secondary" style="color: white"><?= $totalHistory ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-button <?= $activeTab === 'refunds' ? 'active' : '' ?>" 
                        onclick="switchTab('refunds')" data-tab="refunds">
                    <i class="fas fa-undo"></i> Payment Refunds
                    <?php if ($totalRefunds > 0): ?>
                        <span class="badge bg-danger" style="color: white;"><?= $totalRefunds ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Outstanding Payments Tab -->
            <div id="outstanding-tab" class="tab-content <?= $activeTab === 'outstanding' ? 'active' : '' ?>">
                <section class="outstanding-payments-section">
                    <div class="section-header">
                        <h5>Outstanding Payments</h5>
                    </div>

                    <?php if (empty($outstandingPayments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-money-check-alt"></i>
                            <h4><?= $hasActiveFilters ? 'No Outstanding Payments Found' : 'No Outstanding Payments' ?></h4>
                            <?php if ($hasActiveFilters): ?>
                                <p>Try adjusting your filter criteria or <a href="?view=payments&active_tab=outstanding">clear all filters</a>.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/outstanding-payments.php'; ?>
                        
                        <!-- Pagination for Outstanding Payments -->
                        <?php 
                        $paginationData = $outstandingPaginationData;
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/pagination/index.php'; 
                        ?>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Payments History Tab -->
            <div id="history-tab" class="tab-content <?= $activeTab === 'history' ? 'active' : '' ?>">
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
                            
                            <!-- Pagination for History Payments -->
                            <?php 
                            $paginationData = $historyPaginationData;
                            require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/pagination/index.php'; 
                            ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <h4><?= $hasActiveFilters ? 'No Payment History Found' : 'No Payment History' ?></h4>
                                <?php if ($hasActiveFilters): ?>
                                    <p>Try adjusting your filter criteria or <a href="?view=payments&active_tab=history">clear all filters</a>.</p>
                                <?php else: ?>
                                    <p>No payments have been recorded yet.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Refunds Tab -->
            <div id="refunds-tab" class="tab-content <?= $activeTab === 'refunds' ? 'active' : '' ?>">
                <section class="refunds-section">
                    <div class="section-header">
                        <h5>Payment Refunds</h5>
                    </div>

                    <div id="refunds-loading" class="text-center py-4" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Loading Refunds...
                    </div>

                    <div id="refunds-container">
                        <?php if (!empty($pendingRefunds)): ?>
                            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/refund-payments.php'; ?>
                            
                            <!-- Pagination for Refund Payments -->
                            <?php 
                            $paginationData = $refundsPaginationData;
                            require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/pagination/index.php'; 
                            ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-undo"></i>
                                <h4><?= $hasActiveFilters ? 'No Refund Payments Found' : 'No Refund Payments' ?></h4>
                                <?php if ($hasActiveFilters): ?>
                                    <p>Try adjusting your filter criteria or <a href="?view=payments&active_tab=refunds">clear all filters</a>.</p>
                                <?php else: ?>
                                    <p>No refund payments have been recorded yet.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>

        <!-- Payment Filter Modal -->
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
                        <!-- Maintain the view parameter and active tab -->
                        <input type="hidden" name="view" value="payments">
                        <input type="hidden" name="active_tab" value="<?= htmlspecialchars($activeTab) ?>">
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
                            <a href="?view=payments&active_tab=<?= htmlspecialchars($activeTab) ?>" class="btn btn-outline-secondary">Clear All</a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/pagination/pagination.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-outstanding-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-history-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-refund-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payment-date-range-helper.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payment-print-report.js"></script>
        
        <script>
            // Tab switching with state preservation
            function switchTab(tabName) {
                // Remove active class from all tabs and buttons
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.classList.remove('active');
                });
                
                // Add active class to selected tab and button
                document.getElementById(tabName + '-tab').classList.add('active');
                document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
                
                // Update URL to preserve tab state
                const url = new URL(window.location);
                url.searchParams.set('active_tab', tabName);
                window.history.replaceState({}, '', url);
                
                // Update form hidden input for active tab
                const filterForm = document.getElementById('paymentFilterForm');
                if (filterForm) {
                    const activeTabInput = filterForm.querySelector('input[name="active_tab"]');
                    if (activeTabInput) {
                        activeTabInput.value = tabName;
                    }
                }
            }
            
            // Initialize tab state on page load
            document.addEventListener('DOMContentLoaded', function() {
                const activeTab = '<?= $activeTab ?>';
                if (activeTab) {
                    switchTab(activeTab);
                }
            });
        </script>
    </div>
</body>

</html>