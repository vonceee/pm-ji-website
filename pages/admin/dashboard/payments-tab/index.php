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

                $historyPayments = $paymentModel->getAllPaymentsHistory($limit, $offset);
                $totalCount = $paymentModel->getTotalPaymentsCount();

                echo json_encode([
                    'success' => true,
                    'payments' => $historyPayments,
                    'total' => $totalCount,
                    'page' => $page,
                    'hasMore' => ($offset + $limit) < $totalCount
                ]);
                break;

            case 'process_refund':
                $refundId = (int) $_POST['refund_id'];
                $refundMethod = $_POST['refund_method'] ?? '';
                $adminNotes = $_POST['admin_notes'] ?? '';
                $refundReference = $_POST['refund_reference'] ?? '';

                if (empty($refundMethod)) {
                    throw new Exception('Refund method is required');
                }

                $result = $refundModel->processRefund($refundId, $refundMethod, $adminNotes, $refundReference);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Refund has been processed successfully'
                    ]);
                } else {
                    throw new Exception('Failed to process refund');
                }
                break;

            case 'reject_refund':
                $refundId = (int) $_POST['refund_id'];
                $rejectionReason = $_POST['rejection_reason'] ?? '';
                $rejectionNotes = $_POST['rejection_notes'] ?? '';

                if (empty($rejectionReason)) {
                    throw new Exception('Rejection reason is required');
                }

                $result = $refundModel->rejectRefund($refundId, $rejectionReason, $rejectionNotes);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Refund has been rejected successfully'
                    ]);
                } else {
                    throw new Exception('Failed to reject refund');
                }
                break;

            case 'get_refund_details':
                $refundId = (int) $_GET['refund_id'];
                $refund = $refundModel->getRefundById($refundId);

                if ($refund) {
                    echo json_encode(['success' => true, 'refund' => $refund]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Refund not found']);
                }
                break;
            
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$outstandingPayments = $paymentModel->getOutstandingPayments();
$historyPayments = $paymentModel->getAllPaymentsHistory(20, 0);

// Fix: Assign refund data to the correct variable name used in the template
$refundPayments = $refundModel->getAllRefundPayments();
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
                        <?php if (!empty($historyPayments)): ?>
                            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/history-payments.php'; ?>
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
                            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/components/tabs/refund-payments.php'; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-undo"></i>
                                <h4>No Pending Refunds</h4>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script
            src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-outstanding-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-history-management.js"></script>
        <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-refund-management.js"></script>


</body>

</html>