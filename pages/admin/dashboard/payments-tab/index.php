<?php
// Complete the payments-tab/index.php file - fixing the incomplete HTML and adding missing functionality

// Database connection and model initialization (keeping your existing code)
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;
$pdo = Database::getConnection();

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentOutstandingsModel.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentRefundsModel.php';

$paymentModel = new \Models\PaymentOutstandingsModel($pdo);
$refundModel = new \Models\PaymentRefundsModel($pdo);

// Get filter parameters
$dateFrom = isset($_GET['payment_date_from']) ? $_GET['payment_date_from'] : '';
$dateTo = isset($_GET['payment_date_to']) ? $_GET['payment_date_to'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination parameters - following bookings-tab pattern
$tab = $_GET['tab'] ?? 'outstanding';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Set the current page for each tab, only the active tab uses the actual page number
$outstandingPage = ($tab === 'outstanding') ? $page : 1;
$historyPage = ($tab === 'history') ? $page : 1;
$refundsPage = ($tab === 'refunds') ? $page : 1;

// Determine which tab is currently active and set the current page accordingly
$activeTab = $_GET['tab'] ?? 'outstanding';
$currentPage = $activeTab === 'history' ? $historyPage : ($activeTab === 'refunds' ? $refundsPage : $outstandingPage);

// Items per page
$limit = 10;
$offset = ($currentPage - 1) * $limit;

// Check if filters are active
$hasActiveFilters = !empty($dateFrom) || !empty($dateTo) || !empty($search);

// Handle AJAX requests (keeping your existing AJAX handling code)
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) ||
    ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']))) {
    // Your existing AJAX handling code here...
    exit;
}

// Get data for all tabs with counts
$outstandingPayments = $paymentModel->getOutstandingPayments($dateFrom, $dateTo, '', '', $search, $limit, ($outstandingPage - 1) * $limit);
$outstandingCount = $paymentModel->getOutstandingPaymentsCount($dateFrom, $dateTo, '', '', $search);

$historyPayments = $paymentModel->getAllPaymentsHistory($limit, ($historyPage - 1) * $limit, $dateFrom, $dateTo, '', '', $search);
$historyCount = $paymentModel->getTotalPaymentsCount($dateFrom, $dateTo, '', '', $search);

$refundPayments = $refundModel->getAllRefundPayments($dateFrom, $dateTo, $search, $limit, ($refundsPage - 1) * $limit);
$refundCount = $refundModel->getRefundPaymentsCount($dateFrom, $dateTo, $search);

// Generate pagination data for each tab (following bookings-tab pattern)
function getPaginationData($totalCount, $currentPage, $itemsPerPage = 10) {
    $totalPages = ceil($totalCount / $itemsPerPage);
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $totalCount,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage - 1,
        'next_page' => $currentPage + 1
    ];
}

