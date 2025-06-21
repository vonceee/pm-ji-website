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
        <?php include ROOT_PATH . '/pages/admin/dashboard/payments-tab/components/alerts/index.php'; ?>

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
                    <?php if (!empty($filters['dateFrom'])): ?>
                        <span class="badge bg-info me-2">
                            From: <?= date('M d, Y', strtotime($filters['dateFrom'])) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['dateTo'])): ?>
                        <span class="badge bg-info me-2">
                            To: <?= date('M d, Y', strtotime($filters['dateTo'])) ?>
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
                <?php include ROOT_PATH . '/pages/admin/dashboard/payments-tab/views/payments/tabs/outstanding.php'; ?>
            </div>

            <!-- Payments History Tab -->
            <div id="history-tab" class="tab-content">
                <?php include ROOT_PATH . '/views/payments/tabs/history.php'; ?>
            </div>

            <!-- Refunds Tab -->
            <div id="refunds-tab" class="tab-content">
                <?php include ROOT_PATH . '/views/payments/tabs/refunds.php'; ?>
            </div>
        </div>

        <!-- Payment Filter Modal -->
        <?php include ROOT_PATH . '/views/payments/modals/filter-modal.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/assets/js/payments/outstanding-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/assets/js/payments/history-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/assets/js/payments/refund-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/assets/js/payments/date-range-helper.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/assets/js/payments/print-report.js"></script>

        <!-- Debug output for refund data -->
        <script>console.log('Refund Payments Data:', <?= json_encode($refundPayments) ?>);</script>
    </div>
</body>

</html>