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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination parameters
$limit = 10; // Items per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Determine active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'outstanding';

// Check if filters are active
$hasActiveFilters = !empty($dateFrom) || !empty($dateTo) || !empty($search);

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
                $historyPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                $historyLimit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
                $historyOffset = ($historyPage - 1) * $historyLimit;

                $historyPayments = $paymentModel->getAllPaymentsHistory($historyLimit, $historyOffset, $dateFrom, $dateTo, '', '', $search);
                $totalCount = $paymentModel->getTotalPaymentsCount($dateFrom, $dateTo, '', '', $search);

                echo json_encode([
                    'success' => true,
                    'payments' => $historyPayments,
                    'total' => $totalCount,
                    'page' => $historyPage,
                    'hasMore' => ($historyOffset + $historyLimit) < $totalCount
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

// Get data based on active tab with pagination
$outstandingPayments = [];
$historyPayments = [];
$refundPayments = [];
$paginationData = [];

switch ($activeTab) {
    case 'outstanding':
        $outstandingPayments = $paymentModel->getOutstandingPayments($dateFrom, $dateTo, '', '', $search, $limit, $offset);
        $totalItems = $paymentModel->getOutstandingPaymentsCount($dateFrom, $dateTo, '', '', $search);
        $paginationData = [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $limit),
            'total_items' => $totalItems,
            'has_previous' => $page > 1,
            'has_next' => $page < ceil($totalItems / $limit),
            'previous_page' => $page - 1,
            'next_page' => $page + 1
        ];
        break;

    case 'history':
        $historyPayments = $paymentModel->getAllPaymentsHistory($limit, $offset, $dateFrom, $dateTo, '', '', $search);
        $totalItems = $paymentModel->getTotalPaymentsCount($dateFrom, $dateTo, '', '', $search);
        $paginationData = [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $limit),
            'total_items' => $totalItems,
            'has_previous' => $page > 1,
            'has_next' => $page < ceil($totalItems / $limit),
            'previous_page' => $page - 1,
            'next_page' => $page + 1
        ];
        break;

    case 'refunds':
        $refundPayments = $refundModel->getAllRefundPayments($dateFrom, $dateTo, $search, $limit, $offset);
        $totalItems = $refundModel->getRefundPaymentsCount($dateFrom, $dateTo, $search);
        $paginationData = [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $limit),
            'total_items' => $totalItems,
            'has_previous' => $page > 1,
            'has_next' => $page < ceil($totalItems / $limit),
            'previous_page' => $page - 1,
            'next_page' => $page + 1
        ];
        break;

    default:
        // Default to outstanding
        $outstandingPayments = $paymentModel->getOutstandingPayments($dateFrom, $dateTo, '', '', $search, $limit, $offset);
        $totalItems = $paymentModel->getOutstandingPaymentsCount($dateFrom, $dateTo, '', '', $search);
        $paginationData = [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $limit),
            'total_items' => $totalItems,
            'has_previous' => $page > 1,
            'has_next' => $page < ceil($totalItems / $limit),
            'previous_page' => $page - 1,
            'next_page' => $page + 1
        ];
        break;
}

// Get counts for tab badges (without pagination)
$outstandingCount = $paymentModel->getOutstandingPaymentsCount($dateFrom, $dateTo, '', '', $search);
$historyCount = $paymentModel->getTotalPaymentsCount($dateFrom, $dateTo, '', '', $search);
$refundCount = $refundModel->getRefundPaymentsCount($dateFrom, $dateTo, $search);

// For backward compatibility
$pendingRefunds = $refundPayments;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Payment Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-tab.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/pagination/pagination.css">

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
                            To: <?= date('