$outstandingPagination = getPaginationData($outstandingCount, $outstandingPage);
$historyPagination = getPaginationData($historyCount, $historyPage);
$refundsPagination = getPaginationData($refundCount, $refundsPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Payment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-tab.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/pagination/pagination.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <div class="container-fluid">
        <!-- Alert Messages -->
        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/alerts/index.php')): ?>
            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/alerts/index.php'; ?>
        <?php endif; ?>

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
                <a href="?view=payments&tab=<?= $activeTab ?>" class="btn btn-outline-secondary">
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
                    <?php if (!empty($search)): ?>
                        <span class="badge bg-info me-2">
                            Search: "<?= htmlspecialchars($search) ?>"
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Tabs -->
        <ul class="nav nav-tabs payment-tabs" id="paymentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'outstanding' ? 'active' : '' ?>" 
                        id="outstanding-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#outstanding" 
                        type="button" 
                        role="tab" 
                        aria-controls="outstanding"
                        aria-selected="<?= $activeTab === 'outstanding' ? 'true' : 'false' ?>"
                        onclick="setActivePaymentTab('outstanding')">
                    <i class="fas fa-exclamation-triangle me-2"></i>Outstanding Payments
                    <?php if ($outstandingCount > 0): ?>
                        <span class="badge bg-danger ms-2"><?= $outstandingCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'history' ? 'active' : '' ?>" 
                        id="history-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#history" 
                        type="button" 
                        role="tab" 
                        aria-controls="history"
                        aria-selected="<?= $activeTab === 'history' ? 'true' : 'false' ?>"
                        onclick="setActivePaymentTab('history')">
                    <i class="fas fa-history me-2"></i>Payment History
                    <?php if ($historyCount > 0): ?>
                        <span class="badge bg-secondary ms-2"><?= $historyCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'refunds' ? 'active' : '' ?>" 
                        id="refunds-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#refunds" 
                        type="button" 
                        role="tab" 
                        aria-controls="refunds"
                        aria-selected="<?= $activeTab === 'refunds' ? 'true' : 'false' ?>"
                        onclick="setActivePaymentTab('refunds')">
                    <i class="fas fa-undo me-2"></i>Refund Requests
                    <?php if ($refundCount > 0): ?>
                        <span class="badge bg-warning ms-2"><?= $refundCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="paymentTabContent">
            <!-- Outstanding Payments Tab -->
            <div class="tab-pane fade <?= $activeTab === 'outstanding' ? 'show active' : '' ?>" 
                 id="outstanding" 
                 role="tabpanel" 
                 aria-labelledby="outstanding-tab">
                <?php if (empty($outstandingPayments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-money-bill-wave"></i>
                        <h4><?= $hasActiveFilters ? 'No Outstanding Payments Found' : 'No Outstanding Payments' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=payments&tab=outstanding">clear all filters</a>.</p>
                        <?php else: ?>
                            <p>All payments are up to date!</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference #</th>
                                    <th>Customer</th>
                                    <th>Event</th>
                                    <th>Amount Due</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($outstandingPayments as $payment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($payment['reference_number']) ?></td>
                                        <td>
                                            <div class="customer-info">
                                                <strong><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($payment['email']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="event-info">
                                                <strong><?= htmlspecialchars($payment['event_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= date('M d, Y', strtotime($payment['event_date'])) ?></small>
                                            </div>
                                        </td>
                                        <td>₱<?= number_format($payment['amount_due'], 2) ?></td>
                                        <td>₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                        <td class="text-danger">
                                            <strong>₱<?= number_format($payment['remaining_balance'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $dueDate = new DateTime($payment['due_date']);
                                            $now = new DateTime();
                                            $isOverdue = $dueDate < $now;
                                            ?>
                                            <span class="<?= $isOverdue ? 'text-danger' : 'text-muted' ?>">
                                                <?= $dueDate->format('M d, Y') ?>
                                                <?php if ($isOverdue): ?>
                                                    <br><small class="badge bg-danger">Overdue</small>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $payment['payment_status'] === 'pending' ? 'warning' : 'info' ?>">
                                                <?= ucfirst($payment['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-info" 
                                                        onclick="viewPaymentDetails(<?= $payment['payment_id'] ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" 
                                                        onclick="markAsPaid(<?= $payment['payment_id'] ?>)"
                                                        title="Mark as Paid">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for Outstanding Tab -->
                    <?php if ($activeTab === 'outstanding'): ?>
                        <?php
                        $paginationData = $outstandingPagination;
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/pagination/index.php';
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Payment History Tab -->
            <div class="tab-pane fade <?= $activeTab === 'history' ? 'show active' : '' ?>" 
                 id="history" 
                 role="tabpanel" 
                 aria-labelledby="history-tab">
                <?php if (empty($historyPayments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h4><?= $hasActiveFilters ? 'No Payment History Found' : 'No Payment History' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=payments&tab=history">clear all filters</a>.</p>
                        <?php else: ?>
                            <p>No payment records found.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference #</th>
                                    <th>Customer</th>
                                    <th>Event</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyPayments as $payment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($payment['reference_number']) ?></td>
                                        <td>
                                            <div class="customer-info">
                                                <strong><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($payment['email']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="event-info">
                                                <strong><?= htmlspecialchars($payment['event_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= date('M d, Y', strtotime($payment['event_date'])) ?></small>
                                            </div>
                                        </td>
                                        <td>₱<?= number_format($payment['amount_due'], 2) ?></td>
                                        <td><?= ucfirst($payment['payment_method'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $payment['payment_status'] === 'paid' ? 'success' : 
                                                ($payment['payment_status'] === 'pending' ? 'warning' : 'info') 
                                            ?>">
                                                <?= ucfirst($payment['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y g:i A', strtotime($payment['updated_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-outline-info btn-sm" 
                                                    onclick="viewPaymentDetails(<?= $payment['payment_id'] ?>)"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for History Tab -->
                    <?php if ($activeTab === 'history'): ?>
                        <?php
                        $paginationData = $historyPagination;
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/pagination/index.php';
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Refund Requests Tab -->
            <div class="tab-pane fade <?= $activeTab === 'refunds' ? 'show active' : '' ?>" 
                 id="refunds" 
                 role="tabpanel" 
                 aria-labelledby="refunds-tab">
                <?php if (empty($refundPayments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-undo"></i>
                        <h4><?= $hasActiveFilters ? 'No Refund Requests Found' : 'No Refund Requests' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=payments&tab=refunds">clear all filters</a>.</p>
                        <?php else: ?>
                            <p>No refund requests found.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference #</th>
                                    <th>Customer</th>
                                    <th>Event</th>
                                    <th>Refund Amount</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Requested Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($refundPayments as $refund): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($refund['reference_number']) ?></td>
                                        <td>
                                            <div class="customer-info">
                                                <strong><?= htmlspecialchars($refund['first_name'] . ' ' . $refund['last_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($refund['email']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="event-info">
                                                <strong><?= htmlspecialchars($refund['event_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= date('M d, Y', strtotime($refund['event_date'])) ?></small>
                                            </div>
                                        </td>
                                        <td>₱<?= number_format($refund['refund_amount'], 2) ?></td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                                  title="<?= htmlspecialchars($refund['refund_reason']) ?>">
                                                <?= htmlspecialchars($refund['refund_reason']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $refund['refund_status'] === 'processed' ? 'success' : 
                                                ($refund['refund_status'] === 'pending' ? 'warning' : 'danger') 
                                            ?>">
                                                <?= ucfirst($refund['refund_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y g:i A', strtotime($refund['requested_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-info" 
                                                        onclick="viewRefundDetails(<?= $refund['refund_id'] ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($refund['refund_status'] === 'pending'): ?>
                                                    <button class="btn btn-outline-success" 
                                                            onclick="processRefund(<?= $refund['refund_id'] ?>)"
                                                            title="Process Refund">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="rejectRefund(<?= $refund['refund_id'] ?>)"
                                                            title="Reject Refund">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for Refunds Tab -->
                    <?php if ($activeTab === 'refunds'): ?>
                        <?php
                        $paginationData = $refundsPagination;
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/pagination/index.php';
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Include Filter Modal -->
    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/filter-modal/index.php')): ?>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/filter-modal/index.php'; ?>
    <?php endif; ?>

    <!-- Include Payment Modals -->
    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/payment-modals/index.php')): ?>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/payment-modals/index.php'; ?>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Include Pagination JavaScript -->
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/pagination/pagination.js"></script>
    
    <!-- Include Payment JavaScript -->
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-tab.js"></script>

    <script>
        // Payment tab management (following bookings-tab pattern)
        function setActivePaymentTab(tabName) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            url.searchParams.set('view', 'payments');
            url.searchParams.delete('page'); // Reset page when switching tabs
            window.location.href = url.toString();
        }

        // Initialize tab state from URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'outstanding';
            
            // Activate the correct tab
            const tabElement = document.getElementById(activeTab + '-tab');
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
        });

        // Payment action functions (you'll need to implement these)
        function viewPaymentDetails(paymentId) {
            // Implementation for viewing payment details
            console.log('View payment details for ID:', paymentId);
        }

        function markAsPaid(paymentId) {
            // Implementation for marking payment as paid
            console.log('Mark as paid for ID:', paymentId);
        }

        function viewRefundDetails(refundId) {
            // Implementation for viewing refund details
            console.log('View refund details for ID:', refundId);
        }

        function processRefund(refundId) {
            // Implementation for processing refund
            console.log('Process refund for ID:', refundId);
        }

        function rejectRefund(refundId) {
            // Implementation for rejecting refund
            console.log('Reject refund for ID:', refundId);
        }

        function showPaymentPrintReportModal() {
            // Implementation for print report modal
            console.log('Show print report modal');
        }

        function printCurrentPaymentPage() {
            // Implementation for printing current page
            window.print();
        }
    </script>
</body>
</html>