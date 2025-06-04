<?php

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// fetch the shared PDO instance
$pdo = Database::getConnection();

// payment model
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentModel.php';

$paymentModel = new \Models\PaymentModel($pdo);

// handle AJAX requests
if (
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) ||
    ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']))
) {
    header('Content-Type: application/json');

    try {
        $action = $_POST['action'] ?? $_GET['action'];

        switch ($action) {
            case 'mark_paid':
                $paymentId = (int) $_POST['payment_id'];
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $notes = $_POST['notes'] ?? '';

                $result = $paymentModel->markAsPaid($paymentId);
                echo json_encode(['success' => true, 'message' => 'payment marked as fully paid']);
                break;

            case 'get_payment_details':
                $paymentId = (int) $_GET['payment_id'];
                $payment = $paymentModel->getPaymentById($paymentId);

                if ($payment) {
                    echo json_encode(['success' => true, 'payment' => $payment]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'payment not found']);
                }
                break;

            case 'get_payment_history':
                $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
                $offset = ($page - 1) * $limit;

                $paymentHistory = $paymentModel->getAllPaymentsHistory($limit, $offset);
                $totalCount = $paymentModel->getTotalPaymentsCount();

                echo json_encode([
                    'success' => true,
                    'payments' => $paymentHistory,
                    'total' => $totalCount,
                    'page' => $page,
                    'hasMore' => ($offset + $limit) < $totalCount
                ]);
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// get outstanding payments and stats
$outstandingPayments = $paymentModel->getOutstandingPayments();
$paymentStats = $paymentModel->getPaymentStats();
// Get initial payment history for the tab
$paymentHistory = $paymentModel->getAllPaymentsHistory(20, 0);

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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="payment-header">
            <h4>Payment Management</h4>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" onclick="switchTab('outstanding')">
                    <i class="fas fa-exclamation-triangle"></i> Outstanding Payments
                </button>
                <button class="tab-button" onclick="switchTab('history')">
                    <i class="fas fa-history"></i> Payments History
                </button>
                <button class="tab-button" onclick="switchTab('refunds')">
                    <i class="fas fa-undo"></i> Payment Refunds
                </button>
            </div>

            <!-- Outstanding Payments Tab -->
            <div id="outstanding-tab" class="tab-content active">
                <section class="outstanding-payments-section">
                    <div class="section-header">
                        <h5>Outstanding Payments</h5>
                        <div class="btn-group">
                            <button class="btn btn-outline-info btn-sm" onclick="exportPayments()">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                        </div>
                    </div>

                    <?php if (empty($outstandingPayments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-money-check-alt"></i>
                            <h4>No Outstanding Payments</h4>
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
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary btn-sm" onclick="filterPaymentHistory()">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportPaymentHistory()">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                        </div>
                    </div>

                    <div id="history-loading" class="text-center py-4" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Loading Payment History...
                    </div>

                    <div id="payment-history-container">
                        <?php if (!empty($paymentHistory)): ?>
                            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/history-payments.php'; ?>
                            <div class="text-center mt-3">
                                <button id="load-more-history" class="btn btn-outline-primary" onclick="loadMoreHistory()">
                                    <i class="fas fa-plus me-1"></i> Load More
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <h4>No Payment History</h4>
                                <p>No payments have been recorded yet.</p>
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
                        <div class="btn-group">
                            <button class="btn btn-outline-info btn-sm" onclick="exportRefunds()">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                        </div>
                    </div>

                    <div id="refunds-loading" class="text-center py-4" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Loading Refunds...
                    </div>

                    <div id="refunds-container">
                        <?php if (!empty($pendingRefunds)): ?>
                            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/refunds.php'; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-undo"></i>
                            <h4>No Pending Refunds</h4>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script
            src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-outstanding-management.js"></script>
        <script
            src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-history-management.js"></script>


</body>

</html>